<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountServerMessageBundle\Repository\ServerMessageRepository;

class ServerMessageRepositoryTest extends TestCase
{
    public function testInheritance(): void
    {
        $this->assertInstanceOf(
            ServiceEntityRepository::class,
            $this->createPartialMock(ServerMessageRepository::class, [])
        );
    }
    
    public function testEntityClassIsCorrect(): void
    {
        $reflection = new \ReflectionClass(ServerMessageRepository::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('registry', $parameters[0]->getName());
        
        // 检查构造函数中是否将ServerMessage::class传递给父类构造函数
        $constructorCode = file_get_contents($reflection->getFileName());
        $constructorPattern = '/parent::__construct\(\$registry,\s*ServerMessage::class\s*\)/';
        $this->assertMatchesRegularExpression($constructorPattern, $constructorCode);
    }
} 