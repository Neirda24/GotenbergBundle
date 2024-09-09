<?php

namespace Sensiolabs\GotenbergBundle;

use Sensiolabs\GotenbergBundle\Builder\Screenshot\HtmlScreenshotBuilder;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\MarkdownScreenshotBuilder;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\ScreenshotBuilderInterface;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\UrlScreenshotBuilder;

interface GotenbergScreenshotInterface
{
    /**
     * @template T of ScreenshotBuilderInterface
     *
     * @param string|class-string<T> $builder
     *
     * @return ($builder is class-string ? T : ScreenshotBuilderInterface<mixed>)
     */
    public function get(string $builder): ScreenshotBuilderInterface;

    /**
     * @return HtmlScreenshotBuilder<mixed>
     */
    public function html(): ScreenshotBuilderInterface;

    /**
     * @return UrlScreenshotBuilder<mixed>
     */
    public function url(): ScreenshotBuilderInterface;

    /**
     * @return MarkdownScreenshotBuilder<mixed>
     */
    public function markdown(): ScreenshotBuilderInterface;
}
