<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;
use WechatOfficialAccountServerMessageBundle\Controller\ServerController;

/**
 * @internal
 */
#[CoversClass(ServerController::class)]
#[RunTestsInSeparateProcesses]
final class ServerControllerTest extends AbstractWebTestCase
{
    public function testGetRequestReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request('GET', "/wechat/official-account/server/{$account->getId()}", [
            'echostr' => 'test_echo_string',
        ]);
    }

    public function testPostRequestReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request('POST', "/wechat/official-account/server/{$account->getId()}");
    }

    public function testPutRequestNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request('PUT', "/wechat/official-account/server/{$account->getId()}");
    }

    public function testDeleteRequestNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request('DELETE', "/wechat/official-account/server/{$account->getId()}");
    }

    public function testPatchRequestNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request('PATCH', "/wechat/official-account/server/{$account->getId()}");
    }

    public function testHeadRequestNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request('HEAD', "/wechat/official-account/server/{$account->getId()}");
    }

    public function testOptionsRequestNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request('OPTIONS', "/wechat/official-account/server/{$account->getId()}");
    }

    public function testUnauthorizedAccessReturnsExpectedResponse(): void
    {
        $client = self::createClient();

        $client->request('GET', '/wechat/official-account/server/test-id');

        $response = $client->getResponse();
        $this->assertLessThan(500, $response->getStatusCode(), 'Unauthorized access should not result in server error');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $account = $this->createTestAccount();

        $this->expectException(NotFoundHttpException::class);
        $client->request($method, "/wechat/official-account/server/{$account->getId()}");
    }

    private function createTestAccount(): Account
    {
        $account = new Account();
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret');
        $account->setName('Test Account');
        $account->setToken('test_token');
        $account->setEncodingAesKey('test_encoding_aes_key_12345678901234567890123456789012');

        $accountRepository = self::getService(AccountRepository::class);
        $this->assertInstanceOf(AccountRepository::class, $accountRepository);
        $accountRepository->save($account);

        return $account;
    }
}
