<?php

namespace WechatOfficialAccountServerMessageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Repository\ServerMessageRepository;

#[AsScheduleClean(expression: '26 5 * * *', defaultKeepDay: 7, keepDayEnv: 'WECHAT_OFFICIAL_ACCOUNT_MESSAGE_PERSIST_DAY_NUM')]
#[AsPermission(title: '服务端消息')]
#[ORM\Entity(repositoryClass: ServerMessageRepository::class)]
#[ORM\Table(name: 'wechat_official_account_message', options: ['comment' => '服务端消息'])]
class ServerMessage
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false)]
    private Account $account;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文'])]
    private ?array $context = [];

    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '唯一ID'])]
    private ?string $msgId = null;

    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => 'ToUserName'])]
    private ?string $toUserName = null;

    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => 'FromUserName'])]
    private ?string $fromUserName = null;

    #[Filterable]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 30, options: ['comment' => '消息类型'])]
    private ?string $msgType = null;

    #[ListColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '创建时间戳'])]
    private ?int $createTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getToUserName(): ?string
    {
        return $this->toUserName;
    }

    public function setToUserName(string $toUserName): self
    {
        $this->toUserName = $toUserName;

        return $this;
    }

    public function getFromUserName(): ?string
    {
        return $this->fromUserName;
    }

    public function setFromUserName(string $fromUserName): self
    {
        $this->fromUserName = $fromUserName;

        return $this;
    }

    public function getMsgType(): ?string
    {
        return $this->msgType;
    }

    public function setMsgType(string $msgType): self
    {
        $this->msgType = $msgType;

        return $this;
    }

    public function getMsgId(): ?string
    {
        return $this->msgId;
    }

    public function setMsgId(string $msgId): self
    {
        $this->msgId = $msgId;

        return $this;
    }

    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    public function setCreateTime(int $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    public static function genMsgId(array $message): string
    {
        return strval($message['MsgId'] ?? $message['FromUserName'] . '_' . $message['CreateTime']);
    }

    public static function createFromMessage(array $message): static
    {
        $localMsg = new self();
        $localMsg->setMsgId(static::genMsgId($message));
        $localMsg->setMsgType($message['MsgType']);
        $localMsg->setToUserName($message['ToUserName']);
        $localMsg->setFromUserName($message['FromUserName']);
        $localMsg->setCreateTime($message['CreateTime']);
        $localMsg->setContext($message);

        return $localMsg;
    }
}
