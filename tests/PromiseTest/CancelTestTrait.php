<?php

namespace React\Promise\PromiseTest;

use React\Promise;

trait CancelTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testCancelShouldCallCancellerWithResolverArguments()
    {
        $args = null;
        $adapter = $this->getPromiseTestAdapter(function ($resolve, $reject, $notify) use (&$args) {
            $args = func_get_args();
        });

        $adapter->promise()->cancel();

        $this->assertCount(3, $args);
        $this->assertTrue(is_callable($args[0]));
        $this->assertTrue(is_callable($args[1]));
        $this->assertTrue(is_callable($args[2]));
    }

    public function testCancelShouldCallCancellerWithoutArgumentsIfNotAccessed()
    {
        $args = null;
        $adapter = $this->getPromiseTestAdapter(function () use (&$args) {
            $args = func_num_args();
        });

        $adapter->promise()->cancel();

        $this->assertSame(0, $args);
    }

    public function testCancelShouldFulfillPromiseIfCancellerFulfills()
    {
        $adapter = $this->getPromiseTestAdapter(function ($resolve) {
            $resolve(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock, $this->expectCallableNever());

        $adapter->promise()->cancel();
    }

    public function testCancelShouldRejectPromiseIfCancellerRejects()
    {
        $adapter = $this->getPromiseTestAdapter(function ($resolve, $reject) {
            $reject(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    public function testCancelShouldRejectPromiseWithExceptionIfCancellerThrows()
    {
        $e = new \Exception();

        $adapter = $this->getPromiseTestAdapter(function () use ($e) {
            throw $e;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($e));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    public function testCancelShouldProgressPromiseIfCancellerNotifies()
    {
        $adapter = $this->getPromiseTestAdapter(function ($resolve, $reject, $progress) {
            $progress(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    public function testCancelShouldCallCancellerOnlyOnceIfCancellerResolves()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(function ($resolve) {
                $resolve();
            }));

        $adapter = $this->getPromiseTestAdapter($mock);

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    public function testCancelShouldHaveNoEffectIfCancellerDoesNothing()
    {
        $adapter = $this->getPromiseTestAdapter(function () {});

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    public function testCancelShouldCallCancellerFromDeepNestedPromiseChain()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        $adapter = $this->getPromiseTestAdapter($mock);

        $promise = $adapter->promise()
            ->then(function () {
                return new Promise\Promise(function () {});
            })
            ->then(function () {
                $d = new Promise\Deferred();

                return $d->promise();
            })
            ->then(function () {
                return new Promise\Promise(function () {});
            });

        $promise->cancel();
    }

    public function testCancelCalledOnChildrenSouldOnlyCancelWhenAllChildrenCancelled()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $child1->cancel();
    }

    public function testCancelShouldTriggerCancellerWhenAllChildrenCancel()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $child2 = $adapter->promise()
            ->then();

        $child1->cancel();
        $child2->cancel();
    }

    public function testCancelShouldNotTriggerCancellerWhenCancellingOneChildrenMultipleTimes()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $child2 = $adapter->promise()
            ->then();

        $child1->cancel();
        $child1->cancel();
    }

    public function testCancelShouldTriggerCancellerOnlyOnceWhenCancellingMultipleTimes()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    public function testCancelShouldAlwaysTriggerCancellerWhenCalledOnRootPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $adapter->promise()->cancel();
    }
}
