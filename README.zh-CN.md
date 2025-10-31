# WechatOfficialAccountServerMessageBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)
[![Build Status](https://img.shields.io/travis/tourze/wechat-official-account-server-message-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/wechat-official-account-server-message-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/wechat-official-account-server-message-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/wechat-official-account-server-message-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-official-account-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-server-message-bundle)

用于处理微信公众号服务端消息和回调的 Symfony Bundle。

## 功能特性

- **消息处理**: 处理微信公众号服务端推送的消息
- **回调支持**: 处理微信服务器回调，支持签名验证
- **消息持久化**: 将服务端消息存储到数据库，支持自动清理
- **事件分发**: 分发事件供自定义消息处理
- **异步处理**: 通过 Symfony Messenger 支持异步消息处理
- **锁管理**: 防止重复消息处理
- **用户同步**: 自动从微信同步用户信息

## 安装

```bash
composer require tourze/wechat-official-account-server-message-bundle
```

## 环境要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM

## 快速开始

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
                'Content' => '你好，世界！',
            ];
            
            $event->setResponse($response);
        }
    }
}
```

## 高级用法

### 自定义消息处理

对于更复杂的消息处理场景：

```php
// 配置特定消息类型的异步处理
if (in_array($message->getMsgType(), ['TEMPLATESENDJOBFINISH', 'VIEW', 'view_miniprogram'])) {
    // 消息将异步处理
}

// 访问完整消息上下文
$context = $message->getContext();
$customData = $context['CustomField'] ?? null;
```

## 配置

### Bundle 注册

如果使用 Symfony Flex，Bundle 将自动注册。否则，请在 `config/bundles.php` 中添加：

```php
return [
    // ...
    WechatOfficialAccountServerMessageBundle\WechatOfficialAccountServerMessageBundle::class => ['all' => true],
];
```

### 环境变量

配置消息保留期限：

```env
# 服务端消息保留天数（默认：7天）
WECHAT_OFFICIAL_ACCOUNT_MESSAGE_PERSIST_DAY_NUM=7
```

## 使用方法

### 回调端点

Bundle 提供回调端点 `/wechat/official-account/server/{id}`，其中 `{id}` 可以是：
- 账户 ID
- 微信 App ID

### 支持的消息类型

- 文本消息
- 图片消息
- 语音消息
- 视频消息
- 位置消息
- 链接消息
- 事件消息（关注、取消关注、点击等）

### 事件处理

Bundle 自动处理所有消息类型并分发事件供自定义处理。
请参阅快速开始部分了解基本用法。

### 异步处理

以下消息类型通过 Symfony Messenger 异步处理：

- `TEMPLATESENDJOBFINISH`
- `VIEW`
- `view_miniprogram`

## 数据库结构

Bundle 创建 `wechat_official_account_message` 表来存储服务端消息，包含以下字段：

- `id`: 主键
- `account_id`: 微信账户引用
- `msg_id`: 唯一消息标识符
- `to_user_name`: 接收者 OpenID
- `from_user_name`: 发送者 OpenID
- `msg_type`: 消息类型
- `create_time`: 消息时间戳
- `context`: 完整消息上下文（JSON）

## 自动清理

服务端消息使用 Schedule Entity Clean Bundle 自动清理。超过配置保留期限的消息将在每天凌晨 5:26 自动删除。

## 安全性

- 加密消息的签名验证
- IP 验证（TODO：实现微信 IP 白名单检查）
- 基于锁的重复消息预防

## 贡献

1. Fork 仓库
2. 创建功能分支
3. 进行修改
4. 为新功能添加测试
5. 提交 Pull Request

## 许可证

此 Bundle 基于 MIT 许可证发布。详见 [LICENSE](LICENSE) 文件。
