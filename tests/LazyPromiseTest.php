<?php

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class LazyPromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $d = new Deferred($canceller);

        $factory = function () use ($d) {
            return $d->promise();
        };

        return new CallbackPromiseAdapter([
            'promise'  => function () use ($factory) {
                return new LazyPromise($factory);
            },
            'resolve' => [$d, 'resolve'],
            'reject'  => [$d, 'reject'],
            'notify'  => [$d, 'progress'],
            'settle'  => [$d, 'resolve'],
        ]);
    }

    public function testShouldNotCallFactoryIfThenIsNotInvoked()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->never())
            ->method('__invoke');

        new LazyPromise($factory);
    }

    public function testShouldCallFactoryIfThenIsInvoked()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke');

        $p = new LazyPromise($factory);
        $p->then();
    }

    public function testShouldReturnPromiseFromFactory()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(new FulfilledPromise(1)));

        $onFulfilled = $this->createCallableMock();
        $onFulfilled
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $p = new LazyPromise($factory);

        $p->then($onFulfilled);
    }

    public function testShouldReturnPromiseIfFactoryReturnsNull()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(null));

        $p = new LazyPromise($factory);
        $this->assertInstanceOf('React\\Promise\\PromiseInterface', $p->then());
    }

    public function testShouldReturnRejectedPromiseIfFactoryThrowsException()
    {
        $exception = new \Exception();

        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $onRejected = $this->createCallableMock();
        $onRejected
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $p = new LazyPromise($factory);

        $p->then($this->expectCallableNever(), $onRejected);
    }
}
