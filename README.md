# Symfony Messenger Extensions

## Installation

The preferred method of installation is via [Composer](https://getcomposer.org/):

```bash
composer require snortlin/symfony-messenger-extensions
```

## Usage

### Messenger error dump listener

```yaml
# /config/services.yaml
services:
    Snortlin\SymfonyMessengerExtensions\EventSubscriber\WorkerMessageFailedListener:
        arguments:
            # Error log dump path
            $messengerLogDirectory: '%kernel.logs_dir%/messenger/errors'
            # Optionally, log dump also for retryable messages (default false)
            $logRetryableMessages: true
        tags:
            - { name: kernel.event_listener, event: Symfony\Component\Messenger\Event\WorkerMessageFailedEvent }
```

### Message UniqueId

```yaml
# /config/services.yaml
services:
    Snortlin\SymfonyMessengerExtensions\Middleware\MessageUniqueIdMiddleware: ~

# /config/packages/messenger.yaml
framework:
    messenger:
        buses:
            messenger.bus.default:
                middleware:
                    - Snortlin\SymfonyMessengerExtensions\Middleware\MessageUniqueIdMiddleware
```

### Constant Retry Strategy

```yaml
# /config/services.yaml
services:
    app.messenger.retry.constant_retry_strategy:
        class: Snortlin\SymfonyMessengerExtensions\Retry\ConstantRetryStrategy
        arguments:
            # Retry delays in ms, CSV string format (1000,2000,5000) => delay 1s, 2s, 5s.
            $retries: '1000,2000,5000'

# /config/packages/messenger.yaml
framework:
    messenger:
        transports:
            my_transport:
                retry_strategy:
                    service: app.messenger.retry.constant_retry_strategy
```

### Infinite Constant Retry Strategy

```yaml
# /config/services.yaml
services:
    app.messenger.retry.infinite_constant_retry_strategy:
        class: Snortlin\SymfonyMessengerExtensions\Retry\InfiniteConstantRetryStrategy
        arguments:
            # Retry delays in ms, CSV string format (1000,2000,5000) => delay 1s, 2s, 5s, 5s, ...
            $retries: '1000,2000,5000'

# /config/packages/messenger.yaml
framework:
    messenger:
        transports:
            my_transport:
                retry_strategy:
                    service: app.messenger.retry.infinite_constant_retry_strategy
```
