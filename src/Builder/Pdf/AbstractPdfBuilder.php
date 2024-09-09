<?php

namespace Sensiolabs\GotenbergBundle\Builder\Pdf;

use Sensiolabs\GotenbergBundle\Builder\DefaultBuilderTrait;
use Sensiolabs\GotenbergBundle\Client\GotenbergClientInterface;
use Sensiolabs\GotenbergBundle\Formatter\AssetBaseDirFormatter;

/**
 * @internal
 *
 * @template T of mixed
 * @implements PdfBuilderInterface<T>
 */
abstract class AbstractPdfBuilder implements PdfBuilderInterface
{
    /** @use DefaultBuilderTrait<T> */
    use DefaultBuilderTrait;

    public function __construct(
        GotenbergClientInterface $gotenbergClient,
        AssetBaseDirFormatter $asset,
    ) {
        $this->client = $gotenbergClient;
        $this->asset = $asset;

        $this->normalizers = [
            'metadata' => function (mixed $value): array {
                return $this->encodeData('metadata', $value);
            },
        ];
    }
}
