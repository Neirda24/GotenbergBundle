<?php

namespace Sensiolabs\GotenbergBundle;

use Psr\Container\ContainerInterface;
use Sensiolabs\GotenbergBundle\Builder\Pdf\ConvertPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\HtmlPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\LibreOfficePdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\MarkdownPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\MergePdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\PdfBuilderInterface;
use Sensiolabs\GotenbergBundle\Builder\Pdf\UrlPdfBuilder;

final class GotenbergPdf implements GotenbergPdfInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function get(string $builder): PdfBuilderInterface
    {
        return $this->container->get($builder);
    }

    /**
     * @param 'html'|'url'|'markdown'|'office'|'merge'|'convert' $key
     *
     * @return (
     *   $key is 'html' ? HtmlPdfBuilder<mixed> :
     *   $key is 'url' ? UrlPdfBuilder<mixed> :
     *   $key is 'markdown' ? MarkdownPdfBuilder<mixed> :
     *   $key is 'office' ? LibreOfficePdfBuilder<mixed> :
     *   $key is 'merge' ? MergePdfBuilder<mixed> :
     *   $key is 'convert' ? ConvertPdfBuilder<mixed> :
     *   PdfBuilderInterface<mixed>
     * )
     */
    private function getInternal(string $key): PdfBuilderInterface
    {
        return $this->get(".sensiolabs_gotenberg.pdf_builder.{$key}");
    }

    public function html(): PdfBuilderInterface
    {
        return $this->getInternal('html');
    }

    public function url(): PdfBuilderInterface
    {
        return $this->getInternal('url');
    }

    public function office(): PdfBuilderInterface
    {
        return $this->getInternal('office');
    }

    public function markdown(): PdfBuilderInterface
    {
        return $this->getInternal('markdown');
    }

    public function merge(): PdfBuilderInterface
    {
        return $this->getInternal('merge');
    }

    public function convert(): PdfBuilderInterface
    {
        return $this->getInternal('convert');
    }
}
