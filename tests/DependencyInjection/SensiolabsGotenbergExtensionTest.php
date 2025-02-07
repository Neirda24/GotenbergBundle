<?php

namespace Sensiolabs\GotenbergBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Sensiolabs\GotenbergBundle\DependencyInjection\Configuration;
use Sensiolabs\GotenbergBundle\DependencyInjection\SensiolabsGotenbergExtension;
use Sensiolabs\GotenbergBundle\Enumeration\EmulatedMediaType;
use Sensiolabs\GotenbergBundle\Enumeration\ImageResolutionDPI;
use Sensiolabs\GotenbergBundle\Enumeration\PaperSize;
use Sensiolabs\GotenbergBundle\Enumeration\PdfFormat;
use Sensiolabs\GotenbergBundle\Enumeration\SplitMode;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

#[CoversClass(SensiolabsGotenbergExtension::class)]
#[UsesClass(ContainerBuilder::class)]
#[UsesClass(Configuration::class)]
final class SensiolabsGotenbergExtensionTest extends TestCase
{
    private function getContainerBuilder(bool $kernelDebug = false): ContainerBuilder
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.debug' => $kernelDebug,
        ]));
    }

    public function testGotenbergConfiguredWithValidConfig(): void
    {
        $extension = new SensiolabsGotenbergExtension();

        $containerBuilder = $this->getContainerBuilder();
        $validConfig = self::getValidConfig();
        $extension->load($validConfig, $containerBuilder);

        $list = [
            'pdf' => [
                'html' => [
                    'paper_standard_size' => PaperSize::A4,
                    'margin_top' => 1,
                    'margin_bottom' => 1,
                    'margin_left' => 1,
                    'margin_right' => 1,
                    'prefer_css_page_size' => true,
                    'generate_document_outline' => true,
                    'print_background' => true,
                    'omit_background' => true,
                    'landscape' => true,
                    'scale' => 1.5,
                    'native_page_ranges' => '1-5',
                    'wait_delay' => '10s',
                    'wait_for_expression' => 'window.globalVar === "ready"',
                    'emulated_media_type' => EmulatedMediaType::Screen,
                    'cookies' => [[
                        'name' => 'cook_me',
                        'value' => 'sensio',
                        'domain' => 'sensiolabs.com',
                        'secure' => true,
                        'httpOnly' => true,
                        'sameSite' => 'Lax',
                    ]],
                    'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                    'fail_on_http_status_codes' => [401],
                    'fail_on_resource_http_status_codes' => [401],
                    'fail_on_resource_loading_failed' => true,
                    'fail_on_console_exceptions' => true,
                    'skip_network_idle_event' => true,
                    'pdf_format' => PdfFormat::Pdf1b,
                    'pdf_universal_access' => true,
                    'download_from' => [
                        [
                            'url' => 'http://example.com',
                            'extraHttpHeaders' => [
                                'MyHeader' => 'MyValue',
                            ],
                        ],
                    ],
                ],
                'url' => [
                    'paper_width' => 21,
                    'paper_height' => 50,
                    'margin_top' => 0.5,
                    'margin_bottom' => 0.5,
                    'margin_left' => 0.5,
                    'margin_right' => 0.5,
                    'prefer_css_page_size' => false,
                    'generate_document_outline' => false,
                    'print_background' => false,
                    'omit_background' => false,
                    'landscape' => false,
                    'scale' => 1.5,
                    'native_page_ranges' => '1-10',
                    'wait_delay' => '5s',
                    'wait_for_expression' => 'window.globalVar === "ready"',
                    'emulated_media_type' => EmulatedMediaType::Screen,
                    'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                    'fail_on_http_status_codes' => [401, 403],
                    'fail_on_resource_http_status_codes' => [401, 403],
                    'fail_on_resource_loading_failed' => false,
                    'fail_on_console_exceptions' => false,
                    'skip_network_idle_event' => false,
                    'pdf_format' => PdfFormat::Pdf2b,
                    'pdf_universal_access' => false,
                    'cookies' => [[
                        'name' => 'cook_me',
                        'value' => 'sensio',
                        'domain' => 'sensiolabs.com',
                        'secure' => true,
                        'httpOnly' => true,
                        'sameSite' => 'Lax',
                    ]],
                    'download_from' => [
                        [
                            'url' => 'http://example.com',
                            'extraHttpHeaders' => [
                                'MyHeader' => 'MyValue',
                            ],
                        ],
                    ],
                ],
                //                'markdown' => [
                //                    'paper_width' => 30,
                //                    'paper_height' => 45,
                //                    'margin_top' => 1,
                //                    'margin_bottom' => 1,
                //                    'margin_left' => 1,
                //                    'margin_right' => 1,
                //                    'prefer_css_page_size' => true,
                //                    'generate_document_outline' => true,
                //                    'print_background' => false,
                //                    'omit_background' => false,
                //                    'landscape' => true,
                //                    'scale' => 1.5,
                //                    'native_page_ranges' => '1-5',
                //                    'wait_delay' => '10s',
                //                    'wait_for_expression' => 'window.globalVar === "ready"',
                //                    'emulated_media_type' => 'screen',
                //                    'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                //                    'fail_on_http_status_codes' => [404],
                //                    'fail_on_resource_http_status_codes' => [404],
                //                    'fail_on_resource_loading_failed' => false,
                //                    'fail_on_console_exceptions' => false,
                //                    'skip_network_idle_event' => true,
                //                    'pdf_format' => PdfFormat::Pdf3b->value,
                //                    'pdf_universal_access' => true,
                //                    'cookies' => [],
                //                    'download_from' => [],
                //                ],
                'office' => [
                    'password' => 'secret',
                    'pdf_format' => PdfFormat::Pdf1b,
                    'pdf_universal_access' => true,
                    'landscape' => false,
                    'native_page_ranges' => '1-2',
                    'do_not_export_form_fields' => false,
                    'single_page_sheets' => true,
                    'merge' => true,
                    'metadata' => [
                        'Author' => 'SensioLabs HTML',
                    ],
                    'allow_duplicate_field_names' => true,
                    'do_not_export_bookmarks' => false,
                    'export_bookmarks_to_pdf_destination' => true,
                    'export_placeholders' => true,
                    'export_notes' => true,
                    'export_notes_pages' => true,
                    'export_only_notes_pages' => true,
                    'export_notes_in_margin' => true,
                    'convert_ooo_target_to_pdf_target' => true,
                    'export_links_relative_fsys' => true,
                    'export_hidden_slides' => true,
                    'skip_empty_pages' => true,
                    'add_original_document_as_stream' => true,
                    'lossless_image_compression' => true,
                    'quality' => 80,
                    'reduce_image_resolution' => true,
                    'max_image_resolution' => ImageResolutionDPI::DPI150,
                    'download_from' => [
                        [
                            'url' => 'http://example.com',
                            'extraHttpHeaders' => [
                                'MyHeader' => 'MyValue',
                            ],
                        ],
                    ],
                    'split_mode' => SplitMode::Pages,
                    'split_span' => '1-2',
                    'split_unify' => true,
                ],
                'merge' => [
                    'pdf_format' => PdfFormat::Pdf3b,
                    'pdf_universal_access' => true,
                    'metadata' => [
                        'Author' => 'SensioLabs HTML',
                    ],
                    'download_from' => [
                        [
                            'url' => 'http://example.com',
                            'extraHttpHeaders' => [
                                'MyHeader' => 'MyValue',
                            ],
                        ],
                    ],
                ],
                //                'convert' => [
                //                    'pdf_format' => 'PDF/A-2b',
                //                    'pdf_universal_access' => true,
                //                    'download_from' => [],
                //                ],
            ],
            //            'screenshot' => [
            //                'html' => [
            //                    'width' => 500,
            //                    'height' => 500,
            //                    'clip' => true,
            //                    'format' => 'png',
            //                    'omit_background' => true,
            //                    'optimize_for_speed' => true,
            //                    'wait_delay' => '10s',
            //                    'wait_for_expression' => 'window.globalVar === "ready"',
            //                    'emulated_media_type' => 'screen',
            //                    'cookies' => [[
            //                        'name' => 'cook',
            //                        'value' => 'sensio',
            //                        'domain' => 'sensiolabs.com',
            //                        'secure' => true,
            //                        'httpOnly' => true,
            //                        'path' => null,
            //                        'sameSite' => null,
            //                    ]],
            //                    'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
            //                    'fail_on_http_status_codes' => [401],
            //                    'fail_on_resource_http_status_codes' => [401],
            //                    'fail_on_resource_loading_failed' => true,
            //                    'fail_on_console_exceptions' => true,
            //                    'skip_network_idle_event' => true,
            //                    'download_from' => [],
            //                ],
            //                'url' => [
            //                    'width' => 1000,
            //                    'height' => 500,
            //                    'clip' => true,
            //                    'format' => 'jpeg',
            //                    'quality' => 75,
            //                    'omit_background' => false,
            //                    'optimize_for_speed' => true,
            //                    'wait_delay' => '5s',
            //                    'wait_for_expression' => 'window.globalVar === "ready"',
            //                    'emulated_media_type' => 'screen',
            //                    'cookies' => [[
            //                        'name' => 'cook_me',
            //                        'value' => 'sensio',
            //                        'domain' => 'sensiolabs.com',
            //                        'path' => null,
            //                        'secure' => null,
            //                        'httpOnly' => null,
            //                        'sameSite' => null,
            //                    ]],
            //                    'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
            //                    'fail_on_http_status_codes' => [401, 403],
            //                    'fail_on_resource_http_status_codes' => [401, 403],
            //                    'fail_on_resource_loading_failed' => false,
            //                    'fail_on_console_exceptions' => false,
            //                    'skip_network_idle_event' => true,
            //                    'download_from' => [],
            //                ],
            //                'markdown' => [
            //                    'width' => 1000,
            //                    'height' => 500,
            //                    'clip' => true,
            //                    'format' => 'webp',
            //                    'omit_background' => false,
            //                    'optimize_for_speed' => false,
            //                    'wait_delay' => '15s',
            //                    'wait_for_expression' => 'window.globalVar === "ready"',
            //                    'emulated_media_type' => 'screen',
            //                    'cookies' => [[
            //                        'name' => 'cook_me',
            //                        'value' => 'sensio',
            //                        'domain' => 'sensiolabs.com',
            //                        'path' => null,
            //                        'secure' => null,
            //                        'httpOnly' => null,
            //                        'sameSite' => null,
            //                    ]],
            //                    'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
            //                    'fail_on_http_status_codes' => [401, 403],
            //                    'fail_on_resource_http_status_codes' => [401, 403],
            //                    'fail_on_resource_loading_failed' => false,
            //                    'fail_on_console_exceptions' => false,
            //                    'skip_network_idle_event' => false,
            //                    'download_from' => [],
            //                ],
            //            ],
        ];

        foreach ($list as $builderType => $builder) {
            foreach ($builder as $builderName => $expectedConfig) {
                $definition = $containerBuilder->getDefinition(".sensiolabs_gotenberg.{$builderType}_builder.{$builderName}");

                /** @var array<array-key, mixed> $configurator */
                $configurator = $definition->getConfigurator();
                self::assertSame('setConfigurations', $configurator[1]);

                $configuratorDefinition = $containerBuilder->getDefinition($configurator[0]->__toString());
                self::assertSame($expectedConfig, $configuratorDefinition->getArgument(0));
            }
        }
    }

    //    public static function urlBuildersCanChangeTheirRequestContextProvider(): \Generator
    //    {
    //        yield '.sensiolabs_gotenberg.pdf_builder.url' => ['.sensiolabs_gotenberg.pdf_builder.url'];
    //        yield '.sensiolabs_gotenberg.screenshot_builder.url' => ['.sensiolabs_gotenberg.screenshot_builder.url'];
    //    }
    //
    //    #[DataProvider('urlBuildersCanChangeTheirRequestContextProvider')]
    //    public function testUrlBuildersCanChangeTheirRequestContext(string $serviceName): void
    //    {
    //        $extension = new SensiolabsGotenbergExtension();
    //
    //        $containerBuilder = $this->getContainerBuilder();
    //        self::assertNotContains('.sensiolabs_gotenberg.request_context', $containerBuilder->getServiceIds());
    //
    //        $extension->load([[
    //            'http_client' => 'http_client',
    //            'request_context' => [
    //                'base_uri' => 'https://sensiolabs.com',
    //            ],
    //        ]], $containerBuilder);
    //
    //        self::assertContains('.sensiolabs_gotenberg.request_context', $containerBuilder->getServiceIds());
    //
    //        $requestContextDefinition = $containerBuilder->getDefinition('.sensiolabs_gotenberg.request_context');
    //        self::assertSame('https://sensiolabs.com', $requestContextDefinition->getArgument(0));
    //
    //        $urlBuilderDefinition = $containerBuilder->getDefinition($serviceName);
    //        self::assertCount(3, $urlBuilderDefinition->getMethodCalls());
    //
    //        $indexedMethodCalls = [];
    //        foreach ($urlBuilderDefinition->getMethodCalls() as $methodCall) {
    //            [$name, $arguments] = $methodCall;
    //            $indexedMethodCalls[$name] ??= [];
    //            $indexedMethodCalls[$name][] = $arguments;
    //        }
    //
    //        self::assertArrayHasKey('setRequestContext', $indexedMethodCalls);
    //        self::assertCount(1, $indexedMethodCalls['setRequestContext']);
    //    }

    public function testDataCollectorIsNotEnabledWhenKernelDebugIsFalse(): void
    {
        $extension = new SensiolabsGotenbergExtension();

        $containerBuilder = $this->getContainerBuilder(kernelDebug: false);
        $extension->load([[
            'http_client' => 'http_client',
        ]], $containerBuilder);

        self::assertNotContains('sensiolabs_gotenberg.data_collector', $containerBuilder->getServiceIds());
    }

    public function testDataCollectorIsEnabledWhenKernelDebugIsTrue(): void
    {
        $extension = new SensiolabsGotenbergExtension();

        $containerBuilder = $this->getContainerBuilder(kernelDebug: true);
        $extension->load([[
            'http_client' => 'http_client',
        ]], $containerBuilder);

        self::assertContains('sensiolabs_gotenberg.data_collector', $containerBuilder->getServiceIds());
    }

    public function testDataCollectorIsProperlyConfiguredIfEnabled(): void
    {
        $extension = new SensiolabsGotenbergExtension();

        $containerBuilder = $this->getContainerBuilder(kernelDebug: true);
        $extension->load([[
            'http_client' => 'http_client',
            'default_options' => [
                'pdf' => [
                    'html' => [
                        'metadata' => [
                            'Author' => 'SensioLabs HTML',
                        ],
                    ],
                    'url' => [
                        'metadata' => [
                            'Author' => 'SensioLabs URL',
                        ],
                    ],
                    //                    'markdown' => [
                    //                        'metadata' => [
                    //                            'Author' => 'SensioLabs MARKDOWN',
                    //                        ],
                    //                        'cookies' => [],
                    //                        'extra_http_headers' => [],
                    //                        'fail_on_http_status_codes' => [],
                    //                        'fail_on_resource_http_status_codes' => [],
                    //                        'download_from' => [],
                    //                    ],
                    'office' => [
                        'metadata' => [
                            'Author' => 'SensioLabs OFFICE',
                        ],
                    ],
                    'merge' => [
                        'metadata' => [
                            'Author' => 'SensioLabs MERGE',
                        ],
                    ],
                    //                    'convert' => [
                    //                        'pdf_format' => 'PDF/A-2b',
                    //                        'download_from' => [],
                    //                    ],
                ],
            ],
        ]], $containerBuilder);

        $dataCollector = $containerBuilder->getDefinition('sensiolabs_gotenberg.data_collector');
        self::assertNotNull($dataCollector);

        $dataCollectorOptions = $dataCollector->getArguments()[3];
        self::assertEquals([
            'html' => [
                'metadata' => [
                    'Author' => 'SensioLabs HTML',
                ],
            ],
            'url' => [
                'metadata' => [
                    'Author' => 'SensioLabs URL',
                ],
            ],
            //            'markdown' => [
            //                'metadata' => [
            //                    'Author' => 'SensioLabs MARKDOWN',
            //                ],
            //                'cookies' => [],
            //                'extra_http_headers' => [],
            //                'fail_on_http_status_codes' => [],
            //                'fail_on_resource_http_status_codes' => [],
            //                'download_from' => [],
            //            ],
            'office' => [
                'metadata' => [
                    'Author' => 'SensioLabs OFFICE',
                ],
            ],
            'merge' => [
                'metadata' => [
                    'Author' => 'SensioLabs MERGE',
                ],
            ],
            //            'convert' => [
            //                'pdf_format' => 'PDF/A-2b',
            //                'download_from' => [],
            //            ],
            //            'split' => [],
        ], $dataCollectorOptions);
    }

    public function testBuilderWebhookConfiguredWithValidConfiguration(): void
    {
        $extension = new SensiolabsGotenbergExtension();

        $containerBuilder = $this->getContainerBuilder();
        $extension->load([[
            'http_client' => 'http_client',
            'webhook' => [
                'foo' => ['success' => ['url' => 'https://sensiolabs.com/webhook'], 'error' => ['route' => 'simple_route']],
                'baz' => ['success' => ['route' => ['array_route', ['param1', 'param2']]]],
            ],
            'default_options' => [
                'webhook' => 'foo',
                'pdf' => [
                    'html' => ['webhook' => ['config_name' => 'bar']],
                    //                        'url' => ['webhook' => 'baz'],
                    //                        'markdown' => ['webhook' => ['success' => ['url' => 'https://sensiolabs.com/webhook-on-the-fly']]],
                ],
                //                    'screenshot' => [
                //                        'html' => ['webhook' => 'foo'],
                //                        'url' => ['webhook' => 'bar'],
                //                        'markdown' => ['webhook' => 'baz'],
                //                    ],
            ],
        ]], $containerBuilder);

        $list = [
            'pdf' => [
                'html' => [
                    'webhook' => [
                        'config_name' => 'bar',
                        'extra_http_headers' => [],
                    ],
                ],
                //                'url' => [],
                //                'markdown' => [],
                'office' => [
                    'webhook' => [
                        'success' => [
                            'url' => 'https://sensiolabs.com/webhook',
                            'method' => null,
                        ],
                        'error' => [
                            'route' => [
                                'simple_route',
                                [],
                            ],
                            'method' => null,
                        ],
                        'extra_http_headers' => [],
                    ],
                ],
                'merge' => [
                    'webhook' => [
                        'success' => [
                            'url' => 'https://sensiolabs.com/webhook',
                            'method' => null,
                        ],
                        'error' => [
                            'route' => [
                                'simple_route',
                                [],
                            ],
                            'method' => null,
                        ],
                        'extra_http_headers' => [],
                    ],
                ],
                //                'convert' => [],
            ],
            //            'screenshot' => [
            //                'html' => [],
            //                'url' => [],
            //                'markdown' => [],
            //            ],
        ];

        foreach ($list as $builderType => $builder) {
            foreach ($builder as $builderName => $expectedConfig) {
                $definition = $containerBuilder->getDefinition(".sensiolabs_gotenberg.{$builderType}_builder.{$builderName}");

                $configurator = $definition->getConfigurator();
                self::assertSame('setConfigurations', $configurator[1]);

                $configuratorDefinition = $containerBuilder->getDefinition($configurator[0]->__toString());
                self::assertSame($expectedConfig, $configuratorDefinition->getArgument(0));
            }
        }

        //            $webhookConfigurationRegistryDefinition = $containerBuilder->getDefinition('.sensiolabs_gotenberg.webhook_configuration_registry');
        //            $methodCalls = $webhookConfigurationRegistryDefinition->getMethodCalls();
        //            self::assertCount(3, $methodCalls);
        //            foreach ($methodCalls as $methodCall) {
        //                [$name, $arguments] = $methodCall;
        //                self::assertSame('add', $name);
        //                self::assertContains($arguments[0], ['foo', 'baz', '.sensiolabs_gotenberg.pdf_builder.markdown.webhook_configuration']);
        //                self::assertSame(match ($arguments[0]) {
        //                    'foo' => [
        //                        'success' => [
        //                            'url' => 'https://sensiolabs.com/webhook',
        //                            'method' => null,
        //                        ],
        //                        'error' => [
        //                            'route' => ['simple_route', []],
        //                            'method' => null,
        //                        ],
        //                        'extra_http_headers' => [],
        //                    ],
        //                    'baz' => [
        //                        'success' => [
        //                            'route' => ['array_route', ['param1', 'param2']],
        //                            'method' => null,
        //                        ],
        //                        'extra_http_headers' => [],
        //                    ],
        //                    '.sensiolabs_gotenberg.pdf_builder.markdown.webhook_configuration' => [
        //                        'success' => [
        //                            'url' => 'https://sensiolabs.com/webhook-on-the-fly',
        //                            'method' => null,
        //                        ],
        //                        'error' => [
        //                            'route' => ['simple_route', []],
        //                            'method' => null,
        //                        ],
        //                        'extra_http_headers' => [],
        //                    ],
        //                    default => self::fail('Unexpected webhook configuration'),
        //                }, $arguments[1], "Configuration mismatch for webhook '{$arguments[0]}'.");
        //            }
    }

    /**
     * @return array<int, array{
     *          'webhook': array<string, array{
     *              'success': array{'url'?: string, 'route'?: string|array{0: string, 1: list<mixed>}, 'webhook'?: string},
     *              'error'?: array{'url'?: string, 'route'?: string|array{0: string, 1: list<mixed>}, 'webhook'?: string}
     *          }>,
     *          'default_options': array{
     *              'webhook': string,
     *              'pdf': array{
     *                  'html': array<string, mixed>,
     *                  'url': array<string, mixed>,
     *                  'markdown': array<string, mixed>,
     *                  'office': array<string, mixed>,
     *                  'merge': array<string, mixed>,
     *                  'convert': array<string, mixed>,
     *              },
     *              'screenshot': array{
     *                  'html': array<string, mixed>,
     *                  'url': array<string, mixed>,
     *                  'markdown': array<string, mixed>,
     *              }
     *          }
     *      }>
     */
    private static function getValidConfig(): array
    {
        return [
            [
                'http_client' => 'http_client',
                'default_options' => [
                    'pdf' => [
                        'html' => [
                            'paper_standard_size' => 'A4',
                            'margin_top' => 1,
                            'margin_bottom' => 1,
                            'margin_left' => 1,
                            'margin_right' => 1,
                            'prefer_css_page_size' => true,
                            'generate_document_outline' => true,
                            'print_background' => true,
                            'omit_background' => true,
                            'landscape' => true,
                            'scale' => 1.5,
                            'native_page_ranges' => '1-5',
                            'wait_delay' => '10s',
                            'wait_for_expression' => 'window.globalVar === "ready"',
                            'emulated_media_type' => 'screen',
                            'cookies' => [[
                                'name' => 'cook_me',
                                'value' => 'sensio',
                                'domain' => 'sensiolabs.com',
                                'secure' => true,
                                'httpOnly' => true,
                                'sameSite' => 'Lax',
                            ]],
                            'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                            'fail_on_http_status_codes' => [401],
                            'fail_on_resource_http_status_codes' => [401],
                            'fail_on_resource_loading_failed' => true,
                            'fail_on_console_exceptions' => true,
                            'skip_network_idle_event' => true,
                            'pdf_format' => PdfFormat::Pdf1b->value,
                            'pdf_universal_access' => true,
                            'download_from' => [
                                [
                                    'url' => 'http://example.com',
                                    'extraHttpHeaders' => [
                                        [
                                            'name' => 'MyHeader',
                                            'value' => 'MyValue',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'url' => [
                            'paper_width' => 21,
                            'paper_height' => 50,
                            'margin_top' => 0.5,
                            'margin_bottom' => 0.5,
                            'margin_left' => 0.5,
                            'margin_right' => 0.5,
                            'prefer_css_page_size' => false,
                            'generate_document_outline' => false,
                            'print_background' => false,
                            'omit_background' => false,
                            'landscape' => false,
                            'scale' => 1.5,
                            'native_page_ranges' => '1-10',
                            'wait_delay' => '5s',
                            'wait_for_expression' => 'window.globalVar === "ready"',
                            'emulated_media_type' => 'screen',
                            'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                            'fail_on_http_status_codes' => [401, 403],
                            'fail_on_resource_http_status_codes' => [401, 403],
                            'fail_on_resource_loading_failed' => false,
                            'fail_on_console_exceptions' => false,
                            'skip_network_idle_event' => false,
                            'pdf_format' => PdfFormat::Pdf2b->value,
                            'pdf_universal_access' => false,
                            'cookies' => [[
                                'name' => 'cook_me',
                                'value' => 'sensio',
                                'domain' => 'sensiolabs.com',
                                'secure' => true,
                                'httpOnly' => true,
                                'sameSite' => 'Lax',
                            ]],
                            'download_from' => [
                                [
                                    'url' => 'http://example.com',
                                    'extraHttpHeaders' => [
                                        [
                                            'name' => 'MyHeader',
                                            'value' => 'MyValue',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        //                        'markdown' => [
                        //                            'paper_width' => 30,
                        //                            'paper_height' => 45,
                        //                            'margin_top' => 1,
                        //                            'margin_bottom' => 1,
                        //                            'margin_left' => 1,
                        //                            'margin_right' => 1,
                        //                            'prefer_css_page_size' => true,
                        //                            'generate_document_outline' => true,
                        //                            'print_background' => false,
                        //                            'omit_background' => false,
                        //                            'landscape' => true,
                        //                            'scale' => 1.5,
                        //                            'native_page_ranges' => '1-5',
                        //                            'wait_delay' => '10s',
                        //                            'wait_for_expression' => 'window.globalVar === "ready"',
                        //                            'emulated_media_type' => 'screen',
                        //                            'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                        //                            'fail_on_http_status_codes' => [404],
                        //                            'fail_on_resource_http_status_codes' => [404],
                        //                            'fail_on_resource_loading_failed' => false,
                        //                            'fail_on_console_exceptions' => false,
                        //                            'skip_network_idle_event' => true,
                        //                            'pdf_format' => PdfFormat::Pdf3b->value,
                        //                            'pdf_universal_access' => true,
                        //                        ],
                        'office' => [
                            'password' => 'secret',
                            'pdf_format' => PdfFormat::Pdf1b->value,
                            'pdf_universal_access' => true,
                            'landscape' => false,
                            'native_page_ranges' => '1-2',
                            'do_not_export_form_fields' => false,
                            'single_page_sheets' => true,
                            'merge' => true,
                            'metadata' => [
                                'Author' => 'SensioLabs HTML',
                            ],
                            'allow_duplicate_field_names' => true,
                            'do_not_export_bookmarks' => false,
                            'export_bookmarks_to_pdf_destination' => true,
                            'export_placeholders' => true,
                            'export_notes' => true,
                            'export_notes_pages' => true,
                            'export_only_notes_pages' => true,
                            'export_notes_in_margin' => true,
                            'convert_ooo_target_to_pdf_target' => true,
                            'export_links_relative_fsys' => true,
                            'export_hidden_slides' => true,
                            'skip_empty_pages' => true,
                            'add_original_document_as_stream' => true,
                            'lossless_image_compression' => true,
                            'quality' => 80,
                            'reduce_image_resolution' => true,
                            'max_image_resolution' => ImageResolutionDPI::DPI150->value,
                            'download_from' => [
                                [
                                    'url' => 'http://example.com',
                                    'extraHttpHeaders' => [
                                        [
                                            'name' => 'MyHeader',
                                            'value' => 'MyValue',
                                        ],
                                    ],
                                ],
                            ],
                            'split_mode' => SplitMode::Pages->value,
                            'split_span' => '1-2',
                            'split_unify' => true,
                        ],
                        'merge' => [
                            'pdf_format' => PdfFormat::Pdf3b->value,
                            'pdf_universal_access' => true,
                            'metadata' => [
                                'Author' => 'SensioLabs HTML',
                            ],
                            'download_from' => [
                                [
                                    'url' => 'http://example.com',
                                    'extraHttpHeaders' => [
                                        [
                                            'name' => 'MyHeader',
                                            'value' => 'MyValue',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        //                        'convert' => [
                        //                            'pdf_format' => PdfFormat::Pdf2b->value,
                        //                            'pdf_universal_access' => true,
                        //                        ],
                    ],
                    //                    'screenshot' => [
                    //                        'html' => [
                    //                            'width' => 500,
                    //                            'height' => 500,
                    //                            'clip' => true,
                    //                            'format' => 'png',
                    //                            'omit_background' => true,
                    //                            'optimize_for_speed' => true,
                    //                            'wait_delay' => '10s',
                    //                            'wait_for_expression' => 'window.globalVar === "ready"',
                    //                            'emulated_media_type' => 'screen',
                    //                            'cookies' => [[
                    //                                'name' => 'cook',
                    //                                'value' => 'sensio',
                    //                                'domain' => 'sensiolabs.com',
                    //                                'secure' => true,
                    //                                'httpOnly' => true,
                    //                            ]],
                    //                            'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                    //                            'fail_on_http_status_codes' => [401],
                    //                            'fail_on_resource_http_status_codes' => [401],
                    //                            'fail_on_resource_loading_failed' => true,
                    //                            'fail_on_console_exceptions' => true,
                    //                            'skip_network_idle_event' => true,
                    //                        ],
                    //                        'url' => [
                    //                            'width' => 1000,
                    //                            'height' => 500,
                    //                            'clip' => true,
                    //                            'format' => 'jpeg',
                    //                            'quality' => 75,
                    //                            'omit_background' => false,
                    //                            'optimize_for_speed' => true,
                    //                            'wait_delay' => '5s',
                    //                            'wait_for_expression' => 'window.globalVar === "ready"',
                    //                            'emulated_media_type' => 'screen',
                    //                            'cookies' => [[
                    //                                'name' => 'cook_me',
                    //                                'value' => 'sensio',
                    //                                'domain' => 'sensiolabs.com',
                    //                            ]],
                    //                            'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                    //                            'fail_on_http_status_codes' => [401, 403],
                    //                            'fail_on_resource_http_status_codes' => [401, 403],
                    //                            'fail_on_resource_loading_failed' => false,
                    //                            'fail_on_console_exceptions' => false,
                    //                            'skip_network_idle_event' => true,
                    //                        ],
                    //                        'markdown' => [
                    //                            'width' => 1000,
                    //                            'height' => 500,
                    //                            'clip' => true,
                    //                            'format' => 'webp',
                    //                            'omit_background' => false,
                    //                            'optimize_for_speed' => false,
                    //                            'wait_delay' => '15s',
                    //                            'wait_for_expression' => 'window.globalVar === "ready"',
                    //                            'emulated_media_type' => 'screen',
                    //                            'cookies' => [[
                    //                                'name' => 'cook_me',
                    //                                'value' => 'sensio',
                    //                                'domain' => 'sensiolabs.com',
                    //                            ]],
                    //                            'extra_http_headers' => ['MyHeader' => 'MyValue', 'User-Agent' => 'MyValue'],
                    //                            'fail_on_http_status_codes' => [401, 403],
                    //                            'fail_on_resource_http_status_codes' => [401, 403],
                    //                            'fail_on_resource_loading_failed' => false,
                    //                            'fail_on_console_exceptions' => false,
                    //                            'skip_network_idle_event' => false,
                    //                        ],
                    //                    ],
                ],
            ],
        ];
    }

    public function testControllerListenerIsEnabledByDefault(): void
    {
        $extension = new SensiolabsGotenbergExtension();

        $containerBuilder = $this->getContainerBuilder(kernelDebug: false);
        $extension->load([[
            'http_client' => 'http_client',
        ]], $containerBuilder);

        self::assertContains('sensiolabs_gotenberg.http_kernel.stream_builder', $containerBuilder->getServiceIds());
    }

    //    public function testControllerListenerCanBeDisabled(): void
    //    {
    //        $extension = new SensiolabsGotenbergExtension();
    //
    //        $containerBuilder = $this->getContainerBuilder(kernelDebug: false);
    //        $extension->load([[
    //            'http_client' => 'http_client',
    //            'controller_listener' => false,
    //        ]], $containerBuilder);
    //
    //        self::assertNotContains('sensiolabs_gotenberg.http_kernel.stream_builder', $containerBuilder->getServiceIds());
    //    }
}
