<?php

namespace WechatOfficialAccountServerMessageBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatHelper\Encryptor;
use Tourze\XML\XML;
use WechatOfficialAccountBundle\Repository\AccountRepository;

#[Route(path: '/wechat/official-account')]
class ServerController extends AbstractController
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
    #[Route(path: '/server/{id}', methods: ['GET', 'POST'])]
    public function server(
        string $id,
        Request $request,
        AccountRepository $accountRepository,
        LoggerInterface $logger,
    ): Response {
        $account = $accountRepository->findOneBy(['id' => $id]);
        if (!$account) {
            $account = $accountRepository->findOneBy(['appId' => $id]);
        }
        if (!$account) {
            $logger->error('找不到公众号', [
                'appId' => $id,
            ]);

            return new Response('success');
        }

        if ($request->query->has('echostr')) {
            return new Response($request->query->get('echostr'));
        }

        $content = $request->getContent();

        $message = $this->parseMessage($content);
        if (empty($message)) {
            $logger->error('找不到有效的提交请求内容', [
                'content' => $content,
            ]);

            return new Response('success');
        }

        if (isset($message['Encrypt'])) {
            $encryptor = new Encryptor($account->getAppId(), $account->getToken(), $account->getEncodingAesKey());
            $message = $encryptor->decrypt(
                $message['Encrypt'],
                $request->query->get('msg_signature'),
                $request->query->get('nonce'),
                $request->query->get('timestamp')
            );

            $message = $this->parseMessage($message);
        }

        // TODO 只允许微信的IP回调这个接口，查询 CallbackIP 来判断
        $logger->info('收到公众号服务端消息', [
            'account' => $account,
            'message' => $message,
            'content' => $content,
        ]);

        $asyncMessage = new ServerCallbackMessage();
        $asyncMessage->setMessage($message);
        $asyncMessage->setAccountId($account->getId());

        // 有一些事件，我们其实不用回复的
        if (in_array($message['Event'] ?? '', [
            'TEMPLATESENDJOBFINISH',
            'VIEW',
            'view_miniprogram',
            // 'unsubscribe',
        ])) {
            $this->messageBus->dispatch($asyncMessage);

            return new Response('success');
        }

        $event = $this->callbackHandler->__invoke($asyncMessage);
        if (!$event) {
            return new Response('success');
        }

        if ($event->getResponse()) {
            $logger->debug('最终响应微信公众号消息', [
                'message' => $event->getMessage(),
                'response' => $event->getResponse(),
            ]);

            return new Response(XML::build($event->getResponse()));
        }

        return new Response('success');
    }

    /**
     * Parse message array from raw php input.
     *
     * @throws BadRequestException
     */
    private function parseMessage(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        try {
            if (0 === mb_stripos($content, '<')) {
                $content = XML::parse($content);
            } else {
                // Handle JSON format.
                $dataSet = json_decode($content, true);
                if ($dataSet && (JSON_ERROR_NONE === json_last_error())) {
                    $content = $dataSet;
                }
            }

            return (array) $content;
        } catch (\Exception $exception) {
            $this->logger->error('解密Payload失败', [
                'exception' => $exception,
                'content' => $content,
            ]);
            throw new BadRequestException(sprintf('Invalid message content:(%s) %s', $exception->getCode(), $exception->getMessage()), $exception->getCode(), previous: $exception);
        }
    }
}
