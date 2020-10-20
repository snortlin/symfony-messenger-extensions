<?php

namespace Snortlin\SymfonyMessengerExtensions\Stamp;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Stamp\StampInterface;

class UniqueIdStamp implements StampInterface
{
    private string $uniqueId;

    public function __construct()
    {
        $this->uniqueId = Uuid::uuid4()->toString();
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}
