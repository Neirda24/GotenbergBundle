<?php

namespace Sensiolabs\GotenbergBundle\Debug;

//use Sensiolabs\GotenbergBundle\Builder\Pdf\ConvertPdfBuilder;
//use Sensiolabs\GotenbergBundle\Builder\Pdf\HtmlPdfBuilder;
//use Sensiolabs\GotenbergBundle\Builder\Pdf\LibreOfficePdfBuilder;
//use Sensiolabs\GotenbergBundle\Builder\Pdf\MarkdownPdfBuilder;
//use Sensiolabs\GotenbergBundle\Builder\Pdf\MergePdfBuilder;
//use Sensiolabs\GotenbergBundle\Builder\Pdf\PdfBuilderInterface;
//use Sensiolabs\GotenbergBundle\Builder\Pdf\SplitPdfBuilder;
//use Sensiolabs\GotenbergBundle\Builder\Pdf\UrlPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\BuilderInterface;
use Sensiolabs\GotenbergBundle\Builder\HtmlPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\MergePdfBuilder;
use Sensiolabs\GotenbergBundle\Debug\Builder\TraceableBuilder;
use Sensiolabs\GotenbergBundle\Debug\Builder\TraceablePdfBuilder;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;

final class TraceableGotenbergPdf implements GotenbergPdfInterface
{
    /**
     * @var list<array{string, TraceableBuilder}>
     */
    private array $builders = [];

    public function __construct(
        private readonly GotenbergPdfInterface $inner,
    ) {
    }

    public function get(string $builder): BuilderInterface
    {
        $traceableBuilder = $this->inner->get($builder);

        if (!$traceableBuilder instanceof TraceableBuilder) {
            return $traceableBuilder;
        }

        $this->builders[] = [$builder, $traceableBuilder];

        return $traceableBuilder;
    }

    /**
     * @return HtmlPdfBuilder|TraceableBuilder
     */
    public function html(): BuilderInterface
    {
        /** @var HtmlPdfBuilder|TraceableBuilder $traceableBuilder */
        $traceableBuilder = $this->inner->html();

        if (!$traceableBuilder instanceof TraceableBuilder) {
            return $traceableBuilder;
        }

        $this->builders[] = ['html', $traceableBuilder];

        return $traceableBuilder;
    }

    /**
     * @return MergePdfBuilder|TraceableBuilder
     */
    public function merge(): BuilderInterface
    {
        /** @var MergePdfBuilder|TraceableBuilder $traceableBuilder */
        $traceableBuilder = $this->inner->merge();

        if (!$traceableBuilder instanceof TraceableBuilder) {
            return $traceableBuilder;
        }

        $this->builders[] = ['merge', $traceableBuilder];

        return $traceableBuilder;
    }

//    /**
//     * @return ConvertPdfBuilder|TraceablePdfBuilder
//     */
//    public function convert(): PdfBuilderInterface
//    {
//        /** @var ConvertPdfBuilder|TraceablePdfBuilder $traceableBuilder */
//        $traceableBuilder = $this->inner->convert();
//
//        if (!$traceableBuilder instanceof TraceablePdfBuilder) {
//            return $traceableBuilder;
//        }
//
//        $this->builders[] = ['convert', $traceableBuilder];
//
//        return $traceableBuilder;
//    }
//
//    /**
//     * @return SplitPdfBuilder|TraceablePdfBuilder
//     */
//    public function split(): PdfBuilderInterface
//    {
//        /** @var SplitPdfBuilder|TraceablePdfBuilder $traceableBuilder */
//        $traceableBuilder = $this->inner->split();
//
//        if (!$traceableBuilder instanceof TraceablePdfBuilder) {
//            return $traceableBuilder;
//        }
//
//        $this->builders[] = ['split', $traceableBuilder];
//
//        return $traceableBuilder;
//    }

    /**
     * @return list<array{string, TraceablePdfBuilder}>
     */
    public function getBuilders(): array
    {
        return $this->builders;
    }
}
