<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;
use WechatOfficialAccountServerMessageBundle\Event\WechatOfficialAccountServerMessageRequestEvent;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountServerMessageRequestEvent::class)]
final class WechatOfficialAccountServerMessageRequestEventTest extends AbstractEventTestCase
{
    private WechatOfficialAccountServerMessageRequestEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new WechatOfficialAccountServerMessageRequestEvent();
    }

    public function testGetSetMessage(): void
    {
        // 使用具体类 ServerMessage 的 Mock，理由：
        // 1. ServerMessage是数据实体类，需要模拟其数据访问方法
        // 2. 避免测试依赖真实的数据库记录和复杂的实体初始化
        // 3. 可以控制ServerMessage的返回值用于测试特定场景
        $message = $this->createMock(ServerMessage::class);

        $this->event->setMessage($message);
        $this->assertSame($message, $this->event->getMessage());
    }

    public function testGetSetAccount(): void
    {
        // 使用具体类 Account 的 Mock，理由：
        // 1. Account是数据实体类，需要模拟其数据访问方法
        // 2. 避免测试依赖真实的数据库记录
        // 3. 可以控制Account的返回值用于测试特定场景
        $account = $this->createMock(Account::class);

        $this->event->setAccount($account);
        $this->assertSame($account, $this->event->getAccount());
    }

    public function testGetSetUser(): void
    {
        $user = $this->createMock(UserInterface::class);

        // 测试设置用户
        $this->event->setUser($user);
        $this->assertSame($user, $this->event->getUser());

        // 测试设置空用户
        $this->event->setUser(null);
        $this->assertNull($this->event->getUser());
    }

    public function testGetSetResponse(): void
    {
        $response = ['ToUserName' => 'user123', 'FromUserName' => 'official456', 'Content' => 'Hello'];

        // 默认应该为null
        $this->assertNull($this->event->getResponse());

        // 测试设置响应
        $this->event->setResponse($response);
        $this->assertSame($response, $this->event->getResponse());

        // 测试设置空响应
        $this->event->setResponse(null);
        $this->assertNull($this->event->getResponse());
    }

    public function testInheritsFromSymfonyEvent(): void
    {
        $this->assertInstanceOf(Event::class, $this->event);
    }
}
