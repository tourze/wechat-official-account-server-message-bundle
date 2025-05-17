<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

class ServerMessageTest extends TestCase
{
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
        $account = $this->createMock(Account::class);
        
        $serverMessage->setAccount($account);
        $this->assertSame($account, $serverMessage->getAccount());
    }
    
    public function testGetSetContext(): void
    {
        $serverMessage = new ServerMessage();
        $context = ['foo' => 'bar', 'baz' => 123];
        
        $this->assertInstanceOf(ServerMessage::class, $serverMessage->setContext($context));
        $this->assertEquals($context, $serverMessage->getContext());
        
        $this->assertInstanceOf(ServerMessage::class, $serverMessage->setContext(null));
        $this->assertNull($serverMessage->getContext());
    }
    
    public function testGetSetMsgId(): void
    {
        $serverMessage = new ServerMessage();
        $msgId = 'test_msg_id_12345';
        
        $this->assertInstanceOf(ServerMessage::class, $serverMessage->setMsgId($msgId));
        $this->assertEquals($msgId, $serverMessage->getMsgId());
    }
    
    public function testGetSetToUserName(): void
    {
        $serverMessage = new ServerMessage();
        $toUserName = 'test_to_user_name';
        
        $this->assertInstanceOf(ServerMessage::class, $serverMessage->setToUserName($toUserName));
        $this->assertEquals($toUserName, $serverMessage->getToUserName());
    }
    
    public function testGetSetFromUserName(): void
    {
        $serverMessage = new ServerMessage();
        $fromUserName = 'test_from_user_name';
        
        $this->assertInstanceOf(ServerMessage::class, $serverMessage->setFromUserName($fromUserName));
        $this->assertEquals($fromUserName, $serverMessage->getFromUserName());
    }
    
    public function testGetSetMsgType(): void
    {
        $serverMessage = new ServerMessage();
        $msgType = 'text';
        
        $this->assertInstanceOf(ServerMessage::class, $serverMessage->setMsgType($msgType));
        $this->assertEquals($msgType, $serverMessage->getMsgType());
    }
    
    public function testGetSetCreateTime(): void
    {
        $serverMessage = new ServerMessage();
        $createTime = time();
        
        $this->assertInstanceOf(ServerMessage::class, $serverMessage->setCreateTime($createTime));
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