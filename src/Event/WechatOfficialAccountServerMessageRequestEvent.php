<?php

namespace WechatOfficialAccountServerMessageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

class WechatOfficialAccountServerMessageRequestEvent extends Event
{
    /**
     * @var array<string, mixed>|null 拦截响应的内容
     */
    protected ?array $response = null;

    /**
     * @var ServerMessage 发送的消息
     */
    private ServerMessage $message;

    /**
     * @var Account 发送账号
     */
    private Account $account;

    /**
     * @var UserInterface|null 当前用户
     */
    private ?UserInterface $user;

    public function getMessage(): ServerMessage
    {
        return $this->message;
    }

    public function setMessage(ServerMessage $message): void
    {
        $this->message = $message;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }

    /**
     * @param array<string, mixed>|null $response
     */
    public function setResponse(?array $response): void
    {
        $this->response = $response;
    }
}
