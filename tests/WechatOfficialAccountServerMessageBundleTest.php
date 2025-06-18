<?php

namespace WechatOfficialAccountServerMessageBundle\Tests;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountServerMessageBundle\WechatOfficialAccountServerMessageBundle;

class WechatOfficialAccountServerMessageBundleTest extends TestCase
{
    public function testGetBundleDependencies(): void
    {
        $bundle = new WechatOfficialAccountServerMessageBundle();
        $dependencies = $bundle::getBundleDependencies();
        
        $this->assertNotEmpty($dependencies);
        $this->assertArrayHasKey(\WechatOfficialAccountBundle\WechatOfficialAccountBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[\WechatOfficialAccountBundle\WechatOfficialAccountBundle::class]);
    }
    
    public function testImplementsBundleDependencyInterface(): void
    {
        $bundle = new WechatOfficialAccountServerMessageBundle();
        $this->assertInstanceOf(\Tourze\BundleDependency\BundleDependencyInterface::class, $bundle);
    }
    
    public function testExtendsBundle(): void
    {
        $bundle = new WechatOfficialAccountServerMessageBundle();
        $this->assertInstanceOf(\Symfony\Component\HttpKernel\Bundle\Bundle::class, $bundle);
    }
    
    public function testHasPermissionAttribute(): void
    {
        $reflection = new \ReflectionClass(WechatOfficialAccountServerMessageBundle::class);
        $attributes = $reflection->getAttributes(\Tourze\EasyAdmin\Attribute\Permission\AsPermission::class);
        
        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();
        $this->assertEquals('服务端消息', $attribute->title);
    }
} 