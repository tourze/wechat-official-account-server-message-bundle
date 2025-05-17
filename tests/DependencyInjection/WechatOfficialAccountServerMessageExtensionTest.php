<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatOfficialAccountServerMessageBundle\DependencyInjection\WechatOfficialAccountServerMessageExtension;

class WechatOfficialAccountServerMessageExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $extension = new WechatOfficialAccountServerMessageExtension();
        $container = new ContainerBuilder();
        
        $extension->load([], $container);
        
        // 验证服务是否已加载
        $this->assertTrue($container->hasDefinition('WechatOfficialAccountServerMessageBundle\Repository\ServerMessageRepository') || 
                         $container->hasAlias('WechatOfficialAccountServerMessageBundle\Repository\ServerMessageRepository'));
    }
    
    public function testExtendsExtensionClass(): void
    {
        $extension = new WechatOfficialAccountServerMessageExtension();
        $this->assertInstanceOf(\Symfony\Component\DependencyInjection\Extension\Extension::class, $extension);
    }
} 