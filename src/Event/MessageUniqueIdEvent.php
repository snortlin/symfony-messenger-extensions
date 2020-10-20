<?php

namespace Snortlin\SymfonyMessengerExtensions\Event;

use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

class MessageUniqueIdEvent extends Event
{
    public const RECEIVED_ACTION = 'received_action';
    public const SENT_ACTION = 'sent_action';
    public const HANDLING_ACTION = 'handling_action';

    private Envelope $envelope;
    private string $state;

    public function __construct(Envelope $envelope, string $state)
    {
        $this->envelope = $envelope;
        $this->state = $state;
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
