<?php

declare(strict_types=1);

namespace WechatOfficialAccountServerMessageBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatOfficialAccountServerMessageBundle\WechatOfficialAccountServerMessageBundle;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountServerMessageBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatOfficialAccountServerMessageBundleTest extends AbstractBundleTestCase
{
}
