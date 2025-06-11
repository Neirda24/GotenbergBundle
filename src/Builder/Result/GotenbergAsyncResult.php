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
}
