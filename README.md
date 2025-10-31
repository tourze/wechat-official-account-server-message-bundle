# WechatOfficialAccountServerMessageBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)
[![Build Status](https://img.shields.io/travis/tourze/wechat-official-account-server-message-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/wechat-official-account-server-message-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/wechat-official-account-server-message-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/wechat-official-account-server-message-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)

A Symfony bundle for handling WeChat Official Account server messages and
webhooks.

## Features

- **Message Processing**: Handle incoming WeChat Official Account server messages
- **Webhook Support**: Process WeChat server callbacks with signature validation
- **Message Persistence**: Store server messages in database with automatic cleanup
- **Event Dispatching**: Dispatch events for custom message handling
- **Async Processing**: Support for asynchronous message handling via Symfony Messenger
- **Lock Management**: Prevent duplicate message processing
- **User Synchronization**: Automatically sync user information from WeChat

## Installation

```bash
composer require tourze/wechat-official-account-server-message-bundle
```

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM

## Quick Start

```php
<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WechatOfficialAccountServerMessageBundle\Event\WechatOfficialAccountServerMessageRequestEvent;

class MessageHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            WechatOfficialAccountServerMessageRequestEvent::class => 'handleMessage',
        ];
    }

    public function handleMessage(WechatOfficialAccountServerMessageRequestEvent $event): void
    {
        $message = $event->getMessage();
        
        if ($message->getMsgType() === 'text') {
            $response = [
                'ToUserName' => $message->getFromUserName(),
                'FromUserName' => $message->getToUserName(),
                'CreateTime' => time(),
                'MsgType' => 'text',
                'Content' => 'Hello, World!',
            ];
            
            $event->setResponse($response);
        }
    }
}
```

## Advanced Usage

### Custom Message Processing

For more complex message handling scenarios:

```php
// Configure async processing for specific message types
if (in_array($message->getMsgType(), ['TEMPLATESENDJOBFINISH', 'VIEW', 'view_miniprogram'])) {
    // Message will be processed asynchronously
}

// Access full message context
$context = $message->getContext();
$customData = $context['CustomField'] ?? null;
```

## Configuration

### Bundle Registration

The bundle will be automatically registered if you use Symfony Flex. Otherwise, add it to your `config/bundles.php`:

```php
return [
    // ...
    WechatOfficialAccountServerMessageBundle\WechatOfficialAccountServerMessageBundle::class => ['all' => true],
];
```

### Environment Variables

Configure message retention period:

```env
# Number of days to keep server messages (default: 7)
WECHAT_OFFICIAL_ACCOUNT_MESSAGE_PERSIST_DAY_NUM=7
```

## Usage

### Webhook Endpoint

The bundle provides a webhook endpoint at `/wechat/official-account/server/{id}` where `{id}` can be either:
- Account ID
- WeChat App ID

### Message Types Supported

- Text messages
- Image messages
- Voice messages
- Video messages
- Location messages
- Link messages
- Event messages (subscribe, unsubscribe, click, etc.)

### Event Handling

The bundle automatically handles all message types and dispatches events for
custom processing. See the Quick Start section for basic usage.

### Async Processing

Some message types are processed asynchronously via Symfony Messenger:

- `TEMPLATESENDJOBFINISH`
- `VIEW`
- `view_miniprogram`

## Database Schema

The bundle creates a `wechat_official_account_message` table to store server messages with the following fields:

- `id`: Primary key
- `account_id`: Reference to WeChat account
- `msg_id`: Unique message identifier
- `to_user_name`: Recipient OpenID
- `from_user_name`: Sender OpenID
- `msg_type`: Message type
- `create_time`: Message timestamp
- `context`: Full message context (JSON)

## Automatic Cleanup

Server messages are automatically cleaned up using the Schedule Entity Clean Bundle. Messages older than the configured retention period are deleted daily at 5:26 AM.

## Security

- Message signature validation for encrypted messages
- IP validation (TODO: implement WeChat IP whitelist check)
- Lock-based duplicate message prevention

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.