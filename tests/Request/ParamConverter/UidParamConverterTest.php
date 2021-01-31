<?php

declare(strict_types=1);

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\UidParamConverter;
use Symfony\Bridge\PhpUnit\ClassExistsMock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

final class UidParamConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testUidClassDoesNotExist(): void
    {
        ClassExistsMock::register(UidParamConverter::class);
        ClassExistsMock::withMockedClasses([AbstractUid::class => false]);

        $converter = new UidParamConverter();
        $this->assertFalse($converter->supports($this->createMock(ParamConverter::class)));
    }

    public function providesUnsupportedParamConverters(): \Traversable
    {
        $paramConverter = $this->createMock(ParamConverter::class);
        $paramConverter->method('getClass')->willReturn(null);
        yield 'With no class set' => [$paramConverter];

        $paramConverter = $this->createMock(ParamConverter::class);
        $paramConverter->method('getClass')->willReturn(new \stdClass());
        yield 'With random class' => [$paramConverter];
    }

    /**
     * @dataProvider providesUnsupportedParamConverters
     */
    public function testOnlySupportUidClassesButNull(ParamConverter $paramConverter): void
    {
        ClassExistsMock::register(UidParamConverter::class);
        ClassExistsMock::withMockedClasses([AbstractUid::class => $this->createMock(AbstractUid::class)]);

        $converter = new UidParamConverter();
        $this->assertFalse($converter->supports($paramConverter));
    }

    public function providesSupportedParamConverters(): \Traversable
    {
        $paramConverter = $this->createMock(ParamConverter::class);
        $paramConverter->method('getClass')->willReturn(Uuid::class);
        yield 'Uuid' => [$paramConverter];

        $paramConverter = $this->createMock(ParamConverter::class);
        $paramConverter->method('getClass')->willReturn(Ulid::class);
        yield 'Ulid' => [$paramConverter];
    }

    /**
     * @dataProvider providesSupportedParamConverters
     */
    public function testSupportedParameter(ParamConverter $paramConverter): void
    {
        ClassExistsMock::register(UidParamConverter::class);
        ClassExistsMock::withMockedClasses([AbstractUid::class => $this->createMock(AbstractUid::class)]);

        $converter = new UidParamConverter();
        $this->assertTrue($converter->supports($paramConverter));
    }

    public function testConvertWithNoParam(): void
    {
        ClassExistsMock::register(UidParamConverter::class);
        ClassExistsMock::withMockedClasses([AbstractUid::class => $this->createMock(AbstractUid::class)]);

        $paramConverter = $this->createMock(ParamConverter::class);
        $paramConverter->method('getClass')->willReturn(Uuid::class);
        $paramConverter->method('getName')->willReturn('id');

        $this->assertFalse((new UidParamConverter())->apply(new Request(), $paramConverter));
    }

    public function testConvertIntoUuid(): void
    {
        ClassExistsMock::register(UidParamConverter::class);
        ClassExistsMock::withMockedClasses([AbstractUid::class => $this->createMock(AbstractUid::class)]);

        $paramConverter = $this->createMock(ParamConverter::class);
        $paramConverter->method('getClass')->willReturn(Uuid::class);
        $paramConverter->method('getName')->willReturn('id');

        $request = new Request([], [], ['id' => 'daa8146a-b6f0-480f-92da-eb5338167a85']);
        $expectedUid = Uuid::fromString('daa8146a-b6f0-480f-92da-eb5338167a85');

        $applied = (new UidParamConverter())->apply($request, $paramConverter);

        $this->assertTrue($applied);
        $this->assertTrue($expectedUid->equals($request->attributes->get('id')));
        $this->assertInstanceOf(Uuid::class, $request->attributes->get('id'));
    }

    public function testConvertIntoUlid(): void
    {
        ClassExistsMock::register(UidParamConverter::class);
        ClassExistsMock::withMockedClasses([AbstractUid::class => $this->createMock(AbstractUid::class)]);

        $paramConverter = $this->createMock(ParamConverter::class);
        $paramConverter->method('getClass')->willReturn(Ulid::class);
        $paramConverter->method('getName')->willReturn('id');

        $request = new Request([], [], ['id' => '01EXC0G54KD4YDY6JZQMRRKKDT']);
        $expectedUlid = Ulid::fromString('01EXC0G54KD4YDY6JZQMRRKKDT');

        $applied = (new UidParamConverter())->apply($request, $paramConverter);

        $this->assertTrue($applied);
        $this->assertTrue($expectedUlid->equals($request->attributes->get('id')));
        $this->assertInstanceOf(Ulid::class, $request->attributes->get('id'));
    }
}
