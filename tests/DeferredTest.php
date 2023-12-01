<?php

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class DeferredTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $d = new Deferred($canceller);

        return new CallbackPromiseAdapter([
            'promise' => [$d, 'promise'],
            'resolve' => [$d, 'resolve'],
            'reject'  => [$d, 'reject'],
            'notify'  => [$d, 'progress'],
            'settle'  => [$d, 'resolve'],
        ]);
    }

    public function testProgressIsAnAliasForNotify()
    {
        $deferred = new Deferred();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $deferred->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $deferred->progress($sentinel);
    }

    public function testShouldRejectWithoutCreatingGarbageCyclesIfCancellerRejectsWithException()
    {
        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $deferred = new Deferred(function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });
        $deferred->promise()->cancel();
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }

    public function testShouldRejectWithoutCreatingGarbageCyclesIfParentCancellerRejectsWithException()
    {
        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $deferred = new Deferred(function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });
        $deferred->promise()->then()->cancel();
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }

    public function testShouldRejectWithoutCreatingGarbageCyclesIfCancellerHoldsReferenceAndExplicitlyRejectWithException()
    {
        gc_collect_cycles();
        $deferred = new Deferred(function () use (&$deferred) { });
        $deferred->reject(new \Exception('foo'));
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }

    public function testShouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingDeferred()
    {
        gc_collect_cycles();
        $deferred = new Deferred();
        $deferred->promise();
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }

    public function testShouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingDeferredWithUnusedCanceller()
    {
        gc_collect_cycles();
        $deferred = new Deferred(function () { });
        $deferred->promise();
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }

    public function testShouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingDeferredWithNoopCanceller()
    {
        gc_collect_cycles();
        $deferred = new Deferred(function () { });
        $deferred->promise()->cancel();
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }
}
