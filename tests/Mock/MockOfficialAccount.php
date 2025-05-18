<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Mock;

use Tourze\WechatOfficialAccountContracts\OfficialAccountInterface;

/**
 * OfficialAccountInterface 的模拟实现，用于测试
 */
class MockOfficialAccount implements OfficialAccountInterface
{
    private ?string $appId;

    public function __construct(?string $appId = null)
    {
        $this->appId = $appId;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }
}
