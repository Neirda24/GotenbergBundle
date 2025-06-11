<?php

namespace Sensiolabs\GotenbergBundle\Test\Builder;

use Sensiolabs\GotenbergBundle\Builder\Result\GotenbergFileResult;
use Sensiolabs\GotenbergBundle\Processor\NullProcessor;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use function array_merge;
use function basename;

/**
 * @extends GotenbergFileResult<null>
 */
class GotenbergMockFileResult extends GotenbergFileResult
{
    /**
     * @param array<string, array<string>> $headers
     */
    public static function fromFile(
        string $path,
        string|null $fileName = null,
        array $headers = [],
        string $disposition = HeaderUtils::DISPOSITION_INLINE,
    ): self {
        $client = new MockHttpClient([
            MockResponse::fromFile($path, [
                'http_code' => 200,
                'response_headers' => array_merge($headers, [
                    'Content-Disposition' => HeaderUtils::makeDisposition($disposition, $fileName ?? basename($path)),
                ]),
            ])
        ]);

        $response = $client->request('GET', '/');

        return new self($client->stream($response), new NullProcessor(), $disposition);
    }

    /**
     * @param array<string, array<string>> $headers
     */
    public static function fromContent(
        string $content,
        string $fileName,
        array $headers = [],
        string $disposition = HeaderUtils::DISPOSITION_INLINE,
    ): self {
        $client = new MockHttpClient([
            new MockResponse($content, [
                'http_code' => 200,
                'response_headers' => array_merge($headers, [
                    'Content-Disposition' => HeaderUtils::makeDisposition($disposition, $fileName),
                ]),
            ])
        ]);

        $response = $client->request('GET', '/');

        return new self($client->stream($response), new NullProcessor(), $disposition);
    }
}
