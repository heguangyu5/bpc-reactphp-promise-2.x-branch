<?php

namespace React\Promise\PromiseTest;

trait NotifyTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testNotifyShouldProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $adapter->notify($sentinel);
    }

    public function testNotifyShouldPropagateProgressToDownstreamPromises()
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnArgument(0));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $adapter->notify($sentinel);
    }

    public function testNotifyShouldPropagateTransformedProgressToDownstreamPromises()
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $adapter->notify(1);
    }

    public function testNotifyShouldPropagateCaughtExceptionValueAsProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $adapter->notify(1);
    }

    public function testNotifyShouldForwardProgressEventsWhenIntermediaryCallbackTiedToAResolvedPromiseReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $promise2 = $adapter2->promise();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        // resolve BEFORE attaching progress handler
        $adapter->resolve();

        $adapter->promise()
            ->then(function () use ($promise2) {
                return $promise2;
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            );

        $adapter2->notify($sentinel);
    }

    public function testNotifyShouldForwardProgressEventsWhenIntermediaryCallbackTiedToAnUnresolvedPromiseReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $promise2 = $adapter2->promise();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(function () use ($promise2) {
                return $promise2;
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            );

        // resolve AFTER attaching progress handler
        $adapter->resolve();
        $adapter2->notify($sentinel);
    }

    public function testNotifyShouldForwardProgressWhenResolvedWithAnotherPromise()
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2
            );

        $adapter->resolve($adapter2->promise());
        $adapter2->notify($sentinel);
    }

    public function testNotifyShouldAllowResolveAfterProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock->expects($this->exactly(2))->method('__invoke')->withConsecutive(
            array($this->identicalTo(1)),
            array($this->identicalTo(2))
        );

        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever(),
                $mock
            );

        $adapter->notify(1);
        $adapter->resolve(2);
    }

    public function testNotifyShouldAllowRejectAfterProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock->expects($this->exactly(2))->method('__invoke')->withConsecutive(
            array($this->identicalTo(1)),
            array($this->identicalTo(2))
        );

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
                $mock
            );

        $adapter->notify(1);
        $adapter->reject(2);
    }

    public function testNotifyShouldReturnSilentlyOnProgressWhenAlreadyRejected()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(1);

        $this->assertNull($adapter->notify());
    }

    public function testNotifyShouldInvokeProgressHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()->progress($mock);
        $adapter->notify(1);
    }

    public function testNotifyShouldInvokeProgressHandlerFromDone()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done(null, null, $mock));
        $adapter->notify(1);
    }

    public function testNotifyShouldThrowExceptionThrownProgressHandlerFromDone()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, null, function () {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->notify(1);
    }
}
