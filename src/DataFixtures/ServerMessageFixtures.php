<?php

namespace WechatOfficialAccountServerMessageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatOfficialAccountBundle\DataFixtures\AccountFixtures;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

#[When(env: 'test')]
#[When(env: 'dev')]
class ServerMessageFixtures extends Fixture implements DependentFixtureInterface
{
    public const SERVER_MESSAGE_REFERENCE_PREFIX = 'server_message_';
    public const SERVER_MESSAGE_COUNT = 20;

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('zh_CN');
    }

    public function load(ObjectManager $manager): void
    {
        $account = $this->getReference(AccountFixtures::ACCOUNT_REFERENCE, Account::class);

        for ($i = 0; $i < self::SERVER_MESSAGE_COUNT; ++$i) {
            $serverMessage = $this->createServerMessage($account);
            $manager->persist($serverMessage);
            $this->addReference(self::SERVER_MESSAGE_REFERENCE_PREFIX . $i, $serverMessage);
        }

        $manager->flush();
    }

    private function createServerMessage(Account $account): ServerMessage
    {
        $messageTypes = ['text', 'image', 'voice', 'video', 'music', 'news', 'link'];
        $selectedType = $this->faker->randomElement($messageTypes);
        $msgType = is_string($selectedType) ? $selectedType : 'text';

        $fromUserName = 'o' . $this->faker->regexify('[a-zA-Z0-9]{27}');
        $toUserName = 'gh_' . $this->faker->regexify('[a-f0-9]{12}');
        $createTime = $this->faker->unixTime();

        $message = new ServerMessage();
        $message->setAccount($account);
        $message->setMsgType($msgType);
        $message->setFromUserName($fromUserName);
        $message->setToUserName($toUserName);
        $message->setCreateTime($createTime);
        $message->setMsgId($this->generateMsgId($fromUserName, $createTime));
        $message->setContext($this->generateContext($msgType, $fromUserName, $toUserName, $createTime));

        return $message;
    }

    private function generateMsgId(string $fromUserName, int $createTime): string
    {
        return $fromUserName . '_' . $createTime;
    }

    /**
     * @return array<string, mixed>
     */
    private function generateContext(string $msgType, string $fromUserName, string $toUserName, int $createTime): array
    {
        $baseContext = [
            'ToUserName' => $toUserName,
            'FromUserName' => $fromUserName,
            'CreateTime' => $createTime,
            'MsgType' => $msgType,
        ];

        switch ($msgType) {
            case 'text':
                $baseContext['Content'] = $this->faker->sentence();
                $baseContext['MsgId'] = $this->faker->numberBetween(1000000000, 9999999999);
                break;
            case 'image':
                $baseContext['PicUrl'] = $this->faker->imageUrl();
                $baseContext['MediaId'] = $this->faker->regexify('[a-zA-Z0-9]{64}');
                $baseContext['MsgId'] = $this->faker->numberBetween(1000000000, 9999999999);
                break;
            case 'voice':
                $baseContext['MediaId'] = $this->faker->regexify('[a-zA-Z0-9]{64}');
                $baseContext['Format'] = 'amr';
                $baseContext['MsgId'] = $this->faker->numberBetween(1000000000, 9999999999);
                break;
            case 'video':
                $baseContext['MediaId'] = $this->faker->regexify('[a-zA-Z0-9]{64}');
                $baseContext['ThumbMediaId'] = $this->faker->regexify('[a-zA-Z0-9]{64}');
                $baseContext['MsgId'] = $this->faker->numberBetween(1000000000, 9999999999);
                break;
            default:
                $baseContext['MsgId'] = $this->faker->numberBetween(1000000000, 9999999999);
                break;
        }

        return $baseContext;
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
