<?php

namespace WechatOfficialAccountServerMessageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

class WechatOfficialAccountServerMessageRequestEvent extends Event
{
    /**
     * @var array|null 拦截响应的内容
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
     * @var User|null 当前用户
     */
    private ?User $user;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function setResponse(?array $response): void
    {
        $this->response = $response;
    }
}
