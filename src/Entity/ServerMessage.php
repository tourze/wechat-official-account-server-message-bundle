<?php

namespace WechatOfficialAccountServerMessageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Repository\ServerMessageRepository;

#[AsScheduleClean(expression: '26 5 * * *', defaultKeepDay: 7, keepDayEnv: 'WECHAT_OFFICIAL_ACCOUNT_MESSAGE_PERSIST_DAY_NUM')]
#[ORM\Entity(repositoryClass: ServerMessageRepository::class)]
#[ORM\Table(name: 'wechat_official_account_message', options: ['comment' => '服务端消息'])]
class ServerMessage implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false)]
    private Account $account;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文'])]
    private ?array $context = [];

    #[Assert\NotNull]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '唯一ID'])]
    private ?string $msgId = null;

    #[Assert\NotNull]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => 'ToUserName'])]
    private ?string $toUserName = null;

    #[Assert\NotNull]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => 'FromUserName'])]
    private ?string $fromUserName = null;

    #[Assert\NotNull]
    #[Assert\Length(max: 30)]
    #[ORM\Column(type: Types::STRING, length: 30, options: ['comment' => '消息类型'])]
    private ?string $msgType = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '创建时间戳'])]
    private ?int $createTime = null;

    public function getId(): int
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

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    public function getToUserName(): ?string
    {
        return $this->toUserName;
    }

    public function setToUserName(string $toUserName): void
    {
        $this->toUserName = $toUserName;
    }

    public function getFromUserName(): ?string
    {
        return $this->fromUserName;
    }

    public function setFromUserName(string $fromUserName): void
    {
        $this->fromUserName = $fromUserName;
    }

    public function getMsgType(): ?string
    {
        return $this->msgType;
    }

    public function setMsgType(string $msgType): void
    {
        $this->msgType = $msgType;
    }

    public function getMsgId(): ?string
    {
        return $this->msgId;
    }

    public function setMsgId(string $msgId): void
    {
        $this->msgId = $msgId;
    }

    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    public function setCreateTime(int $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getCreateTimeFormatted(): string
    {
        if (null === $this->createTime) {
            return '-';
        }

        return date('Y-m-d H:i:s', $this->createTime);
    }

    /**
     * @param array<string, mixed> $message
     */
    public static function genMsgId(array $message): string
    {
        $msgId = $message['MsgId'] ?? null;
        if (null !== $msgId && (is_string($msgId) || is_numeric($msgId))) {
            return (string) $msgId;
        }

        $fromUserName = $message['FromUserName'] ?? '';
        $createTime = $message['CreateTime'] ?? '';

        $fromUserNameStr = is_string($fromUserName) || is_numeric($fromUserName) ? (string) $fromUserName : '';
        $createTimeStr = is_string($createTime) || is_numeric($createTime) ? (string) $createTime : '';

        return $fromUserNameStr . '_' . $createTimeStr;
    }

    /**
     * @param array<string, mixed> $message
     */
    public static function createFromMessage(array $message): self
    {
        $localMsg = new self();
        $localMsg->setMsgId(static::genMsgId($message));

        $msgType = $message['MsgType'] ?? '';
        $localMsg->setMsgType(is_string($msgType) || is_numeric($msgType) ? (string) $msgType : '');

        $toUserName = $message['ToUserName'] ?? '';
        $localMsg->setToUserName(is_string($toUserName) || is_numeric($toUserName) ? (string) $toUserName : '');

        $fromUserName = $message['FromUserName'] ?? '';
        $localMsg->setFromUserName(is_string($fromUserName) || is_numeric($fromUserName) ? (string) $fromUserName : '');

        $createTime = $message['CreateTime'] ?? 0;
        $localMsg->setCreateTime(is_numeric($createTime) ? (int) $createTime : 0);

        $localMsg->setContext($message);

        return $localMsg;
    }

    public function __toString(): string
    {
        return sprintf('服务端消息 #%s', $this->id ?? 'new');
    }
}
