<?php

namespace WechatOfficialAccountServerMessageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use WechatOfficialAccountServerMessageBundle\Controller\ServerController;
use WechatOfficialAccountServerMessageBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
final class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;

    private AttributeClassLoader $controllerLoader;

    private function initializeLoader(): void
    {
        $expectedCollection = new RouteCollection();

        $this->controllerLoader = new class($expectedCollection) extends AttributeClassLoader {
            private RouteCollection $expectedCollection;

            public function __construct(RouteCollection $expectedCollection)
            {
                parent::__construct();
                $this->expectedCollection = $expectedCollection;
            }

            public function load(mixed $class, ?string $type = null): RouteCollection
            {
                return $this->expectedCollection;
            }

            /**
             * @phpstan-ignore missingType.generics
             */
            protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot): void
            {
                // No-op for testing
            }
        };

        $this->loader = new AttributeControllerLoader($this->controllerLoader);
    }

    public function testAutoload(): void
    {
        $this->initializeLoader();

        $result = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);
    }
}
