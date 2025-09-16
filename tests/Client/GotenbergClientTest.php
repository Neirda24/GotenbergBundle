<?php

namespace Sensiolabs\GotenbergBundle\Tests\Client;

use PHPUnit\Framework\TestCase;
use Sensiolabs\GotenbergBundle\Builder\Payload;
use Sensiolabs\GotenbergBundle\Client\GotenbergClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

final class GotenbergClientTest extends TestCase
{
    public function testCallIsCorrectlyFormatted(): void
    {
        /** @var string $stream */
        $stream = file_get_contents(__DIR__.'/../Fixtures/pdf/simple_pdf.pdf');
        $mockResponse = new MockResponse($stream, [
            'http_code' => Response::HTTP_OK,
            'response_headers' => [
                'accept-ranges' => 'bytes',
                'content-disposition' => 'attachment; filename="simple_pdf.pdf"',
                'content-length' => \strlen($stream),
                'content-type' => 'application/pdf',
            ],
        ]);

        $mockClient = new MockHttpClient([$mockResponse], baseUri: 'http://localhost:3000');

        $payload = new Payload(
            [['url' => 'https://google.com']],
            ['SomeHeader' => 'SomeValue'],
        );

        $gotenbergClient = new GotenbergClient($mockClient);
        $response = $gotenbergClient->call('/some/url', $payload);

        static::assertSame(1, $mockClient->getRequestsCount());
        static::assertSame('POST', $mockResponse->getRequestMethod());
        static::assertSame('http://localhost:3000/some/url', $mockResponse->getRequestUrl());

        $requestHeaders = array_reduce($mockResponse->getRequestOptions()['headers'], static function (array $carry, string $header): array {
            [$key, $value] = explode(': ', $header, 2);

            $carry[$key] ??= [];
            $carry[$key][] = $value;

            return $carry;
        }, []);

        static::assertArrayHasKey('SomeHeader', $requestHeaders);
        static::assertSame('SomeValue', $requestHeaders['SomeHeader'][0]);

        static::assertArrayHasKey('Content-Type', $requestHeaders);
        $requestContentType = $requestHeaders['Content-Type'][0];

        static::assertMatchesRegularExpression('#^multipart/form-data; boundary=(?P<boundary>.*)$#', $requestContentType);

        $responseHeaders = $response->getHeaders();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        static::assertSame('application/pdf', $responseHeaders['content-type'][0]);
        static::assertSame('attachment; filename="simple_pdf.pdf"', $responseHeaders['content-disposition'][0]);
        static::assertSame('13624', $responseHeaders['content-length'][0]);
    }
}
