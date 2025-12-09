<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'test' => true,
        'http_client' => [
            'scoped_clients' => [
                'gotenberg.client' => [
                    'base_uri' => 'http://localhost:9000',
                ],
            ],
        ],
    ]);

    $container->extension('sensiolabs_gotenberg', [
        'http_client' => 'gotenberg.client',
        'webhook' => [
            'some_webhook' => [
                'success' => [
                    'url' => 'https://example.com/success',
                ],
                'error' => [
                    'url' => 'https://example.com/error',
                ],
            ],
        ],
    ]);
};
