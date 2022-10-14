<?php

namespace Snortlin\SymfonyMessengerExtensions\Stamp;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Stamp\StampInterface;

class UniqueIdStamp implements StampInterface
{
    public function __construct(private ?string $uniqueId = null)
    {
        $this->uniqueId ??= Uuid::uuid4()->toString();
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}
