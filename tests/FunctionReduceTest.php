<?php

namespace React\Promise;

class FunctionReduceTest extends TestCase
{
    protected function plus()
    {
        return function ($sum, $val) {
            return $sum + $val;
        };
    }

    protected function append()
    {
        return function ($sum, $val) {
            return $sum . $val;
        };
    }

    public function testShouldReduceValuesWithoutInitialValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(6));

        reduce(
            [1, 2, 3],
            $this->plus()
        )->then($mock);
    }

    public function testShouldReduceValuesWithInitialValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [1, 2, 3],
            $this->plus(),
            1
        )->then($mock);
    }

    public function testShouldReduceValuesWithInitialPromise()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [1, 2, 3],
            $this->plus(),
            resolve(1)
        )->then($mock);
    }

    public function testShouldReducePromisedValuesWithoutInitialValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(6));

        reduce(
            [resolve(1), resolve(2), resolve(3)],
            $this->plus()
        )->then($mock);
    }

    public function testShouldReducePromisedValuesWithInitialValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [resolve(1), resolve(2), resolve(3)],
            $this->plus(),
            1
        )->then($mock);
    }

    public function testShouldReducePromisedValuesWithInitialPromise()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [resolve(1), resolve(2), resolve(3)],
            $this->plus(),
            resolve(1)
        )->then($mock);
    }

    public function testShouldReduceEmptyInputWithInitialValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        reduce(
            [],
            $this->plus(),
            1
        )->then($mock);
    }

    public function testShouldReduceEmptyInputWithInitialPromise()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        reduce(
            [],
            $this->plus(),
            resolve(1)
        )->then($mock);
    }

    public function testShouldRejectWhenInputContainsRejection()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        reduce(
            [resolve(1), reject(2), resolve(3)],
            $this->plus(),
            resolve(1)
        )->then($this->expectCallableNever(), $mock);
    }

    public function testShouldResolveWithNullWhenInputIsEmptyAndNoInitialValueOrPromiseProvided()
    {
        // Note: this is different from when.js's behavior!
        // In when.reduce(), this rejects with a TypeError exception (following
        // JavaScript's [].reduce behavior.
        // We're following PHP's array_reduce behavior and resolve with NULL.
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        reduce(
            [],
            $this->plus()
        )->then($mock);
    }

    public function testShouldAllowSparseArrayInputWithoutInitialValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(3));

        reduce(
            [null, null, 1, null, 1, 1],
            $this->plus()
        )->then($mock);
    }

    public function testShouldAllowSparseArrayInputWithInitialValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(4));

        reduce(
            [null, null, 1, null, 1, 1],
            $this->plus(),
            1
        )->then($mock);
    }

    public function testShouldReduceInInputOrder()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('123'));

        reduce(
            [1, 2, 3],
            $this->append(),
            ''
        )->then($mock);
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('123'));

        reduce(
            resolve([1, 2, 3]),
            $this->append(),
            ''
        )->then($mock);
    }

    public function testShouldResolveToInitialValueWhenInputPromiseDoesNotResolveToAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        reduce(
            resolve(1),
            $this->plus(),
            1
        )->then($mock);
    }

    public function testShouldProvideCorrectBasisValue()
    {
        $insertIntoArray = function ($arr, $val, $i) {
            $arr[$i] = $val;

            return $arr;
        };

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        reduce(
            [$d1->promise(), $d2->promise(), $d3->promise()],
            $insertIntoArray,
            []
        )->then($mock);

        $d3->resolve(3);
        $d1->resolve(1);
        $d2->resolve(2);
    }

    public function testShouldRejectWhenInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        reduce(
            reject(),
            $this->plus(),
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

        reduce(
            $mock,
            $this->plus(),
            1
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

        reduce(
            [$mock1, $mock2],
            $this->plus(),
            1
        )->cancel();
    }
}
