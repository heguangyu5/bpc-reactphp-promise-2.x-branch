<?php

namespace React\Promise;

use function React\Promise\all;
use function React\Promise\resolve;
use function React\Promise\reject;

class FunctionAllTest extends TestCase
{
    public function testShouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        all([])
            ->then($mock);
    }

    public function testShouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all([1, 2, 3])
            ->then($mock);
    }

    public function testShouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all([resolve(1), resolve(2), resolve(3)])
            ->then($mock);
    }

    public function testShouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([null, 1, null, 1, 1]));

        all([null, 1, null, 1, 1])
            ->then($mock);
    }

    public function testShouldRejectIfAnyInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        all([resolve(1), reject(2), resolve(3)])
            ->then($this->expectCallableNever(), $mock);
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all(resolve([1, 2, 3]))
            ->then($mock);
    }

    public function testShouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        all(resolve(1))
            ->then($mock);
    }

    public function testShouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        $deferred = new Deferred();

        all([resolve(1), $deferred->promise(), resolve(3)])
            ->then($mock);

        $deferred->resolve(2);
    }
}
