<?php

namespace WechatOfficialAccountServerMessageBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\WechatOfficialAccountOAuth2Bundle\WechatOfficialAccountOAuth2Bundle;
use WechatOfficialAccountBundle\WechatOfficialAccountBundle;

#[AsPermission(title: '服务端消息')]
class WechatOfficialAccountServerMessageBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            WechatOfficialAccountBundle::class => ['all' => true],
            WechatOfficialAccountOAuth2Bundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
        ];
    }
}
