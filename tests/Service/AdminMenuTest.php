<?php

declare(strict_types=1);

namespace WechatOfficialAccountServerMessageBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;
use WechatOfficialAccountServerMessageBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')
            ->willReturnCallback(function ($entityClass) {
                return match ($entityClass) {
                    ServerMessage::class => '/admin/server-message',
                    default => '/admin/unknown',
                };
            })
        ;

        // 将 Mock 的 LinkGenerator 注入到容器中
        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);

        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testServiceCreation(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testInvokeShouldBeCallable(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testInvokeCreatesMenuStructure(): void
    {
        $rootItem = $this->createMock(ItemInterface::class);
        $wechatMenuItem = $this->createMock(ItemInterface::class);

        $rootItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturnOnConsecutiveCalls(null, $wechatMenuItem)
        ;

        $rootItem->expects($this->once())
            ->method('addChild')
            ->with('微信公众号')
            ->willReturn($wechatMenuItem)
        ;

        $wechatMenuItem->expects($this->once())
            ->method('addChild')
            ->with('服务端消息')
            ->willReturnSelf()
        ;

        $wechatMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/server-message')
            ->willReturnSelf()
        ;

        $wechatMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-comments')
            ->willReturnSelf()
        ;

        ($this->adminMenu)($rootItem);
    }

    public function testInvokeWithExistingMenu(): void
    {
        $rootItem = $this->createMock(ItemInterface::class);
        $wechatMenuItem = $this->createMock(ItemInterface::class);

        $rootItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturn($wechatMenuItem)
        ;

        $rootItem->expects($this->never())
            ->method('addChild')
        ;

        $wechatMenuItem->expects($this->once())
            ->method('addChild')
            ->with('服务端消息')
            ->willReturnSelf()
        ;

        $wechatMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/server-message')
            ->willReturnSelf()
        ;

        $wechatMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-comments')
            ->willReturnSelf()
        ;

        ($this->adminMenu)($rootItem);
    }
}
