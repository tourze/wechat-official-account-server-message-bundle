<?php

namespace WechatOfficialAccountServerMessageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '服务端消息')]
class WechatOfficialAccountServerMessageBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \WechatOfficialAccountBundle\WechatOfficialAccountBundle::class => ['all' => true],
        ];
    }
}
