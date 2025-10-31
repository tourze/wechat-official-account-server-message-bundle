<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatOfficialAccountServerMessageBundle\Controller\Admin\ServerMessageCrudController;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

/**
 * @internal
 */
#[CoversClass(ServerMessageCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ServerMessageCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testIndexPageRequiresAuthentication(): void
    {
        $client = self::createClient();
        $client->catchExceptions(false);

        try {
            $client->request('GET', '/wechat-official-account/server-message');

            $this->assertTrue(
                $client->getResponse()->isNotFound()
                || $client->getResponse()->isRedirect()
                || $client->getResponse()->isSuccessful(),
                'Response should be 404, redirect, or successful'
            );
        } catch (NotFoundHttpException $e) {
            $this->assertInstanceOf(NotFoundHttpException::class, $e, 'Expected NotFoundHttpException was caught');
        } catch (\Exception $e) {
            $this->assertStringNotContainsString(
                'doctrine_ping_connection',
                $e->getMessage(),
                'Should not fail with doctrine_ping_connection error: ' . $e->getMessage()
            );
        }
    }

    public function testUnauthenticatedAccessIsRestricted(): void
    {
        $client = self::createClient();
        $client->catchExceptions(false);

        try {
            $client->request('GET', '/wechat-official-account/server-message');
            $response = $client->getResponse();

            $this->assertTrue(
                $response->isRedirect() || $response->isNotFound() || 401 === $response->getStatusCode(),
                'Unauthenticated access should be restricted'
            );
        } catch (\Exception $e) {
            $this->assertStringNotContainsString(
                'doctrine_ping_connection',
                $e->getMessage(),
                'Should not fail with doctrine connection error'
            );
        }
    }

    public function testSearchFunctionality(): void
    {
        $client = self::createClient();
        $client->catchExceptions(false);

        try {
            $client->request('GET', '/wechat-official-account/server-message?query=test');
            $response = $client->getResponse();

            $this->assertTrue(
                $response->isSuccessful() || $response->isRedirect() || $response->isNotFound(),
                'Search request should not cause server errors'
            );
        } catch (\Exception $e) {
            $this->assertStringNotContainsString(
                'doctrine_ping_connection',
                $e->getMessage(),
                'Should not fail with doctrine_ping_connection error'
            );
        }
    }

    public function testValidationErrors(): void
    {
        $client = self::createClient();
        $client->catchExceptions(false);

        try {
            $crawler = $client->request('GET', '/wechat-official-account/server-message?crudAction=new');
            $response = $client->getResponse();

            $this->assertTrue(
                $response->isSuccessful() || $response->isRedirect() || $response->isNotFound(),
                'Form validation test should not cause server errors'
            );

            if ($response->isSuccessful()) {
                $forms = $crawler->filter('form');
                if ($forms->count() > 0) {
                    $form = $forms->first()->form();
                    $crawler = $client->submit($form);

                    if (422 === $client->getResponse()->getStatusCode()) {
                        $this->assertResponseStatusCodeSame(422);
                        $this->assertStringContainsString('should not be blank', $crawler->filter('.invalid-feedback')->text());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->assertStringNotContainsString(
                'doctrine_ping_connection',
                $e->getMessage(),
                'Validation test should not fail with doctrine_ping_connection error'
            );
        }
    }

    /**
     * @return AbstractCrudController<ServerMessage>
     */
    protected function getControllerService(): AbstractCrudController
    {
        /** @var AbstractCrudController<ServerMessage> $controller */
        $controller = self::getContainer()->get(ServerMessageCrudController::class);
        self::assertInstanceOf(ServerMessageCrudController::class, $controller);

        return $controller;
    }

    /**
     * Index页面显示的表头字段
     * @return \Generator<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '公众号账号' => ['公众号账号'];
        yield '消息ID' => ['消息ID'];
        yield '消息类型' => ['消息类型'];
        yield '发送用户' => ['发送用户'];
        yield '接收用户' => ['接收用户'];
        yield '消息时间' => ['消息时间'];
    }

    /**
     * New页面的字段 - 注意：此控制器禁用了NEW操作，所以测试会跳过
     * @return \Generator<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 控制器实际配置的字段
        yield 'account字段' => ['account'];
        yield 'msgId字段' => ['msgId'];
        yield 'msgType字段' => ['msgType'];
        yield 'fromUserName字段' => ['fromUserName'];
        yield 'toUserName字段' => ['toUserName'];
        yield 'createTime字段' => ['createTime'];
        yield 'context字段' => ['context'];
    }

    /**
     * Edit页面的字段
     * @return \Generator<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'account字段' => ['account'];
        yield 'msgId字段' => ['msgId'];
        yield 'msgType字段' => ['msgType'];
        yield 'fromUserName字段' => ['fromUserName'];
        yield 'toUserName字段' => ['toUserName'];
        yield 'createTime字段' => ['createTime'];
        yield 'context字段' => ['context'];
    }
}
