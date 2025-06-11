<?php

namespace Sensiolabs\GotenbergBundle\Builder\Result;

use Symfony\Contracts\HttpClient\ResponseInterface;

class GotenbergAsyncResult extends AbstractGotenbergResult
{
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
    }

    protected function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        $this->ensureExecution();

        return $this->getResponse()->getStatusCode();
    }

    /**
     * @return array<string, array<string>>
     */
    public function getHeaders(): array
    {
        $this->ensureExecution();

        return $this->getResponse()->getHeaders();
    }
}
