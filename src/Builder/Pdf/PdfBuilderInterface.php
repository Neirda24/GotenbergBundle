<?php

namespace Sensiolabs\GotenbergBundle\Builder\Pdf;

use Sensiolabs\GotenbergBundle\Builder\GotenbergFileResult;

/**
 * @template T of mixed
 */
interface PdfBuilderInterface
{
    /**
     * Generates the PDF and returns the result.
     *
     * @return GotenbergFileResult<T>
     */
    public function generate(): GotenbergFileResult;
}
