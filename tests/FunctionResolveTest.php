<?php

namespace React\Promise;

use function React\Promise\resolve;

class FunctionResolveTest extends TestCase
{
    public function testShouldResolveAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        resolve($expected)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    public function testShouldResolveAFulfilledPromise()
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        resolve($resolved)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    public function testShouldResolveAThenable()
    {
        $thenable = new SimpleFulfilledTestThenable();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('foo'));

        resolve($thenable)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    public function testShouldResolveACancellableThenable()
    {
        $thenable = new SimpleTestCancellableThenable();

        $promise = resolve($thenable);
        $promise->cancel();

        $this->assertTrue($thenable->cancelCalled);
    }

    public function testShouldRejectARejectedPromise()
    {
        $expected = 123;

        $resolved = new RejectedPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        resolve($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    public function testShouldSupportDeepNestingInPromiseChains()
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = resolve(resolve($d->promise()->then(function ($val) {
            $d = new Deferred();
            $d->resolve($val);

            $identity = function ($val) {
                return $val;
            };

            return resolve($d->promise()->then($identity))->then(
                function ($val) {
                    return !$val;
                }
            );
        })));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $result->then($mock);
    }

    public function testShouldSupportVeryDeepNestedPromises()
    {
        $deferreds = [];

        // @TODO Increase count once global-queue is merged
        for ($i = 0; $i < 10; $i++) {
            $deferreds[] = $d = new Deferred();
            $p = $d->promise();

            $last = $p;
            for ($j = 0; $j < 10; $j++) {
                $last = $last->then(function($result) {
                    return $result;
                });
            }
        }

        $p = null;
        foreach ($deferreds as $d) {
            if ($p) {
                $d->resolve($p);
            }

            $p = $d->promise();
        }

        $deferreds[0]->resolve(true);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $deferreds[0]->promise()->then($mock);
    }

    public function testReturnsExtendePromiseForSimplePromise()
    {
        $promise = $this
            ->getMockBuilder('React\Promise\PromiseInterface')
            ->getMock();

        $this->assertInstanceOf('React\Promise\ExtendedPromiseInterface', resolve($promise));
    }
}
