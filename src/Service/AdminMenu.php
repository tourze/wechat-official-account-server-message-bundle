<?php

declare(strict_types=1);

namespace WechatOfficialAccountServerMessageBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('微信公众号')) {
            $item->addChild('微信公众号');
        }

        $wechatMenu = $item->getChild('微信公众号');
        if (null === $wechatMenu) {
            return;
        }

        // 服务端消息管理
        $wechatMenu->addChild('服务端消息')
            ->setUri($this->linkGenerator->getCurdListPage(ServerMessage::class))
            ->setAttribute('icon', 'fas fa-comments')
        ;
    }
}
