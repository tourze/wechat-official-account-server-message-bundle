<?php

namespace WechatOfficialAccountServerMessageBundle\Controller;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatHelper\Encryptor;
use Tourze\XML\XML;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountServerMessageBundle\Message\ServerCallbackMessage;
use WechatOfficialAccountServerMessageBundle\MessageHandler\ServerCallbackHandler;

#[WithMonologChannel(channel: 'wechat_official_account_server_message')]
final class ServerController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ServerCallbackHandler $callbackHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 服务端消息回调
     *
     * @see https://developers.weixin.qq.com/community/develop/doc/000cc80466c478b44d4d6628056800
     * @see https://developers.weixin.qq.com/doc/offiaccount/User_Management/Get_users_basic_information_UnionID.html#UinonId
     */
    #[Route(path: '/wechat/official-account/server/{id}', methods: ['GET', 'POST'])]
    public function __invoke(
        string $id,
        Request $request,
        AccountRepository $accountRepository,
        LoggerInterface $logger,
    ): Response {
        $account = $this->findAccount($id, $accountRepository, $logger);
        if (null === $account) {
            return new Response('success');
        }

        if ($request->query->has('echostr')) {
            return new Response((string) $request->query->get('echostr'));
        }

        $message = $this->processMessageContent($request, $account, $logger);
        if ([] === $message) {
            return new Response('success');
        }

        return $this->handleMessage($message, $account, $logger);
    }

    private function findAccount(string $id, AccountRepository $accountRepository, LoggerInterface $logger): ?Account
    {
        $account = $accountRepository->findOneBy(['id' => $id]);
        if (null === $account) {
            $account = $accountRepository->findOneBy(['appId' => $id]);
        }
        if (null === $account) {
            $logger->error('找不到公众号', [
                'appId' => $id,
            ]);
        }

        return $account;
    }

    /**
     * @return array<string, mixed>
     */
    private function processMessageContent(Request $request, Account $account, LoggerInterface $logger): array
    {
        $content = $request->getContent();
        $message = $this->parseMessage($content);

        if ([] === $message) {
            $logger->error('找不到有效的提交请求内容', [
                'content' => $content,
            ]);

            return [];
        }

        if (isset($message['Encrypt'])) {
            return $this->decryptMessage($message, $request, $account, $logger);
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $message
     * @return array<string, mixed>
     */
    private function decryptMessage(array $message, Request $request, Account $account, LoggerInterface $logger): array
    {
        $appId = $account->getAppId();
        if (null === $appId) {
            $logger->error('公众号AppId为空', [
                'account' => $account,
            ]);

            return [];
        }

        $encryptValue = $message['Encrypt'] ?? '';
        if (!is_string($encryptValue)) {
            $logger->error('加密消息格式错误', [
                'encrypt' => $encryptValue,
            ]);

            return [];
        }

        $encryptor = new Encryptor($appId, $account->getToken(), $account->getEncodingAesKey());
        $decryptedContent = $encryptor->decrypt(
            $encryptValue,
            (string) $request->query->get('msg_signature'),
            (string) $request->query->get('nonce'),
            (string) $request->query->get('timestamp')
        );

        return $this->parseMessage($decryptedContent);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function handleMessage(array $message, Account $account, LoggerInterface $logger): Response
    {
        // TODO 只允许微信的IP回调这个接口，查询 CallbackIP 来判断
        $logger->info('收到公众号服务端消息', [
            'account' => $account,
            'message' => $message,
        ]);

        $asyncMessage = new ServerCallbackMessage();
        $asyncMessage->setMessage($message);
        $asyncMessage->setAccountId((string) $account->getId());

        // 有一些事件，我们其实不用回复的
        if (in_array($message['Event'] ?? '', [
            'TEMPLATESENDJOBFINISH',
            'VIEW',
            'view_miniprogram',
            // 'unsubscribe',
        ], true)) {
            $this->messageBus->dispatch($asyncMessage);

            return new Response('success');
        }

        $event = $this->callbackHandler->__invoke($asyncMessage);
        if (null === $event) {
            return new Response('success');
        }

        if (null !== $event->getResponse()) {
            $logger->debug('最终响应微信公众号消息', [
                'message' => $event->getMessage(),
                'response' => $event->getResponse(),
            ]);

            return new Response(XML::build($event->getResponse()));
        }

        return new Response('success');
    }

    /**
     * 从原始 PHP 输入解析消息数组。
     *
     * @throws BadRequestException
     */
    /**
     * @return array<string, mixed>
     */
    private function parseMessage(string $content): array
    {
        if ('' === $content) {
            return [];
        }

        try {
            if (0 === stripos($content, '<')) {
                $content = XML::parse($content);
            } else {
                // Handle JSON format.
                $dataSet = json_decode($content, true);
                if (null !== $dataSet && (JSON_ERROR_NONE === json_last_error())) {
                    $content = $dataSet;
                }
            }

            if (is_array($content)) {
                // 确保键都是字符串类型
                $result = [];
                foreach ($content as $key => $value) {
                    $result[(string) $key] = $value;
                }

                return $result;
            }

            return [];
        } catch (\Exception $exception) {
            $this->logger->error('解密Payload失败', [
                'exception' => $exception,
                'content' => $content,
            ]);
            throw new BadRequestException(sprintf('Invalid message content:(%s) %s', $exception->getCode(), $exception->getMessage()), $exception->getCode(), previous: $exception);
        }
    }
}
