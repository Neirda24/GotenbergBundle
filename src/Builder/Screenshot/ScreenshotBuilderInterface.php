<?php

namespace Sensiolabs\GotenbergBundle\Builder\Screenshot;

use Sensiolabs\GotenbergBundle\Builder\GotenbergFileResult;

/**
 * @template T of mixed
 */
interface ScreenshotBuilderInterface
{
    /**
     * Generates the Screenshot and returns the result.
     *
     * @return GotenbergFileResult<T>
     */
    public function generate(): GotenbergFileResult;
}
