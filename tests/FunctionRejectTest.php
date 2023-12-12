<?php

namespace React\Promise;

use function React\Promise\reject;

class FunctionRejectTest extends TestCase
{
    public function testShouldRejectAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        reject($expected)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    public function testShouldRejectAFulfilledPromise()
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    public function testShouldRejectARejectedPromise()
    {
        $expected = 123;

        $resolved = new RejectedPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }
}
