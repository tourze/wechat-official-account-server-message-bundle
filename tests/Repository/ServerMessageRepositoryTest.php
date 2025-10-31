<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;
use WechatOfficialAccountServerMessageBundle\Repository\ServerMessageRepository;

/**
 * @internal
 */
#[CoversClass(ServerMessageRepository::class)]
#[RunTestsInSeparateProcesses]
final class ServerMessageRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testInheritance(): void
    {
        $repository = self::getService(ServerMessageRepository::class);
        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }

    public function testRepositoryCanPersistAndRetrieveEntity(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        $account = $this->createAccount();

        $serverMessage = new ServerMessage();
        $serverMessage->setAccount($account);
        $serverMessage->setMsgId('test_msg_' . uniqid());
        $serverMessage->setToUserName('toUser');
        $serverMessage->setFromUserName('fromUser');
        $serverMessage->setCreateTime(time());
        $serverMessage->setMsgType('text');
        $serverMessage->setContext(['content' => 'test message']);

        $repository->save($serverMessage);

        $found = $repository->find($serverMessage->getId());
        $this->assertInstanceOf(ServerMessage::class, $found);
        $this->assertEquals($serverMessage->getMsgId(), $found->getMsgId());
    }

    // 基础 find 测试

    // findAll 测试

    // findBy 测试

    // findOneBy 测试

    // save/remove 测试
    public function testSave(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        $account = $this->createAccount();

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMsgId('save_test');
        $msg->setToUserName('toUser');
        $msg->setFromUserName('fromUser');
        $msg->setCreateTime(time());
        $msg->setMsgType('text');

        $repository->save($msg);

        $found = $repository->findOneBy(['msgId' => 'save_test']);
        $this->assertInstanceOf(ServerMessage::class, $found);
        $this->assertEquals('save_test', $found->getMsgId());
    }

    public function testRemove(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        $account = $this->createAccount();

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMsgId('remove_test');
        $msg->setToUserName('toUser');
        $msg->setFromUserName('fromUser');
        $msg->setCreateTime(time());
        $msg->setMsgType('text');
        $this->persistAndFlush($msg);

        $repository->remove($msg);

        $found = $repository->findOneBy(['msgId' => 'remove_test']);
        $this->assertNull($found);
    }

    // IS NULL 查询测试
    public function testFindByNullableFieldsIsNull(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        // 先清空数据库
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . ServerMessage::class)->execute();
        $em->flush();

        $account = $this->createAccount();

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMsgId('null_context_test');
        $msg->setToUserName('toUser');
        $msg->setFromUserName('fromUser');
        $msg->setCreateTime(time());
        $msg->setMsgType('text');
        $msg->setContext(null);
        $this->persistAndFlush($msg);

        $result = $repository->findBy(['context' => null]);
        $this->assertCount(1, $result);
        /** @var ServerMessage $entity */
        $entity = $result[0];
        $this->assertEquals('null_context_test', $entity->getMsgId());
    }

    public function testCountWithNullableFieldsIsNull(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        // 先清空数据库
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . ServerMessage::class)->execute();
        $em->flush();

        $account = $this->createAccount();

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMsgId('count_null_test');
        $msg->setToUserName('toUser');
        $msg->setFromUserName('fromUser');
        $msg->setCreateTime(time());
        $msg->setMsgType('text');
        $msg->setContext(null);
        $this->persistAndFlush($msg);

        $count = $repository->count(['context' => null]);
        $this->assertEquals(1, $count);
    }

    // 关联查询测试
    public function testFindByAccountAssociation(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        // 先清空数据库
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . ServerMessage::class)->execute();
        $em->flush();

        $account = $this->createAccount();

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMsgId('account_assoc_test');
        $msg->setToUserName('toUser');
        $msg->setFromUserName('fromUser');
        $msg->setCreateTime(time());
        $msg->setMsgType('text');
        $this->persistAndFlush($msg);

        $result = $repository->findBy(['account' => $account]);
        $this->assertCount(1, $result);
        /** @var ServerMessage $entity */
        $entity = $result[0];
        $this->assertEquals('account_assoc_test', $entity->getMsgId());
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        // 先清空数据库
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . ServerMessage::class)->execute();
        $em->flush();

        $account = $this->createAccount();

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMsgId('count_account_test');
        $msg->setToUserName('toUser');
        $msg->setFromUserName('fromUser');
        $msg->setCreateTime(time());
        $msg->setMsgType('text');
        $this->persistAndFlush($msg);

        $count = $repository->count(['account' => $account]);
        $this->assertEquals(1, $count);
    }

    // 额外的测试用例来满足 PHPStan 要求
    public function testFindOneByWithOrderByShouldReturnCorrectEntity(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        // 先清空数据库
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . ServerMessage::class)->execute();
        $em->flush();

        $account = $this->createAccount();

        // 创建两个相同类型的消息
        $msg1 = new ServerMessage();
        $msg1->setAccount($account);
        $msg1->setMsgId('order_msg1');
        $msg1->setToUserName('toUser');
        $msg1->setFromUserName('fromUser');
        $msg1->setCreateTime(1000);
        $msg1->setMsgType('text');
        $this->persistAndFlush($msg1);

        $msg2 = new ServerMessage();
        $msg2->setAccount($account);
        $msg2->setMsgId('order_msg2');
        $msg2->setToUserName('toUser');
        $msg2->setFromUserName('fromUser');
        $msg2->setCreateTime(2000);
        $msg2->setMsgType('text');
        $this->persistAndFlush($msg2);

        $result = $repository->findOneBy(['msgType' => 'text'], ['createTime' => 'DESC']);
        $this->assertInstanceOf(ServerMessage::class, $result);
        $this->assertEquals('order_msg2', $result->getMsgId());
    }

    // 缺少的测试方法以满足 PHPStan 要求
    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $repository = self::getService(ServerMessageRepository::class);

        // 先清空数据库
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . ServerMessage::class)->execute();
        $em->flush();

        $account = $this->createAccount();

        $msg = new ServerMessage();
        $msg->setAccount($account);
        $msg->setMsgId('findonebyassoc_test');
        $msg->setToUserName('toUser');
        $msg->setFromUserName('fromUser');
        $msg->setCreateTime(time());
        $msg->setMsgType('text');
        $this->persistAndFlush($msg);

        $result = $repository->findOneBy(['account' => $account]);
        $this->assertInstanceOf(ServerMessage::class, $result);
        $this->assertEquals('findonebyassoc_test', $result->getMsgId());
    }

    private function createAccount(): Account
    {
        $account = new Account();
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret');
        $account->setName('Test Account');
        $this->persistAndFlush($account);

        return $account;
    }

    protected function createNewEntity(): object
    {
        $account = $this->createAccount();

        $entity = new ServerMessage();
        $entity->setAccount($account);
        $entity->setMsgId('test_msg_' . uniqid());
        $entity->setToUserName('toUser_' . uniqid());
        $entity->setFromUserName('fromUser_' . uniqid());
        $entity->setCreateTime(time());
        $entity->setMsgType('text');
        $entity->setContext(['content' => 'test message']);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<ServerMessage>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(ServerMessageRepository::class);
    }
}
