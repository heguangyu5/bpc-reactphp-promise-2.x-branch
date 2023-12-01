<?php

namespace React\Promise;

use React\Promise\Exception\LengthException;

class FunctionSomeTest extends TestCase
{
    public function testShouldRejectWithLengthExceptionWithEmptyInputArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(function($exception){
                    return $exception instanceof LengthException &&
                           'Input array must contain at least 1 item but contains only 0 items.' === $exception->getMessage();
                })
            );

        some(
            [],
            1
        )->then($this->expectCallableNever(), $mock);
    }

    public function testShouldRejectWithLengthExceptionWithInputArrayContainingNotEnoughItems()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(function($exception){
                    return $exception instanceof LengthException &&
                           'Input array must contain at least 4 items but contains only 3 items.' === $exception->getMessage();
                })
            );

        some(
            [1, 2, 3],
            4
        )->then($this->expectCallableNever(), $mock);
    }

    public function testShouldResolveToEmptyArrayWithNonArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        some(
            null,
            1
        )->then($mock);
    }

    public function testShouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2]));

        some(
            [1, 2, 3],
            2
        )->then($mock);
    }

    public function testShouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2]));

        some(
            [resolve(1), resolve(2), resolve(3)],
            2
        )->then($mock);
    }

    public function testShouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([null, 1]));

        some(
            [null, 1, null, 2, 3],
            2
        )->then($mock);
    }

    public function testShouldRejectIfAnyInputPromiseRejectsBeforeDesiredNumberOfInputsAreResolved()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1 => 2, 2 => 3]));

        some(
            [resolve(1), reject(2), reject(3)],
            2
        )->then($this->expectCallableNever(), $mock);
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2]));

        some(
            resolve([1, 2, 3]),
            2
        )->then($mock);
    }

    public function testShouldResolveWithEmptyArrayIfHowManyIsLessThanOne()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        some(
            [1],
            0
        )->then($mock);
    }

    public function testShouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        some(
            resolve(1),
            1
        )->then($mock);
    }

    public function testShouldRejectWhenInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        some(
            reject(),
            1
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

        some($mock, 1)->cancel();
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

        some([$mock1, $mock2], 1)->cancel();
    }

    public function testShouldNotCancelOtherPendingInputArrayPromisesIfEnoughPromisesFulfill()
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

        some([$deferred->promise(), $mock2], 1);
    }

    public function testShouldNotCancelOtherPendingInputArrayPromisesIfEnoughPromisesReject()
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

        some([$deferred->promise(), $mock2], 2);
    }
}
