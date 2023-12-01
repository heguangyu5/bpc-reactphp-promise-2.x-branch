<?php

namespace React\Promise\PromiseTest;

use React\Promise;
use React\Promise\Deferred;

trait RejectTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testRejectShouldRejectWithAnImmediateValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(1);
    }

    public function testRejectShouldRejectWithFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(Promise\resolve(1));
    }

    public function testRejectShouldRejectWithRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(Promise\reject(1));
    }

    public function testRejectShouldForwardReasonWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject(1);
    }

    public function testRejectShouldMakePromiseImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(null, function ($value) use ($adapter) {
                $adapter->reject(3);

                return Promise\reject($value);
            })
            ->then(
                $this->expectCallableNever(),
                $mock
            );

        $adapter->reject(1);
        $adapter->reject(2);
    }

    public function testNotifyShouldInvokeOtherwiseHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->otherwise($mock);

        $adapter->reject(1);
    }

    public function testDoneShouldInvokeRejectionHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done(null, $mock));
        $adapter->reject(1);
    }

    public function testDoneShouldThrowExceptionThrownByRejectionHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, function () {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->reject(1);
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenRejectedWithNonException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('React\\Promise\\UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(1);
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenRejectionHandlerRejects()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('React\\Promise\\UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, function () {
            return \React\Promise\reject();
        }));
        $adapter->reject(1);
    }

    public function testDoneShouldThrowRejectionExceptionWhenRejectionHandlerRejectsWithException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, function () {
            return \React\Promise\reject(new \Exception('UnhandledRejectionException'));
        }));
        $adapter->reject(1);
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenRejectionHandlerRetunsPendingPromiseWhichRejectsLater()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('React\\Promise\\UnhandledRejectionException');

        $d = new Deferred();
        $promise = $d->promise();

        $this->assertNull($adapter->promise()->done(null, function () use ($promise) {
            return $promise;
        }));
        $adapter->reject(1);
        $d->reject(1);
    }

    public function testDoneShouldThrowExceptionProvidedAsRejectionValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(new \Exception('UnhandledRejectionException'));
    }

    public function testDoneShouldThrowWithDeepNestingPromiseChains()
    {
        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $exception = new \Exception('UnhandledRejectionException');

        $d = new Deferred();

        $result = \React\Promise\resolve(\React\Promise\resolve($d->promise()->then(function () use ($exception) {
            $d = new Deferred();
            $d->resolve();

            return \React\Promise\resolve($d->promise()->then(function () {}))->then(
                function () use ($exception) {
                    throw $exception;
                }
            );
        })));

        $result->done();

        $d->resolve();
    }

    public function testDoneShouldRecoverWhenRejectionHandlerCatchesException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, function (\Exception $e) {

        }));
        $adapter->reject(new \Exception('UnhandledRejectionException'));
    }

    public function testAlwaysShouldNotSuppressRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {})
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    public function testAlwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
                return 1;
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    public function testAlwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
                return \React\Promise\resolve(1);
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    public function testAlwaysShouldRejectWhenHandlerThrowsForRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () use ($exception) {
                throw $exception;
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    public function testAlwaysShouldRejectWhenHandlerRejectsForRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () use ($exception) {
                return \React\Promise\reject($exception);
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }
}
