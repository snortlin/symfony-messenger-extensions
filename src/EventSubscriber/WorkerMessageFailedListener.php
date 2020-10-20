<?php

namespace Snortlin\SymfonyMessengerExtensions\EventSubscriber;

use App\Messenger\Stamp\UniqueIdStamp;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class WorkerMessageFailedListener
{
    private ?string $messengerLogDirectory;
    private bool $logRetryableMessages;

    public function __construct(string $messengerLogDirectory, bool $logRetryableMessages = false)
    {
        $this->messengerLogDirectory = $messengerLogDirectory;
        $this->logRetryableMessages = $logRetryableMessages;
    }

    /**
     * @param WorkerMessageFailedEvent $event
     * @throws \ErrorException
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
            'createdAt' => date('d. m. Y H:i:s'),
            'willRetry' => $event->willRetry(),
        ];

        /** @var UniqueIdStamp|null $uniqueIdStamp */
        if (null !== ($uniqueIdStamp = $envelope->last(UniqueIdStamp::class))) {
            $header['uniqueIdStamp'] = $uniqueIdStamp->getUniqueId();
        }

        $checksum = md5(serialize($envelope));

        $filename = sprintf(
            '%s%s%s-%s-%s.html',
            realpath($this->messengerLogDirectory), DIRECTORY_SEPARATOR, $event->getReceiverName(), date('Y-m-d\TH-i-s'), substr($checksum, 0, 10)
        );

        if ($stream = fopen($filename, 'w+')) {
            $cloner = new VarCloner();
            $dumper = new HtmlDumper();

            $dumper->dump($cloner->cloneVar($header), $stream);
            // $dumper->dump($cloner->cloneVar($envelope), $stream); // je soucasti $throwable
            $dumper->dump($cloner->cloneVar($throwable), $stream);

            fclose($stream);
        } else {
            throw new \RuntimeException(sprintf('Unable to write to log file "%s".', $filename));
        }
    }
}
