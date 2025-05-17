<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;
use WechatOfficialAccountServerMessageBundle\Event\WechatOfficialAccountServerMessageRequestEvent;

class WechatOfficialAccountServerMessageRequestEventTest extends TestCase
{
    private WechatOfficialAccountServerMessageRequestEvent $event;
    
    protected function setUp(): void
    {
        $this->event = new WechatOfficialAccountServerMessageRequestEvent();
    }
    
    public function testGetSetMessage(): void
    {
        $message = $this->createMock(ServerMessage::class);
        
        $this->event->setMessage($message);
        $this->assertSame($message, $this->event->getMessage());
    }
    
    public function testGetSetAccount(): void
    {
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
        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $this->event);
    }
} 