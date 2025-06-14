<?php

namespace Sensiolabs\GotenbergBundle\Processor;

use Psr\Log\LoggerInterface;
use Sensiolabs\GotenbergBundle\Exception\ProcessorException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @implements ProcessorInterface<string>
 */
final class FileProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $directory,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function __invoke(string|null $fileName): \Generator
    {
        if (null === $fileName) {
            $fileName = uniqid('gotenberg_', true).'.pdf';
            $this->logger?->debug('{processor}: no filename given. Content will be dumped to "{file}".', ['processor' => self::class, 'file' => $fileName]);
        }

        $tempfileProcessor = (new TempfileProcessor())($fileName);

        do {
            $chunk = yield;
            $tempfileProcessor->send($chunk);
        } while (!$chunk->isLast());

        $resource = $tempfileProcessor->getReturn();

        try {
            $path = $this->directory.'/'.$fileName;

            $this->filesystem->dumpFile($path, $resource);
            $this->logger?->debug('{processor}: content dumped to "{path}".', ['processor' => self::class, 'path' => $path]);

            return new \SplFileInfo($path);
        } catch (\Throwable $t) {
            throw new ProcessorException(\sprintf('Unable to write to "%s".', $fileName), previous : $t);
        } finally {
            fclose($resource);
        }
    }
}
