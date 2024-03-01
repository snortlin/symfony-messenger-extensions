<?php
declare(strict_types=1);

namespace Snortlin\SymfonyMessengerExtensions\Event;

use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

class MessageUniqueIdEvent extends Event
{
    public const string RECEIVED_ACTION = 'received_action';
    public const string SENT_ACTION = 'sent_action';
    public const string HANDLING_ACTION = 'handling_action';

    public function __construct(private readonly Envelope $envelope,
                                private readonly string   $state)
    {
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
