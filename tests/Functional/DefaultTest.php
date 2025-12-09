<?php

namespace Sensiolabs\GotenbergBundle\Tests\Functional;

class DefaultTest extends AbstractGotenbergWebTestCase
{
    public function testBundleDefault(): void
    {
        $this->expectNotToPerformAssertions();

        static::bootKernel(['test_case' => 'Default']);
    }

    public function testWebhookConfigurationRegistryIsPopulated(): void
    {
        static::bootKernel(['test_case' => 'Default']);

        $webhookConfigurationRegistry = self::getContainer()->get('.sensiolabs_gotenberg.webhook_configuration_registry');

        self::assertEqualsCanonicalizing([], $webhookConfigurationRegistry->get('some_webhook'));
    }
}
