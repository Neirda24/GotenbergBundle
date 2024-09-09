<?php

namespace Sensiolabs\GotenbergBundle;

use Sensiolabs\GotenbergBundle\Builder\Pdf\ConvertPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\HtmlPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\LibreOfficePdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\MarkdownPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\MergePdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\PdfBuilderInterface;
use Sensiolabs\GotenbergBundle\Builder\Pdf\UrlPdfBuilder;

interface GotenbergPdfInterface
{
    /**
     * @template T of PdfBuilderInterface
     *
     * @param string|class-string<T> $builder
     *
     * @return ($builder is class-string ? T : PdfBuilderInterface<mixed>)
     */
    public function get(string $builder): PdfBuilderInterface;

    /**
     * @return HtmlPdfBuilder<mixed>
     */
    public function html(): PdfBuilderInterface;

    /**
     * @return UrlPdfBuilder<mixed>
     */
    public function url(): PdfBuilderInterface;

    /**
     * @return LibreOfficePdfBuilder<mixed>
     */
    public function office(): PdfBuilderInterface;

    /**
     * @return MarkdownPdfBuilder<mixed>
     */
    public function markdown(): PdfBuilderInterface;

    /**
     * @return MergePdfBuilder<mixed>
     */
    public function merge(): PdfBuilderInterface;

    /**
     * @return ConvertPdfBuilder<mixed>
     */
    public function convert(): PdfBuilderInterface;
}
