<?php

namespace React\Promise;

class FunctionRaceTest extends TestCase
{
    public function testShouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            []
        )->then($mock);
    }

    public function testShouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        race(
            [1, 2, 3]
        )->then($mock);
    }

    public function testShouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()]
        )->then($mock);

        $d2->resolve(2);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    public function testShouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            [null, 1, null, 2, 3]
        )->then($mock);
    }

    public function testShouldRejectIfFirstSettledPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()]
        )->then($this->expectCallableNever(), $mock);

        $d2->reject(2);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        race(
            resolve([1, 2, 3])
        )->then($mock);
    }

    public function testShouldResolveToNullWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            resolve(1)
        )->then($mock);
    }

    public function testShouldRejectWhenInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            reject()
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

        race($mock)->cancel();
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

        race([$mock1, $mock2])->cancel();
    }

    public function testShouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseFulfills()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $deferred = New Deferred($mock);
        $deferred->resolve();

        $mock2 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        race([$deferred->promise(), $mock2])->cancel();
    }

    public function testShouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $deferred = New Deferred($mock);
        $deferred->reject();

        $mock2 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        race([$deferred->promise(), $mock2])->cancel();
    }
}
