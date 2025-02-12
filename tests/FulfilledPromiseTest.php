<?php

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class FulfilledPromiseTest extends TestCase
{
    use PromiseTest\PromiseSettledTestTrait,
        PromiseTest\PromiseFulfilledTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    throw new \LogicException('FulfilledPromise must be resolved before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => function ($value = null) use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
            'reject' => function () {
                throw new \LogicException('You cannot call reject() for React\Promise\FulfilledPromise');
            },
            'notify' => function () {
                // no-op
            },
            'settle' => function ($value = null) use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
        ]);
    }

    public function testShouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new FulfilledPromise(new FulfilledPromise());
    }

    public function testShouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToFulfilledPromiseWithAlwaysFollowers()
    {
        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $promise = new FulfilledPromise(1);
        $promise->always(function () {
            throw new \RuntimeException();
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    public function testShouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToFulfilledPromiseWithThenFollowers()
    {
        gc_collect_cycles();
        $promise = new FulfilledPromise(1);
        $promise = $promise->then(function () {
            throw new \RuntimeException();
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }
}
