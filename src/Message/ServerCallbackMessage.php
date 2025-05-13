<?php

namespace WechatOfficialAccountServerMessageBundle\Message;

use Tourze\Symfony\Async\Message\AsyncMessageInterface;

class ServerCallbackMessage implements AsyncMessageInterface
{
    private array $message;

    private string $accountId;

    public function getMessage(): array
    {
        return $this->message;
    }

    public function setMessage(array $message): void
    {
        $this->message = $message;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): void
    {
        $this->accountId = $accountId;
    }
}
