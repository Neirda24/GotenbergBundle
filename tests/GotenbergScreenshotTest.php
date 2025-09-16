<?php

namespace Sensiolabs\GotenbergBundle\Tests;

use Sensiolabs\GotenbergBundle\GotenbergScreenshotInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GotenbergScreenshotTest extends KernelTestCase
{
    public function testUrlBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergScreenshotInterface $gotenberg */
        $gotenberg = $container->get(GotenbergScreenshotInterface::class);

        $builder = $gotenberg->url();
        $builder
            ->width(500)
            ->height(200)
        ;

        $data = $builder->getBodyBag()->all();

        static::assertArrayHasKey('width', $data);
        static::assertSame(500, $data['width']);

        static::assertArrayHasKey('height', $data);
        static::assertSame(200, $data['height']);
    }

    public function testHtmlBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergScreenshotInterface $gotenberg */
        $gotenberg = $container->get(GotenbergScreenshotInterface::class);

        $builder = $gotenberg->html();
        $builder
            ->width(500)
            ->height(200)
        ;

        $data = $builder->getBodyBag()->all();

        static::assertArrayHasKey('width', $data);
        static::assertSame(500, $data['width']);

        static::assertArrayHasKey('height', $data);
        static::assertSame(200, $data['height']);
    }

    public function testMarkdownBuilderFactory(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var GotenbergScreenshotInterface $gotenberg */
        $gotenberg = $container->get(GotenbergScreenshotInterface::class);

        $builder = $gotenberg->markdown();
        $builder
            ->files(__DIR__.'/Fixtures/assets/file.md')
            ->wrapperFile(__DIR__.'/Fixtures/files/wrapper.html')
            ->width(500)
            ->height(200)
        ;

        $data = $builder->getBodyBag()->all();

        static::assertArrayHasKey('files', $data);

        $files = $data['files'];
        static::assertArrayHasKey(__DIR__.'/Fixtures/assets/file.md', $files);
        static::assertInstanceOf(\SplFileInfo::class, $files[__DIR__.'/Fixtures/assets/file.md']);

        static::assertArrayHasKey('index.html', $data);
        static::assertInstanceOf(\SplFileInfo::class, $data['index.html']);

        static::assertArrayHasKey('width', $data);
        static::assertSame(500, $data['width']);

        static::assertArrayHasKey('height', $data);
        static::assertSame(200, $data['height']);
    }
}
