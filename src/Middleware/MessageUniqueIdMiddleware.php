<?php
declare(strict_types=1);

namespace Snortlin\SymfonyMessengerExtensions\Middleware;

use Snortlin\SymfonyMessengerExtensions\Event\MessageUniqueIdEvent;
use Snortlin\SymfonyMessengerExtensions\Stamp\UniqueIdStamp;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;

readonly class MessageUniqueIdMiddleware implements MiddlewareInterface
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(UniqueIdStamp::class)) {
            $envelope = $envelope->with(new UniqueIdStamp());
        }

        $envelope = $stack->next()->handle($envelope, $stack);

        if ($this->eventDispatcher->hasListeners()) {
            $this->dispatchEvent($envelope);
        }

        return $envelope;
    }

    private function dispatchEvent(Envelope $envelope): void
    {
        if ($envelope->last(ReceivedStamp::class)) {
            $this->eventDispatcher->dispatch(new MessageUniqueIdEvent($envelope, MessageUniqueIdEvent::RECEIVED_ACTION));
        } elseif ($envelope->last(SentStamp::class)) {
            $this->eventDispatcher->dispatch(new MessageUniqueIdEvent($envelope, MessageUniqueIdEvent::SENT_ACTION));
        } else {
            $this->eventDispatcher->dispatch(new MessageUniqueIdEvent($envelope, MessageUniqueIdEvent::HANDLING_ACTION));
        }
    }
}
