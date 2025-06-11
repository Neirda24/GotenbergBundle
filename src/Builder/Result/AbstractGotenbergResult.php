<?php

namespace Sensiolabs\GotenbergBundle\Builder\Result;

use Sensiolabs\GotenbergBundle\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractGotenbergResult
{
    private bool $executed = false;

    abstract protected function getResponse(): ResponseInterface;

    protected function ensureExecution(): void
    {
        if ($this->executed) {
            return;
        }

        try {
            if (!\in_array($this->getResponse()->getStatusCode(), [200, 204], true)) {
                throw new ClientException($this->getResponse()->getContent(false), $this->getResponse()->getStatusCode());
            }
        } catch (ExceptionInterface $e) {
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->executed = true;
        }
    }
}
