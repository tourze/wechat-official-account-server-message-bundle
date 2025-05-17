<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountServerMessageBundle\Controller\ServerController;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;
use WechatOfficialAccountServerMessageBundle\Event\WechatOfficialAccountServerMessageRequestEvent;
use WechatOfficialAccountServerMessageBundle\Message\ServerCallbackMessage;
use WechatOfficialAccountServerMessageBundle\MessageHandler\ServerCallbackHandler;

class ServerControllerTest extends TestCase
{
    private ServerController $controller;
    private MessageBusInterface $messageBus;
    private ServerCallbackHandler $callbackHandler;
    private LoggerInterface $logger;
    private AccountRepository $accountRepository;
    
    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->callbackHandler = $this->createMock(ServerCallbackHandler::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->accountRepository = $this->createMock(AccountRepository::class);
        
        $this->controller = new ServerController(
            $this->messageBus,
            $this->callbackHandler,
            $this->logger
        );
        
        // 由于ServerController继承自AbstractController，需要模拟其行为
        $controllerReflection = new \ReflectionClass(AbstractController::class);
        if ($controllerReflection->hasProperty('container')) {
            $containerProperty = $controllerReflection->getProperty('container');
            $containerProperty->setAccessible(true);
            $containerProperty->setValue($this->controller, null);
        }
    }
    
    public function testServerWithEchostr(): void
    {
        $request = Request::create('/wechat/official-account/server/12345', 'GET');
        $request->query->set('echostr', 'test_echo_string');
        
        $account = $this->createMock(Account::class);
        $this->accountRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '12345'])
            ->willReturn($account);
        
        $response = $this->controller->server('12345', $request, $this->accountRepository, $this->logger);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('test_echo_string', $response->getContent());
    }
    
    public function testServerWithNoAccount(): void
    {
        $request = Request::create('/wechat/official-account/server/12345', 'POST', [], [], [], [], '<xml><Content>test</Content></xml>');
        
        // 在PHPUnit 10中，不再支持at()方法，需要使用consecutive()
        $this->accountRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(
                null, // 第一次调用返回null (id查询)
                null  // 第二次调用返回null (appId查询)
            );
            
        $this->logger->expects($this->once())
            ->method('error')
            ->with('找不到公众号', $this->anything());
            
        $response = $this->controller->server('12345', $request, $this->accountRepository, $this->logger);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('success', $response->getContent());
    }
    
    public function testServerWithEmptyContent(): void
    {
        $account = $this->createMock(Account::class);
        $request = Request::create('/wechat/official-account/server/12345', 'POST');
        
        $this->accountRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '12345'])
            ->willReturn($account);
            
        $this->logger->expects($this->once())
            ->method('error')
            ->with('找不到有效的提交请求内容', $this->anything());
            
        $response = $this->controller->server('12345', $request, $this->accountRepository, $this->logger);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('success', $response->getContent());
    }
    
    public function testServerWithTemplateEvent(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(12345);
        
        $xmlContent = '<xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>1234567890</CreateTime>
            <MsgType><![CDATA[event]]></MsgType>
            <Event><![CDATA[TEMPLATESENDJOBFINISH]]></Event>
            <MsgID>1234567890</MsgID>
        </xml>';
        
        $request = Request::create('/wechat/official-account/server/12345', 'POST', [], [], [], [], $xmlContent);
        
        $this->accountRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '12345'])
            ->willReturn($account);
            
        // 由于MessageBus::dispatch返回Envelope，而Envelope是final类，无法mock，
        // 我们只需要验证dispatch被调用，不关心返回值
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function($message) {
                return $message instanceof ServerCallbackMessage
                    && $message->getAccountId() === '12345'
                    && isset($message->getMessage()['Event'])
                    && $message->getMessage()['Event'] === 'TEMPLATESENDJOBFINISH';
            }))
            ->willReturn(new Envelope(new \stdClass()));
            
        $this->callbackHandler->expects($this->never())
            ->method('__invoke');
            
        $response = $this->controller->server('12345', $request, $this->accountRepository, $this->logger);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('success', $response->getContent());
    }
    
    public function testServerWithNormalMessage(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(12345);
        
        $xmlContent = '<xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>1234567890</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[Hello]]></Content>
            <MsgId>1234567890</MsgId>
        </xml>';
        
        $request = Request::create('/wechat/official-account/server/12345', 'POST', [], [], [], [], $xmlContent);
        
        $this->accountRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '12345'])
            ->willReturn($account);
            
        // 创建一个完整初始化的事件对象
        $event = new WechatOfficialAccountServerMessageRequestEvent();
        $serverMessage = $this->createMock(ServerMessage::class);
        $event->setMessage($serverMessage);
        $event->setAccount($account);
        $event->setResponse(['Content' => 'Response', 'ToUserName' => 'fromUser', 'FromUserName' => 'toUser']);
            
        $this->callbackHandler->expects($this->once())
            ->method('__invoke')
            ->willReturn($event);
            
        $this->logger->expects($this->once())
            ->method('debug')
            ->with('最终响应微信公众号消息', $this->anything());
            
        $response = $this->controller->server('12345', $request, $this->accountRepository, $this->logger);
        
        $this->assertInstanceOf(Response::class, $response);
        // 使用更灵活的断言，因为XML格式可能有所不同
        $this->assertStringContainsString('<Content><![CDATA[Response]]></Content>', $response->getContent());
        $this->assertStringContainsString('<ToUserName><![CDATA[fromUser]]></ToUserName>', $response->getContent());
        $this->assertStringContainsString('<FromUserName><![CDATA[toUser]]></FromUserName>', $response->getContent());
    }
    
    public function testServerWithNullResponse(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(12345);
        
        $xmlContent = '<xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>1234567890</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[Hello]]></Content>
            <MsgId>1234567890</MsgId>
        </xml>';
        
        $request = Request::create('/wechat/official-account/server/12345', 'POST', [], [], [], [], $xmlContent);
        
        $this->accountRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '12345'])
            ->willReturn($account);
            
        // 创建一个完整初始化的事件对象
        $event = new WechatOfficialAccountServerMessageRequestEvent();
        $serverMessage = $this->createMock(ServerMessage::class);
        $event->setMessage($serverMessage);
        $event->setAccount($account);
        $event->setResponse(null);
            
        $this->callbackHandler->expects($this->once())
            ->method('__invoke')
            ->willReturn($event);
            
        $response = $this->controller->server('12345', $request, $this->accountRepository, $this->logger);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('success', $response->getContent());
    }
    
    public function testServerWithEncryptedMessage(): void
    {
        $this->markTestSkipped('需要实际的加密实现，暂时跳过');
    }
    
    public function testServerWithNullCallbackHandlerResult(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn(12345);
        
        $xmlContent = '<xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>1234567890</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[Hello]]></Content>
            <MsgId>1234567890</MsgId>
        </xml>';
        
        $request = Request::create('/wechat/official-account/server/12345', 'POST', [], [], [], [], $xmlContent);
        
        $this->accountRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '12345'])
            ->willReturn($account);
            
        $this->callbackHandler->expects($this->once())
            ->method('__invoke')
            ->willReturn(null);
            
        $response = $this->controller->server('12345', $request, $this->accountRepository, $this->logger);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('success', $response->getContent());
    }
} 