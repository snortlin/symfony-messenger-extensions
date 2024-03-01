<?php
declare(strict_types=1);

namespace Snortlin\SymfonyMessengerExtensions\EventSubscriber;

use Snortlin\SymfonyMessengerExtensions\Stamp\UniqueIdStamp;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

readonly class WorkerMessageFailedListener
{
    public function __construct(private string $messengerLogDirectory,
                                private bool   $logRetryableMessages = false)
    {
    }

    /**
     * @param WorkerMessageFailedEvent $event
     */
    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry() && !$this->logRetryableMessages) {
            return;
        }

        if (!is_dir($this->messengerLogDirectory)) {
            if (!@mkdir($this->messengerLogDirectory, 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create log directory "%s".', $this->messengerLogDirectory));
            }
        }

        $envelope = $event->getEnvelope();
        $throwable = $event->getThrowable();

        $header = [
            'error' => $throwable->getMessage(),
            'createdAt' => date(\DateTimeInterface::RFC3339),
            'willRetry' => $event->willRetry(),
        ];

        /** @var UniqueIdStamp|null $uniqueIdStamp */
        if (null !== ($uniqueIdStamp = $envelope->last(UniqueIdStamp::class))) {
            $header['uniqueIdStamp'] = $uniqueIdStamp->getUniqueId();
        }

        try {
            $checksum = $uniqueIdStamp?->getUniqueId() ?? md5(serialize($envelope->getMessage()));
        } catch (\Throwable) {
            $checksum = Uuid::v4()->toRfc4122();
        }

        $filename = sprintf(
            '%s%s%s_%s_%s.html',
            realpath($this->messengerLogDirectory), DIRECTORY_SEPARATOR, $event->getReceiverName(), strtr(date(\DateTimeInterface::RFC3339), ':', '-'), $checksum
        );

        if ($stream = fopen($filename, 'w+')) {
            $cloner = new VarCloner();
            $dumper = new HtmlDumper();

            $dumper->dump($cloner->cloneVar($header), $stream);
            // $dumper->dump($cloner->cloneVar($envelope), $stream); // is part of $throwable
            $dumper->dump($cloner->cloneVar($throwable), $stream);

            fclose($stream);
        } else {
            throw new \RuntimeException(sprintf('Unable to write to log file "%s".', $filename));
        }
    }
}
