<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\DefaultPath;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;
use Dagger\Service;
use function Dagger\dag;

#[DaggerObject]
#[Doc('A generated module for GotenbergBundle functions')]
class GotenbergBundle
{
    #[DaggerFunction]
    #[Doc('Returns a container that echoes whatever string argument is provided')]
    public function gotenbergContainer(
        string $gotenbergVersion = '8.0',
    ): Container {
        return dag()
            ->container()
            ->from("gotenberg/gotenberg:{$gotenbergVersion}")
        ;
    }

    #[DaggerFunction]
    public function gotenbergService(
        string $gotenbergVersion = '8.0',
    ): Service {
        return $this->gotenbergContainer($gotenbergVersion)
            ->withExposedPort(3000)
            ->asService()
        ;
    }

    #[DaggerFunction]
    public function phpContainer(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.4',
    ): Container {
        $aptCache = dag()->cacheVolume("apt-cache-{$phpVersion}");
        $composerBin = dag()->container()->from('composer/composer')->file('/usr/bin/composer');

        return dag()
            ->container()
            ->from("php:{$phpVersion}")
            ->withWorkdir('/GotenbergBundle')
            ->withMountedDirectory('/GotenbergBundle', $source)
            ->withMountedFile('/usr/bin/composer', $composerBin)
            ->withEnvVariable('COMPOSER_ALLOW_SUPERUSER', '1')
            ->withMountedCache('/var/cache/apt/archives', $aptCache)
            ->withExec(['apt', 'update'])
            ->withExec(['apt', 'install', '--yes',
                'git',
                'zip',
            ])
        ;
    }

    #[DaggerFunction]
    public function symfonyContainer(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.4',
        string $symfonyVersion = '7.3',
    ): Container {
        $vendorCache = dag()->cacheVolume("php-{$phpVersion}-symfony-{$symfonyVersion}-vendor-cache");

        return $this->phpContainer($source, $phpVersion)
            ->withExec(['composer', 'global', 'config', '--no-plugins', 'allow-plugins.symfony/flex', 'true'])
            ->withExec(['composer', 'global', 'require', 'symfony/flex'])
            ->withEnvVariable('SYMFONY_REQUIRE', $symfonyVersion)
            ->withMountedCache('/GotenbergBundle/vendor', $vendorCache)
            ->withExec(['composer', 'update'])
        ;
    }

    #[DaggerFunction]
    public function testPhpunitUnit(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.4',
        string $symfonyVersion = '7.3',
    ): Container {
        return $this->symfonyContainer($source, $phpVersion, $symfonyVersion)
            ->withExec(['./vendor/bin/phpunit', '--display-deprecations'])
        ;
    }

    #[DaggerFunction]
    public function testValidateDependencies(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.4',
        string $symfonyVersion = '7.3',
    ): Container {
        return $this->symfonyContainer($source, $phpVersion, $symfonyVersion)
            ->withExec(['./vendor/bin/composer-dependency-analyser', '--show-all-usages'])
        ;
    }

    #[DaggerFunction]
    public function generateDocs(
        #[DefaultPath('.')]
        Directory $source,

        string $phpVersion = '8.4',
        string $symfonyVersion = '7.3',
    ): Directory {
        return $this->symfonyContainer($source, $phpVersion, $symfonyVersion)
            ->withExec(['./docs/generate.php'])
            ->directory('./docs')
        ;
    }
}
