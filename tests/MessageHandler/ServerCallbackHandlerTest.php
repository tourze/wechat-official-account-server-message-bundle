<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\MessageHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountServerMessageBundle\Message\ServerCallbackMessage;
use WechatOfficialAccountServerMessageBundle\MessageHandler\ServerCallbackHandler;

/**
 * @internal
 */
#[CoversClass(ServerCallbackHandler::class)]
#[RunTestsInSeparateProcesses]
final class ServerCallbackHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testHandlerExists(): void
    {
        $handler = self::getService(ServerCallbackHandler::class);
        $this->assertInstanceOf(ServerCallbackHandler::class, $handler);
    }

    public function testMessageClassInstantiation(): void
    {
        $message = new ServerCallbackMessage();
        $this->assertInstanceOf(ServerCallbackMessage::class, $message);

        $message->setAccountId('test-account');
        $message->setMessage(['test' => 'data']);

        $this->assertSame('test-account', $message->getAccountId());
        $this->assertSame(['test' => 'data'], $message->getMessage());
    }
}
