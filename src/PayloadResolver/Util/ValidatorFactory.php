<?php

namespace Sensiolabs\GotenbergBundle\PayloadResolver\Util;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class ValidatorFactory
{
    public static function range(string $value): bool
    {
        // See https://regex101.com/r/XUK2Ip/1
        return preg_match('/^ *(\d+ *(- *\d+)? *, *)*\d+ *(- *\d+)? *$/', $value);
    }

    public static function cookies(): \Closure
    {
        return static function (array $cookies) {
            /** @var list<array<string, mixed>|Cookie> $cookies */
            foreach ($cookies as $cookie) {
                if (\is_array($cookie)) {
                    $keys = array_keys($cookie);

                    $fields = ['name', 'value', 'domain', 'path', 'secure', 'httpOnly', 'sameSite'];
                    if ([] !== array_diff($keys, $fields)) {
                        return false;
                    }
                    $required = ['name', 'value', 'domain'];
                    if ([] !== array_diff($required, $keys)) {
                        return false;
                    }
                }
            }

            return true;
        };
    }

    public static function filesExtension(): \Closure
    {
        return function (array $files): bool {
            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo) {
                    throw new InvalidOptionsException(\sprintf('The option "files" expects an array of "%s" instances, but got "%s".', \SplFileInfo::class, $file));
                }

                $ext = $file->getExtension();
                if ('pdf' !== $ext) {
                    throw new InvalidOptionsException(\sprintf('The option "files" expects files with a "pdf" extension, but "%s" has a "%s" extension.', $file, $ext));
                }
            }

            return true;
        };
    }

    public static function download(): \Closure
    {
        return static function (array $downloadFrom) {
            /** @var list<array{url: string, extraHttpHeaders?: array<string, string>}> $downloadFrom */
            foreach ($downloadFrom as $file) {
                if (!\array_key_exists('url', $file)) {
                    return false;
                }
            }

            return true;
        };
    }
}
