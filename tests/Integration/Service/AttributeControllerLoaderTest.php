<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\RouteCollection;
use WechatOfficialAccountServerMessageBundle\Controller\ServerController;
use WechatOfficialAccountServerMessageBundle\Service\AttributeControllerLoader;

/**
 * @covers \WechatOfficialAccountServerMessageBundle\Service\AttributeControllerLoader
 */
class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;
    private AttributeClassLoader $controllerLoader;

    protected function setUp(): void
    {
        $this->controllerLoader = $this->createMock(AttributeClassLoader::class);
        $this->loader = new AttributeControllerLoader($this->controllerLoader);
    }

    public function testAutoload(): void
    {
        $expectedCollection = new RouteCollection();
        
        $this->controllerLoader->expects($this->once())
            ->method('load')
            ->with(ServerController::class)
            ->willReturn($expectedCollection);
        
        $result = $this->loader->autoload();
        
        $this->assertInstanceOf(RouteCollection::class, $result);
    }
}