<?php

namespace Snortlin\SymfonyMessengerExtensions\Retry;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

class ConstantRetryStrategy implements RetryStrategyInterface
{
    private array $retries;

    /**
     * @param int[] $retries E.g. [1000,2000,5000] in ms => waits 1s, 2s, 5s.
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
        return RedeliveryStamp::getRetryCountFromEnvelope($message) < count($this->retries);
    }

    /**
     * @inheritDoc
     */
    public function getWaitingTime(Envelope $message, \Throwable $throwable = null): int
    {
        return (int) ($this->retries[RedeliveryStamp::getRetryCountFromEnvelope($message)]);
    }
}
