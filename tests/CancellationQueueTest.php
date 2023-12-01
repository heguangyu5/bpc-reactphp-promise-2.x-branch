<?php

namespace React\Promise;

class CancellationQueueTest extends TestCase
{
    public function testAcceptsSimpleCancellableThenable()
    {
        $p = new SimpleTestCancellableThenable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        $this->assertTrue($p->cancelCalled);
    }

    public function testIgnoresSimpleCancellable()
    {
        $p = new SimpleTestCancellable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        $this->assertFalse($p->cancelCalled);
    }

    public function testCallsCancelOnPromisesEnqueuedBeforeStart()
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d1->promise());
        $cancellationQueue->enqueue($d2->promise());

        $cancellationQueue();
    }

    public function testCallsCancelOnPromisesEnqueuedAfterStart()
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();

        $cancellationQueue();

        $cancellationQueue->enqueue($d2->promise());
        $cancellationQueue->enqueue($d1->promise());
    }

    public function testDoesNotCallCancelTwiceWhenStartedTwice()
    {
        $d = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d->promise());

        $cancellationQueue();
        $cancellationQueue();
    }

    public function testRethrowsExceptionsThrownFromCancel()
    {
        $this->setExpectedException('\Exception', 'test');

        $mock = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('cancel')
            ->will($this->throwException(new \Exception('test')));

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($mock);

        $cancellationQueue();
    }

    private function getCancellableDeferred()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return new Deferred($mock);
    }
}
