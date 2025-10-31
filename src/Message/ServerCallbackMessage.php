<?php

namespace WechatOfficialAccountServerMessageBundle\Message;

use Tourze\AsyncContracts\AsyncMessageInterface;

class ServerCallbackMessage implements AsyncMessageInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $message;

    private string $accountId;

    /**
     * @return array<string, mixed>
     */
    public function getMessage(): array
    {
        return $this->message;
    }

    /**
     * @param array<string, mixed> $message
     */
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
