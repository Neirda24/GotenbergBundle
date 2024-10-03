<?php

namespace Sensiolabs\GotenbergBundle\Builder\Behaviors\Chromium;

use Sensiolabs\GotenbergBundle\Builder\Behaviors\Dependencies\LoggerAwareTrait;
use Sensiolabs\GotenbergBundle\Builder\Behaviors\Dependencies\RequestAwareTrait;
use Sensiolabs\GotenbergBundle\Builder\Util\NormalizerFactory;
use Sensiolabs\GotenbergBundle\Builder\Util\ValidatorFactory;
use Sensiolabs\GotenbergBundle\Client\BodyBag;
use Sensiolabs\GotenbergBundle\Exception\JsonEncodingException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://gotenberg.dev/docs/routes#cookies-chromium.
 */
trait CookieTrait
{
    use LoggerAwareTrait;
    use RequestAwareTrait;

    abstract protected function getBodyBag(): BodyBag;

    protected function configure(OptionsResolver $bodyOptionsResolver, OptionsResolver $headersOptionsResolver): void
    {
        $bodyOptionsResolver
            ->define('cookies')
            ->info('Cookies to store in the Chromium cookie jar (JSON format).')
            ->allowedTypes('array[]', Cookie::class.'[]')
            ->allowedValues(ValidatorFactory::cookies())
            ->normalize(NormalizerFactory::json(false))
        ;
    }

    /**
     * @param list<Cookie|array{name: string, value: string, domain: string, path?: string|null, secure?: bool|null, httpOnly?: bool|null, sameSite?: 'Strict'|'Lax'|null}> $cookies
     */
    public function cookies(array $cookies): static
    {
        if ([] === $cookies) {
            $this->getBodyBag()->unset('cookies');

            return $this;
        }

        $this->addCookies($cookies);

        return $this;
    }

    /**
     * Add cookies to store in the Chromium cookie jar.
     *
     * @see https://gotenberg.dev/docs/routes#cookies-chromium
     *
     * @param list<Cookie|array{name: string, value: string, domain: string, path?: string|null, secure?: bool|null, httpOnly?: bool|null, sameSite?: 'Strict'|'Lax'|null}> $cookies
     */
    public function addCookies(array $cookies): static
    {
        $c = $this->getBodyBag()->get('cookies', []);

        foreach ($cookies as $cookie) {
            if ($cookie instanceof Cookie) {
                $c[$cookie->getName()] = $cookie;

                continue;
            }

            $c[$cookie['name']] = $cookie;
        }

        $this->getBodyBag()->set('cookies', $c);

        return $this;
    }

    /**
     * @param Cookie|array{name: string, value: string, domain: string, path?: string|null, secure?: bool|null, httpOnly?: bool|null, sameSite?: 'Strict'|'Lax'|null} $cookie
     */
    public function setCookie(string $name, Cookie|array $cookie): static
    {
        $current = $this->getBodyBag()->get('cookies', []);
        $current[$name] = $cookie;

        $this->getBodyBag()->set('cookies', $current);

        return $this;
    }

    public function forwardCookie(string $name): static
    {
        $request = $this->getCurrentRequest();

        if (null === $request) {
            $this->getLogger()?->debug('Cookie {sensiolabs_gotenberg.cookie_name} cannot be forwarded because there is no Request.', [
                'sensiolabs_gotenberg.cookie_name' => $name,
            ]);

            return $this;
        }

        if (false === $request->cookies->has($name)) {
            $this->getLogger()?->debug('Cookie {sensiolabs_gotenberg.cookie_name} does not exists.', [
                'sensiolabs_gotenberg.cookie_name' => $name,
            ]);

            return $this;
        }

        return $this->setCookie($name, [
            'name' => $name,
            'value' => $request->cookies->get($name),
            'domain' => $request->getHost(),
        ]);
    }
}
