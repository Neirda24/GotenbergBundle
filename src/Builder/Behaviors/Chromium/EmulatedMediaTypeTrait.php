<?php

namespace Sensiolabs\GotenbergBundle\Builder\Behaviors\Chromium;

use Sensiolabs\GotenbergBundle\Builder\Attributes\ExposeSemantic;
use Sensiolabs\GotenbergBundle\Builder\Attributes\NormalizeGotenbergPayload;
use Sensiolabs\GotenbergBundle\Builder\BodyBag;
use Sensiolabs\GotenbergBundle\Builder\Util\NormalizerFactory;
use Sensiolabs\GotenbergBundle\Enumeration\EmulatedMediaType;
use Sensiolabs\GotenbergBundle\NodeBuilder\EnumNodeBuilder;

/**
 * @see https://gotenberg.dev/docs/routes#emulated-media-type-chromium.
 */
trait EmulatedMediaTypeTrait
{
    abstract protected function getBodyBag(): BodyBag;

    /**
     * Forces Chromium to emulate, either "screen" or "print". (default "print").
     */
    #[ExposeSemantic(new EnumNodeBuilder('emulated_media_type', className: EmulatedMediaType::class, callback: [EmulatedMediaType::class, 'cases']))]
    public function emulatedMediaType(EmulatedMediaType $mediaType): static
    {
        $this->getBodyBag()->set('emulatedMediaType', $mediaType);

        return $this;
    }

    #[NormalizeGotenbergPayload]
    private function normalizeEmulatedMediaType(): \Generator
    {
        yield 'emulatedMediaType' => NormalizerFactory::enum();
    }
}
