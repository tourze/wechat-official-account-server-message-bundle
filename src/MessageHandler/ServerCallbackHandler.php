<?php

namespace WechatOfficialAccountServerMessageBundle\MessageHandler;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;
use Tourze\WechatOfficialAccountContracts\UserLoaderInterface;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;
use WechatOfficialAccountServerMessageBundle\Event\WechatOfficialAccountServerMessageRequestEvent;
use WechatOfficialAccountServerMessageBundle\Message\ServerCallbackMessage;

#[AsMessageHandler]
#[WithMonologChannel(channel: 'wechat_official_account_server_message')]
class ServerCallbackHandler
{
    public function __construct(
        private readonly LockFactory $lockFactory,
        private readonly AccountRepository $accountRepository,
        private readonly LoggerInterface $logger,
        private readonly UserLoaderInterface $userLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UpsertManager $upsertManager,
    ) {
    }

    public function __invoke(ServerCallbackMessage $asyncMessage): ?WechatOfficialAccountServerMessageRequestEvent
    {
        $message = $asyncMessage->getMessage();

        // 重复消息的处理
        $lockKey = WechatOfficialAccountServerMessageRequestEvent::class . ServerMessage::genMsgId($message);
        $lock = $this->lockFactory->createLock($lockKey);
        if (!$lock->acquire()) {
            return null;
        }

        $account = $this->accountRepository->find($asyncMessage->getAccountId());
        if (null === $account) {
            $this->logger->error('找不到对应的公众号账号', [
                'accountId' => $asyncMessage->getAccountId(),
            ]);

            return null;
        }

        try {
            // 不管事件内怎么处理，我们先自己保证存一份消息
            $localMsg = ServerMessage::createFromMessage($message);
            $localMsg->setAccount($account);
            $this->upsertManager->upsert($localMsg);

            // 因为在这里我们也能拿到OpenID了，所以同时也要存库一次
            $fromUserName = $message['FromUserName'] ?? '';
            $openId = is_string($fromUserName) || is_numeric($fromUserName) ? (string) $fromUserName : '';
            $localUser = $this->userLoader->syncUserByOpenId($account, $openId);

            // 分发事件
            $event = new WechatOfficialAccountServerMessageRequestEvent();
            $event->setMessage($localMsg);
            $event->setAccount($account);
            $event->setUser($localUser);
            $this->eventDispatcher->dispatch($event);

            return $event;
        } catch (\Throwable $exception) {
            $this->logger->error('微信公众号回调时发生错误', [
                'exception' => $exception,
            ]);
            throw $exception;
        } finally {
            $lock->release();
        }
    }
}
