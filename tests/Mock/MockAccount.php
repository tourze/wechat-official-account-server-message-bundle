<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Mock;

use Tourze\WechatOfficialAccountContracts\OfficialAccountInterface;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * 同时模拟Account和OfficialAccountInterface的测试类
 */
class MockAccount extends Account implements OfficialAccountInterface
{
    private ?string $appId = null;
    
    public function __construct(?string $appId = 'test-app-id')
    {
        $this->appId = $appId;
    }
    
    public function getAppId(): ?string
    {
        return $this->appId;
    }
    
    public function getId(): ?int
    {
        return 1;
    }
    
    public function setLocalMessage(array $data): void
    {
        // 空实现，仅用于测试
    }
}
