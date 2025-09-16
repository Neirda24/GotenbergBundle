<?php

namespace Sensiolabs\GotenbergBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sensiolabs\GotenbergBundle\Enumeration\PdfFormat;
use Sensiolabs\GotenbergBundle\Enumeration\SplitMode;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GotenbergPdfTest extends KernelTestCase
{
    public function testUrlBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);
        $builder = $gotenberg->url();
        $builder->nativePageRanges('1-5');

        $data = $builder->getBodyBag()->all();

        static::assertCount(1, $data);

        static::assertArrayHasKey('nativePageRanges', $data);
        static::assertSame('1-5', $data['nativePageRanges']);
    }

    public function testHtmlBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);
        $builder = $gotenberg->html();
        $builder
            ->marginTop(3)
            ->marginBottom(1)
        ;

        $data = $builder->getBodyBag()->all();

        static::assertCount(2, $data);

        static::assertArrayHasKey('marginTop', $data);
        static::assertSame('3in', $data['marginTop']);

        static::assertArrayHasKey('marginBottom', $data);
        static::assertSame('1in', $data['marginBottom']);
    }

    public function testMarkdownBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);

        $builder = $gotenberg->markdown();
        $builder->files(__DIR__.'/Fixtures/assets/file.md');
        $builder->wrapperFile(__DIR__.'/Fixtures/files/wrapper.html');
        $data = $builder->getBodyBag()->all();

        static::assertCount(2, $data);

        static::assertArrayHasKey('files', $data);
        static::assertIsArray($data['files']);

        $file = array_shift($data['files']);
        static::assertInstanceOf(\SplFileInfo::class, $file);
        static::assertSame('file.md', $file->getFilename());

        static::assertArrayHasKey('index.html', $data);
        static::assertInstanceOf(\SplFileInfo::class, $data['index.html']);
        static::assertSame('wrapper.html', $data['index.html']->getFilename());
    }

    /**
     * @return iterable<string, array<int, string>>
     */
    public static function provideFileToConvert(): iterable
    {
        yield 'convert odt file' => [__DIR__.'/Fixtures/assets/office/document.odt', 'document.odt'];
        yield 'convert docx file' => [__DIR__.'/Fixtures/assets/office/document_1.docx', 'document_1.docx'];
        yield 'convert html file' => [__DIR__.'/Fixtures/assets/office/document_2.html', 'document_2.html'];
        yield 'convert xlsx file' => [__DIR__.'/Fixtures/assets/office/document_3.xlsx', 'document_3.xlsx'];
        yield 'convert pptx file' => [__DIR__.'/Fixtures/assets/office/document_4.pptx', 'document_4.pptx'];
    }

    #[DataProvider('provideFileToConvert')]
    public function testOfficeBuilderFactory(string $path, string $filename): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);

        $builder = $gotenberg->office();
        $builder->files($path);
        $data = $builder->getBodyBag()->all();

        static::assertCount(1, $data);

        static::assertArrayHasKey('files', $data);
        static::assertIsArray($data['files']);

        $firstFile = array_shift($data['files']);
        static::assertInstanceOf(\SplFileInfo::class, $firstFile);
        static::assertSame($filename, $firstFile->getFilename());
    }

    public function testMergeBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);

        $builder = $gotenberg->merge();
        $builder->files(
            __DIR__.'/Fixtures/assets/pdf/document.pdf',
            __DIR__.'/Fixtures/assets/pdf/other_document.pdf',
        );
        $builder->pdfUniversalAccess();
        $data = $builder->getBodyBag()->all();

        static::assertCount(2, $data);

        static::assertArrayHasKey('files', $data);
        static::assertIsArray($data['files']);

        $firstFile = array_shift($data['files']);
        static::assertInstanceOf(\SplFileInfo::class, $firstFile);
        static::assertSame('document.pdf', $firstFile->getFilename());

        $lastFile = array_pop($data['files']);
        static::assertInstanceOf(\SplFileInfo::class, $lastFile);
        static::assertSame('other_document.pdf', $lastFile->getFilename());

        static::assertArrayHasKey('pdfua', $data);
        static::assertTrue($data['pdfua']);
    }

    public function testConvertBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);

        $builder = $gotenberg->convert();
        $builder->files(__DIR__.'/Fixtures/assets/pdf/document.pdf');
        $builder->pdfFormat(PdfFormat::Pdf1b);
        $data = $builder->getBodyBag()->all();

        static::assertCount(2, $data);

        static::assertArrayHasKey('files', $data);
        static::assertIsArray($data['files']);

        $firstFile = array_shift($data['files']);
        static::assertInstanceOf(\SplFileInfo::class, $firstFile);
        static::assertSame('document.pdf', $firstFile->getFilename());

        static::assertArrayHasKey('pdfa', $data);
        static::assertSame(PdfFormat::Pdf1b, $data['pdfa']);
    }

    public function testSplitBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);

        $builder = $gotenberg->split();
        $builder->files(__DIR__.'/Fixtures/assets/pdf/document.pdf');
        $builder->splitMode(SplitMode::Pages);
        $builder->splitSpan('1-2');
        $builder->splitUnify();

        $data = $builder->getBodyBag()->all();

        static::assertCount(4, $data);

        static::assertArrayHasKey('files', $data);
        static::assertIsArray($data['files']);

        $firstFile = array_shift($data['files']);
        static::assertInstanceOf(\SplFileInfo::class, $firstFile);
        static::assertSame('document.pdf', $firstFile->getFilename());

        static::assertArrayHasKey('splitMode', $data);
        static::assertSame(SplitMode::Pages, $data['splitMode']);

        static::assertArrayHasKey('splitSpan', $data);
        static::assertSame('1-2', $data['splitSpan']);

        static::assertArrayHasKey('splitUnify', $data);
        static::assertTrue($data['splitUnify']);
    }

    public function testFlattenBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergPdfInterface $gotenberg */
        $gotenberg = $container->get(GotenbergPdfInterface::class);

        $builder = $gotenberg->flatten();
        $builder->files(__DIR__.'/Fixtures/assets/pdf/document.pdf');

        $data = $builder->getBodyBag()->all();

        static::assertCount(1, $data);

        static::assertArrayHasKey('files', $data);
        static::assertIsArray($data['files']);

        $firstFile = array_shift($data['files']);
        static::assertInstanceOf(\SplFileInfo::class, $firstFile);
        static::assertSame('document.pdf', $firstFile->getFilename());
    }
}
