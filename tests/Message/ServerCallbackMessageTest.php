<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Message;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountServerMessageBundle\Message\ServerCallbackMessage;

class ServerCallbackMessageTest extends TestCase
{
    public function testGetSetMessage(): void
    {
        $serverCallbackMessage = new ServerCallbackMessage();
        $message = [
            'MsgType' => 'text',
            'Content' => 'Hello World',
            'MsgId' => '123456',
            'FromUserName' => 'user123',
            'ToUserName' => 'official456',
            'CreateTime' => time(),
        ];
        
        $serverCallbackMessage->setMessage($message);
        $this->assertSame($message, $serverCallbackMessage->getMessage());
    }
    
    public function testGetSetAccountId(): void
    {
        $serverCallbackMessage = new ServerCallbackMessage();
        $accountId = 'account_12345';
        
        $serverCallbackMessage->setAccountId($accountId);
        $this->assertEquals($accountId, $serverCallbackMessage->getAccountId());
    }
    
    public function testImplementsAsyncMessageInterface(): void
    {
        $serverCallbackMessage = new ServerCallbackMessage();
        $this->assertInstanceOf(\Tourze\Symfony\Async\Message\AsyncMessageInterface::class, $serverCallbackMessage);
    }
} 