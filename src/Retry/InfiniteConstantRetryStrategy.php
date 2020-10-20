<?php

namespace Snortlin\SymfonyMessengerExtensions\Retry;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

class InfiniteConstantRetryStrategy implements RetryStrategyInterface
{
    private array $retries;

    /**
     * @param int[] $retries E.g. [1000,2000,5000] in ms => waits 1s, 2s, 5s, 5s, ...
     */
    public function __construct(array $retries = [])
    {
        if (array_filter($retries, fn($v) => ((int) $v) > 0) !== $retries) {
            throw new \InvalidArgumentException('All retries must be integers greater than zero.');
        }

        $this->retries = $retries;
    }

    /**
     * @inheritDoc
     */
    public function isRetryable(Envelope $message, \Throwable $throwable = null): bool
    {
        return count($this->retries) > 0;
    }

    /**
     * @inheritDoc
     */
    public function getWaitingTime(Envelope $message, \Throwable $throwable = null): int
    {
        return (int) ($this->retries[RedeliveryStamp::getRetryCountFromEnvelope($message)] ?? end($this->retries));
    }
}
