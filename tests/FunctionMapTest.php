<?php

namespace React\Promise;

use function React\Promise\map;
use function React\Promise\resolve;
use function React\Promise\reject;

class FunctionMapTest extends TestCase
{
    protected function mapper()
    {
        return function ($val) {
            return $val * 2;
        };
    }

    protected function promiseMapper()
    {
        return function ($val) {
            return resolve($val * 2);
        };
    }

    public function testShouldMapInputValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->mapper()
        )->then($mock);
    }

    public function testShouldMapInputPromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [resolve(1), resolve(2), resolve(3)],
            $this->mapper()
        )->then($mock);
    }

    public function testShouldMapMixedInputArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, resolve(2), 3],
            $this->mapper()
        )->then($mock);
    }

    public function testShouldMapInputWhenMapperReturnsAPromise()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->promiseMapper()
        )->then($mock);
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            resolve([1, resolve(2), 3]),
            $this->mapper()
        )->then($mock);
    }

    public function testShouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        map(
            resolve(1),
            $this->mapper()
        )->then($mock);
    }

    public function testShouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        $deferred = new Deferred();

        map(
            [resolve(1), $deferred->promise(), resolve(3)],
            $this->mapper()
        )->then($mock);

        $deferred->resolve(2);
    }

    public function testShouldRejectWhenInputContainsRejection()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        map(
            [resolve(1), reject(2), resolve(3)],
            $this->mapper()
        )->then($this->expectCallableNever(), $mock);
    }

    public function testShouldRejectWhenInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        map(
            reject(),
            $this->mapper()
        )->then($this->expectCallableNever(), $mock);
    }

    public function testShouldCancelInputPromise()
    {
        $mock = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('cancel');

        map(
            $mock,
            $this->mapper()
        )->cancel();
    }

    public function testShouldCancelInputArrayPromises()
    {
        $mock1 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock1
            ->expects($this->once())
            ->method('cancel');

        $mock2 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->once())
            ->method('cancel');

        map(
            [$mock1, $mock2],
            $this->mapper()
        )->cancel();
    }
}
