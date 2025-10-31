<?php

namespace WechatOfficialAccountServerMessageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

/**
 * @extends ServiceEntityRepository<ServerMessage>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: ServerMessage::class)]
class ServerMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServerMessage::class);
    }

    public function save(ServerMessage $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServerMessage $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
