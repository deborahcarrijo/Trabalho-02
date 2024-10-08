<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\Php\Factory;

use InvalidArgumentException;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Php\Class_ as ClassElement;
use phpDocumentor\Reflection\Php\File;
use phpDocumentor\Reflection\Php\ProjectFactoryStrategy;
use phpDocumentor\Reflection\Php\StrategyContainer;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\Namespace_ as NamespaceNode;
use PHPUnit\Framework\Attributes\CoversClass;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

use function current;

#[CoversClass(Namespace_::class)]
final class Namespace_Test extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->fixture = new Namespace_();
    }

    public function testMatches(): void
    {
        $this->assertFalse($this->fixture->matches(self::createContext(null), new stdClass()));
        $this->assertTrue($this->fixture->matches(
            self::createContext(null),
            $this->prophesize(NamespaceNode::class)->reveal(),
        ));
    }

    public function testCreateThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fixture->create(
            self::createContext(null),
            new stdClass(),
            $this->prophesize(StrategyContainer::class)->reveal(),
        );
    }

    public function testIteratesStatements(): void
    {
        $class           = new ClassNode('\MyClass');
        $classElement = new ClassElement(new Fqsen('\MyClass'));
        $strategyMock      = $this->prophesize(ProjectFactoryStrategy::class);
        $containerMock     = $this->prophesize(StrategyContainer::class);
        $namespace         = new NamespaceNode(new Name('MyNamespace'));
        $namespace->setAttribute('fqsen', new Fqsen('\MyNamespace'));
        $namespace->stmts = [$class];

        $strategyMock->create(Argument::type(ContextStack::class), $class, $containerMock)
            ->will(function ($args) use ($classElement): void {
                $args[0]->peek()->addClass($classElement);
            })
            ->shouldBeCalled();

        $containerMock->findMatching(
            Argument::type(ContextStack::class),
            $class,
        )->willReturn($strategyMock->reveal());

        $file = new File('hash', 'path');
        $this->fixture->create(self::createContext(null)->push($file), $namespace, $containerMock->reveal());
        $class = current($file->getClasses());
        $fqsen = current($file->getNamespaces());

        $this->assertInstanceOf(ClassElement::class, $class);
        $this->assertEquals('\MyClass', (string) $class->getFqsen());
        $this->assertEquals(new Fqsen('\MyNamespace'), $fqsen);
    }
}
