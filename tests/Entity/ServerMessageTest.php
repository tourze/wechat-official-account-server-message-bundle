<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

/**
 * @internal
 */
#[CoversClass(ServerMessage::class)]
final class ServerMessageTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ServerMessage();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'context' => ['context', []],
            'msgId' => ['msgId', 'test_msg_id'],
            'toUserName' => ['toUserName', 'test_user'],
            'fromUserName' => ['fromUserName', 'test_from'],
            'msgType' => ['msgType', 'text'],
            'createTime' => ['createTime', 1234567890],
        ];
    }

    public function testGetSetId(): void
    {
        $serverMessage = new ServerMessage();
        $reflection = new \ReflectionClass($serverMessage);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($serverMessage, 123);

        $this->assertEquals(123, $serverMessage->getId());
    }

    public function testGetSetAccount(): void
    {
        $serverMessage = new ServerMessage();
        // 使用具体类 Account 的 Mock，理由：
        // 1. Account是数据实体类，需要模拟其数据访问方法
        // 2. 避免测试依赖真实的数据库记录
        // 3. 可以控制Account的返回值用于测试特定场景
        $account = $this->createMock(Account::class);

        $serverMessage->setAccount($account);
        $this->assertSame($account, $serverMessage->getAccount());
    }

    public function testGetSetContext(): void
    {
        $serverMessage = new ServerMessage();
        $context = ['foo' => 'bar', 'baz' => 123];

        $serverMessage->setContext($context);
        $this->assertEquals($context, $serverMessage->getContext());

        $serverMessage->setContext(null);
        $this->assertNull($serverMessage->getContext());
    }

    public function testGetSetMsgId(): void
    {
        $serverMessage = new ServerMessage();
        $msgId = 'test_msg_id_12345';

        $serverMessage->setMsgId($msgId);
        $this->assertEquals($msgId, $serverMessage->getMsgId());
    }

    public function testGetSetToUserName(): void
    {
        $serverMessage = new ServerMessage();
        $toUserName = 'test_to_user_name';

        $serverMessage->setToUserName($toUserName);
        $this->assertEquals($toUserName, $serverMessage->getToUserName());
    }

    public function testGetSetFromUserName(): void
    {
        $serverMessage = new ServerMessage();
        $fromUserName = 'test_from_user_name';

        $serverMessage->setFromUserName($fromUserName);
        $this->assertEquals($fromUserName, $serverMessage->getFromUserName());
    }

    public function testGetSetMsgType(): void
    {
        $serverMessage = new ServerMessage();
        $msgType = 'text';

        $serverMessage->setMsgType($msgType);
        $this->assertEquals($msgType, $serverMessage->getMsgType());
    }

    public function testGetSetCreateTime(): void
    {
        $serverMessage = new ServerMessage();
        $createTime = time();

        $serverMessage->setCreateTime($createTime);
        $this->assertEquals($createTime, $serverMessage->getCreateTime());
    }

    public function testGenMsgId(): void
    {
        // 测试有MsgId的情况
        $message = ['MsgId' => '12345', 'FromUserName' => 'user123', 'CreateTime' => 1623456789];
        $this->assertEquals('12345', ServerMessage::genMsgId($message));

        // 测试没有MsgId的情况
        $message = ['FromUserName' => 'user123', 'CreateTime' => 1623456789];
        $this->assertEquals('user123_1623456789', ServerMessage::genMsgId($message));
    }

    public function testCreateFromMessage(): void
    {
        $message = [
            'MsgId' => '12345',
            'MsgType' => 'text',
            'ToUserName' => 'to_user_123',
            'FromUserName' => 'from_user_456',
            'CreateTime' => 1623456789,
            'Content' => 'Hello world',
        ];

        $serverMessage = ServerMessage::createFromMessage($message);

        $this->assertInstanceOf(ServerMessage::class, $serverMessage);
        $this->assertEquals('12345', $serverMessage->getMsgId());
        $this->assertEquals('text', $serverMessage->getMsgType());
        $this->assertEquals('to_user_123', $serverMessage->getToUserName());
        $this->assertEquals('from_user_456', $serverMessage->getFromUserName());
        $this->assertEquals(1623456789, $serverMessage->getCreateTime());
        $this->assertEquals($message, $serverMessage->getContext());
    }
}
