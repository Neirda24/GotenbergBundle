<?php

declare(strict_types=1);

namespace DaggerModule;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Dagger\Attribute\Check;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Attribute\Generate;
use Dagger\Attribute\ReturnsListOfType;
use Dagger\Attribute\Up;
use Dagger\Changeset;
use Dagger\Container;
use Dagger\Directory;
use Dagger\Secret;
use Dagger\Service;
use Dagger\Workspace;
use DaggerModule\Test\PhpCsFixer;
use function Amp\async;
use function Amp\Future\await;
use function Dagger\core;

#[DaggerObject]
#[Doc('Module for GotenbergBundle')]
class GotenbergBundle
{
    private const DEFAULT_GOTENBERG_VERSION = '8.0';
    private const COMPOSER_HOME = '/root/.composer';

    private Directory $source;
    private Directory $github;

    #[DaggerFunction]
    public function __construct(Workspace $ws)
    {
        $this->source = $ws->directory('/', exclude: ['.git', '.github', '.dagger/sdk', 'dagger.lock'], gitignore: true);
        $this->github = $ws->directory('/.github');
    }

    private function gotenbergContainer(
        string $gotenbergVersion = self::DEFAULT_GOTENBERG_VERSION,
    ): Container {
        return core()
            ->container()
            ->from("gotenberg/gotenberg:{$gotenbergVersion}")
        ;
    }

    private function gotenbergService(
        string $gotenbergVersion = self::DEFAULT_GOTENBERG_VERSION,
    ): Service {
        return $this->gotenbergContainer($gotenbergVersion)
            ->withExposedPort(3000)
            ->asService()
        ;
    }

    private function phpContainer(
        Directory $source,
        string $phpVersion,
    ): Container {
        $aptCache = core()->cacheVolume("apt-cache-{$phpVersion}");
        $composerBin = core()->container()->from('composer/composer:latest-bin')->file('/composer');

        $composerCache = core()->cacheVolume('composer-cache');

        return core()
            ->container()
            ->from("php:{$phpVersion}")
            ->withMountedCache('/var/cache/apt/archives', $aptCache)
            ->withMountedCache(self::COMPOSER_HOME.'/cache/files', $composerCache)
            ->withExec(['apt', 'update'])
            ->withExec(['apt', 'install', '--yes',
                'git',
                'libonig-dev',
                'zip',
            ])
            // The php:8.6 image ships without mbstring, which PHPUnit requires.
            ->withExec(['sh', '-c', 'php -m | grep -qx mbstring || docker-php-ext-install -j"$(nproc)" mbstring'])
            ->withMountedFile('/usr/bin/composer', $composerBin)
            ->withEnvVariable('COMPOSER_ALLOW_SUPERUSER', '1')
            ->withEnvVariable('COMPOSER_HOME', self::COMPOSER_HOME)
            ->withEnvVariable('PATH', '${PATH}:'.self::COMPOSER_HOME.'/vendor/bin', expand: true)
            ->withWorkdir('/GotenbergBundle')
            ->withMountedDirectory('/GotenbergBundle', $source)
        ;
    }

    private function symfonyContainer(
        Directory $source,
        string|null $phpVersion = null,
        string|null $symfonyVersion = null,
        string $minimumStability = 'stable',
        Container|null $phpContainer = null,
    ): Container {
        ['php' => $phpVersion, 'symfony' => $symfonyVersion] = $this->resolveVersions($phpVersion, $symfonyVersion);

        $phpContainer ??= $this->phpContainer($source, $phpVersion);

        $minimumStability = trim($minimumStability);
        if ('' === $minimumStability) {
            $minimumStability = 'stable';
        }

        return $phpContainer
            ->withExec(['composer', 'global', 'config', '--no-plugins', 'allow-plugins.symfony/flex', 'true'])
            ->withExec(['composer', 'global', 'require', 'symfony/flex'])
            ->withEnvVariable('SYMFONY_REQUIRE', $symfonyVersion)
            ->withExec(['composer', 'config', 'minimum-stability', $minimumStability])
            ->withExec(['composer', 'update', '--prefer-dist', '--prefer-stable', '--no-progress'])
        ;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchJson(string $url): array
    {
        return json_decode(core()->http($url)->contents(), associative: true, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * Builds the matrix from the maintained Symfony versions (symfony.com) and the PHP versions
     * between the bundle minimum (composer.json) and the latest active one (php.net).
     *
     * @return list<array{name: string, symfony-version: string, php: string, minimum-stability: string, allow-failure: bool}>
     */
    private function getMatrix(): array
    {
        $symfony = $this->fetchJson('https://symfony.com/releases.json');

        // e.g. ">=8.1" in composer.json and ["8.2", ..., "8.5"] on php.net give 8.1 to 8.5.
        $composerPhpConstraint = json_decode($this->source->file('composer.json')->contents(), associative: true)['require']['php'];
        // ">=8.1" -> "8.1.0.0" -> "8.1"
        preg_match('#^\d+\.\d+#', (new VersionParser())->parseConstraints($composerPhpConstraint)->getLowerBound()->getVersion(), $minPhpVersion);
        $phpVersions = [$minPhpVersion[0]];
        foreach ($this->fetchJson('https://www.php.net/releases/active.php') as $activeVersions) {
            array_push($phpVersions, ...array_keys($activeVersions));
        }
        // The minimum version can also be an active one.
        $phpVersions = Semver::sort($this->filterVersions(array_unique($phpVersions), $composerPhpConstraint));

        // "8.2.0" -> "8.2"
        preg_match('#^\d+\.\d+#', $symfony['symfony_versions']['next'], $nextSymfonyVersion);

        $matrix = [];
        foreach (array_unique([...$symfony['supported_versions'], $nextSymfonyVersion[0]]) as $symfonyVersion) {
            $isNext = $nextSymfonyVersion[0] === $symfonyVersion;
            // "8.4.0" -> "8.4"
            preg_match('#^\d+\.\d+#', $symfony['min_php_requirements'][$symfonyVersion], $minPhpVersion);

            foreach ($phpVersions as $phpVersion) {
                if (version_compare($phpVersion, $minPhpVersion[0], '<')) {
                    continue;
                }

                $matrix[] = [
                    'name' => "Symfony '{$symfonyVersion}' with PHP '{$phpVersion}'",
                    'symfony-version' => "{$symfonyVersion}.*",
                    'php' => $phpVersion,
                    'minimum-stability' => $isNext ? 'dev' : 'stable',
                    'allow-failure' => $isNext,
                ];
            }
        }

        return $matrix;
    }

    /**
     * Keeps the minor versions (e.g. "8.1") that match a Composer constraint (e.g. "^8.1").
     *
     * @param array<int, string> $versions
     *
     * @return array<int, string>
     */
    private function filterVersions(array $versions, string $constraint): array
    {
        return array_filter(
            $versions,
            static fn (string $version): bool => Semver::satisfies("{$version}.0", $constraint),
        );
    }

    /**
     * Resolves the empty versions from symfony.com:
     * - Symfony: the latest stable version, or the highest supported version that runs on the given PHP version.
     * - PHP: the minimum PHP version of the Symfony version.
     *
     * @return array{php: string, symfony: string}
     */
    private function resolveVersions(string|null $phpVersion, string|null $symfonyVersion): array
    {
        $phpVersion = null !== $phpVersion ? trim($phpVersion) : null;
        $symfonyVersion = null !== $symfonyVersion ? trim($symfonyVersion) : null;

        if (null !== $phpVersion && null !== $symfonyVersion) {
            return ['php' => $phpVersion, 'symfony' => $symfonyVersion];
        }

        $symfony = $this->fetchJson('https://symfony.com/releases.json');

        if (null !== $symfonyVersion) {
            // The highest Symfony version that matches "8.1.*", "^8.1" or "~8.1.0".
            $matchingVersions = $this->filterVersions(array_keys($symfony['min_php_requirements']), $symfonyVersion);
            $symfonyMinorVersion = Semver::rsort($matchingVersions)[0] ?? throw new \InvalidArgumentException("No Symfony version matches '{$symfonyVersion}'.");
        } elseif (null === $phpVersion) {
            $symfonyMinorVersion = $symfony['latest_stable_version'];
        } else {
            // supported_versions is sorted from the lowest to the highest version.
            $versions = array_filter(
                $symfony['supported_versions'],
                static function (string $version) use ($symfony, $phpVersion): bool {
                    // "8.4.0" -> "8.4", as version_compare('8.4', '8.4.0') returns -1.
                    preg_match('#^\d+\.\d+#', $symfony['min_php_requirements'][$version], $matches);

                    return version_compare($phpVersion, $matches[0], '>=');
                },
            );
            $symfonyMinorVersion = end($versions);
        }

        if (null === $phpVersion) {
            // "8.4.0" -> "8.4"
            preg_match('#^\d+\.\d+#', $symfony['min_php_requirements'][$symfonyMinorVersion], $matches);
            $phpVersion = $matches[0];
        }

        return ['php' => $phpVersion, 'symfony' => $symfonyVersion ?? "{$symfonyMinorVersion}.*"];
    }

    #[DaggerFunction]
    #[Doc('Output the test matrix (maintained Symfony versions x supported PHP versions) as JSON.')]
    public function matrix(): string
    {
        return json_encode($this->getMatrix(), \JSON_THROW_ON_ERROR);
    }

    #[DaggerFunction]
    #[Check]
    #[Doc('Static analysis with PHPStan, on the default PHP and Symfony versions.')]
    public function phpstan(): void
    {
        $this->test()->phpstan();
    }

    #[DaggerFunction]
    #[Check]
    #[Doc('PHPUnit test suite, on the default PHP and Symfony versions.')]
    public function phpunit(): void
    {
        $this->test()->phpunit();
    }

    #[DaggerFunction]
    #[Check]
    #[Doc('Every dependency the code uses is declared in composer.json.')]
    public function dependencies(): void
    {
        $this->test()->validateDependencies();
    }

    #[DaggerFunction]
    #[Check]
    #[Doc('Audit of the GitHub workflows with zizmor.')]
    public function zizmor(): void
    {
        $this->ci()->zizmor()->check()->sync();
    }

    #[DaggerFunction]
    #[Generate]
    #[Doc('Generates documentation and returns the ChangeSet to apply locally.')]
    public function generateDocs(
        Container|null $symfonyContainer = null,
    ): Changeset {
        $symfonyContainer ??= $this->symfonyContainer($this->source);

        $generatedDocs = core()->directory()->withDirectory('./docs', $symfonyContainer
            ->withExec(['./docs/generate.php'])
            ->directory('./docs'),
        );

        return $generatedDocs->changes(core()->directory()->withDirectory('./docs', $this->source->directory('./docs')));
    }

    #[DaggerFunction]
    #[Generate]
    #[Doc('Fix the coding style with php-cs-fixer.')]
    public function codingStyle(): Changeset
    {
        return $this->phpCsFixer()->fix();
    }

    #[DaggerFunction]
    #[Up]
    #[Doc('A Gotenberg server on port 3000.')]
    public function gotenberg(
        string $gotenbergVersion = self::DEFAULT_GOTENBERG_VERSION,
    ): Service {
        return $this->gotenbergService($gotenbergVersion);
    }

    #[DaggerFunction]
    #[Doc('Run php-cs-fixer. Returns the Directory diff.')]
    public function phpCsFixer(
        Container|null $symfonyContainer = null,
    ): PhpCsFixer {
        $symfonyContainer ??= $this->symfonyContainer($this->source);

        return new PhpCsFixer($this->source, $symfonyContainer);
    }

    #[DaggerFunction]
    #[Doc('Provide a container with all dependencies installed and ready to run tests.')]
    public function ci(
        Secret|null $ghAuthToken = null,
    ): CiGotenbergBundle {
        return new CiGotenbergBundle($this->github, $ghAuthToken);
    }

    #[DaggerFunction]
    #[Doc('Provide a container with all dependencies installed and ready to run tests.')]
    public function test(
        #[Doc('Defaults to the minimum PHP version of the latest stable Symfony version.')]
        string|null $phpVersion = null,
        #[Doc('Defaults to the latest stable Symfony version.')]
        string|null $symfonyVersion = null,
        string $minimumStability = 'stable',
        Container|null $symfonyContainer = null,
    ): TestsGotenbergBundle {
        $symfonyContainer ??= $this->symfonyContainer($this->source, $phpVersion, $symfonyVersion, $minimumStability);

        return new TestsGotenbergBundle($symfonyContainer);
    }

    #[DaggerFunction]
    #[Doc('Execute all tests within matrix (PHP version, Symfony version).')]
    #[ReturnsListOfType(TestsGotenbergBundle::class)]
    public function testsMatrix(): array
    {
        $tests = [];

        foreach ($this->getMatrix() as $row) {
            $tests[] = async(fn () => $this->test($row['php'], $row['symfony-version'], $row['minimum-stability']));
        }

        $result = [];

        foreach (await($tests) as $test) {
            $result[] = $test;
        }

        return $result;
    }
}
