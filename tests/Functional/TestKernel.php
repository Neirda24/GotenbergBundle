<?php

namespace Sensiolabs\GotenbergBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Inspired by Symfony's AppKernel.
 *
 * @see https://github.com/symfony/symfony/blob/7.4/src/Symfony/Bundle/FrameworkBundle/Tests/Functional/app/AppKernel.php
 */
final class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(
        private readonly string $projectDir,
        private readonly string $tmpDir,
    ) {
        parent::__construct('test', true);
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getCacheDir(): string
    {
        return $this->tmpDir.'/cache';
    }

    public function getLogDir(): string
    {
        return $this->tmpDir.'/logs';
    }
}
