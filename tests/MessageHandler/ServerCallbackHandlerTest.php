<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use Tourze\WechatOfficialAccountContracts\UserLoaderInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;
use WechatOfficialAccountServerMessageBundle\Event\WechatOfficialAccountServerMessageRequestEvent;
use WechatOfficialAccountServerMessageBundle\Message\ServerCallbackMessage;
use WechatOfficialAccountServerMessageBundle\MessageHandler\ServerCallbackHandler;

class ServerCallbackHandlerTest extends TestCase
{
    private AccountRepository $accountRepository;
    private LockFactory $lockFactory;
    private LoggerInterface $logger;
    private UserLoaderInterface $userLoader;
    private EventDispatcherInterface $eventDispatcher;
    private UpsertManager $upsertManager;
    private ServerCallbackHandler $handler;
    private LockInterface $lock;
    
    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->lockFactory = $this->createMock(LockFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->upsertManager = $this->createMock(UpsertManager::class);
        $this->lock = $this->createMock(LockInterface::class);
        
        $this->handler = new ServerCallbackHandler(
            $this->lockFactory,
            $this->accountRepository,
            $this->logger,
            $this->userLoader,
            $this->eventDispatcher,
            $this->upsertManager
        );
    }
    
    public function testInvokeSuccess(): void
    {
        $accountId = '12345';
        $message = [
            'MsgType' => 'text',
            'Content' => 'Hello World',
            'MsgId' => '123456',
            'FromUserName' => 'user123',
            'ToUserName' => 'official456',
            'CreateTime' => time(),
        ];
        
        $serverCallbackMessage = new ServerCallbackMessage();
        $serverCallbackMessage->setAccountId($accountId);
        $serverCallbackMessage->setMessage($message);
        
        $account = $this->createMock(Account::class);
        $user = $this->createMock(UserInterface::class);
        
        // 设置模拟对象的行为
        $this->accountRepository->expects($this->once())
            ->method('find')
            ->with($accountId)
            ->willReturn($account);
            
        $lockKey = WechatOfficialAccountServerMessageRequestEvent::class . ServerMessage::genMsgId($message);
        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->with($lockKey)
            ->willReturn($this->lock);
            
        $this->lock->expects($this->once())
            ->method('acquire')
            ->willReturn(true);
            
        $this->lock->expects($this->once())
            ->method('release');
            
        $this->userLoader->expects($this->once())
            ->method('syncUserByOpenId')
            ->with($account, $message['FromUserName'])
            ->willReturn($user);
            
        $this->upsertManager->expects($this->once())
            ->method('upsert')
            ->with($this->isInstanceOf(ServerMessage::class));
            
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(WechatOfficialAccountServerMessageRequestEvent::class))
            ->willReturnCallback(function($event) {
                return $event;
            });
        
        $result = $this->handler->__invoke($serverCallbackMessage);
        
        $this->assertInstanceOf(WechatOfficialAccountServerMessageRequestEvent::class, $result);
    }
    
    public function testInvokeLockNotAcquired(): void
    {
        $accountId = '12345';
        $message = [
            'MsgType' => 'text',
            'Content' => 'Hello World',
            'MsgId' => '123456',
            'FromUserName' => 'user123',
            'ToUserName' => 'official456',
            'CreateTime' => time(),
        ];
        
        $serverCallbackMessage = new ServerCallbackMessage();
        $serverCallbackMessage->setAccountId($accountId);
        $serverCallbackMessage->setMessage($message);
        
        // 设置模拟对象的行为
        $lockKey = WechatOfficialAccountServerMessageRequestEvent::class . ServerMessage::genMsgId($message);
        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->with($lockKey)
            ->willReturn($this->lock);
            
        $this->lock->expects($this->once())
            ->method('acquire')
            ->willReturn(false);
            
        // 当锁未获取成功时，不应执行后续操作
        $this->accountRepository->expects($this->never())
            ->method('find');
        $this->upsertManager->expects($this->never())
            ->method('upsert');
        $this->userLoader->expects($this->never())
            ->method('syncUserByOpenId');
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');
            
        $result = $this->handler->__invoke($serverCallbackMessage);
        
        $this->assertNull($result);
    }
    
    public function testInvokeWithException(): void
    {
        $accountId = '12345';
        $message = [
            'MsgType' => 'text',
            'Content' => 'Hello World',
            'MsgId' => '123456',
            'FromUserName' => 'user123',
            'ToUserName' => 'official456',
            'CreateTime' => time(),
        ];
        
        $serverCallbackMessage = new ServerCallbackMessage();
        $serverCallbackMessage->setAccountId($accountId);
        $serverCallbackMessage->setMessage($message);
        
        $account = $this->createMock(Account::class);
        $exception = new \Exception('Test exception');
        
        // 设置模拟对象的行为
        $this->accountRepository->expects($this->once())
            ->method('find')
            ->with($accountId)
            ->willReturn($account);
            
        $lockKey = WechatOfficialAccountServerMessageRequestEvent::class . ServerMessage::genMsgId($message);
        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->with($lockKey)
            ->willReturn($this->lock);
            
        $this->lock->expects($this->once())
            ->method('acquire')
            ->willReturn(true);
            
        $this->lock->expects($this->once())
            ->method('release');
            
        $this->upsertManager->expects($this->once())
            ->method('upsert')
            ->willThrowException($exception);
            
        $this->logger->expects($this->once())
            ->method('error')
            ->with('微信公众号回调时发生错误', $this->anything());
            
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');
        
        $this->handler->__invoke($serverCallbackMessage);
    }
} 