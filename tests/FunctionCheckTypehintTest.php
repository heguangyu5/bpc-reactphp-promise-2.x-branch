<?php

namespace React\Promise;

class FunctionCheckTypehintTest extends TestCase
{
    public function testShouldAcceptClosureCallbackWithTypehint()
    {
        $this->assertTrue(_checkTypehint(function (\InvalidArgumentException $e) {
                }, new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint(function (\InvalidArgumentException $e) {
                }, new \Exception()));
    }

    public function testShouldAcceptFunctionStringCallbackWithTypehint()
    {
        $this->assertTrue(_checkTypehint('React\Promise\testCallbackWithTypehint', new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint('React\Promise\testCallbackWithTypehint', new \Exception()));
    }

    public function testShouldAcceptInvokableObjectCallbackWithTypehint()
    {
        $this->assertTrue(_checkTypehint(new CallbackWithTypehintClass(), new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint(new CallbackWithTypehintClass(), new \Exception()));
    }

    public function testShouldAcceptObjectMethodCallbackWithTypehint()
    {
        $this->assertTrue(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new \Exception()));
    }

    public function testShouldAcceptStaticClassCallbackWithTypehint()
    {
        $this->assertTrue(_checkTypehint([new CallbackWithTypehintClass(), 'testCallbackStatic'], new \InvalidArgumentException()));
        $this->assertfalse(_checkTypehint([new CallbackWithTypehintClass(), 'testCallbackStatic'], new \Exception()));
    }

//    /**
//     * @test
//     * @requires PHP 8
//     */
//    public function shouldAcceptClosureCallbackWithUnionTypehint()
//    {
//        eval(
//            'namespace React\Promise;' .
//            'self::assertTrue(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \InvalidArgumentException()));' .
//            'self::assertFalse(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \Exception()));'
//        );
//    }

//    /**
//     * @test
//     * @requires PHP 8
//     */
//    public function shouldAcceptInvokableObjectCallbackWithUnionTypehint()
//    {
//        self::assertTrue(_checkTypehint(new CallbackWithUnionTypehintClass(), new \InvalidArgumentException()));
//        self::assertFalse(_checkTypehint(new CallbackWithUnionTypehintClass(), new \Exception()));
//    }

//    /**
//     * @test
//     * @requires PHP 8
//     */
//    public function shouldAcceptObjectMethodCallbackWithUnionTypehint()
//    {
//        self::assertTrue(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
//        self::assertFalse(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new \Exception()));
//    }

//    /**
//     * @test
//     * @requires PHP 8
//     */
//    public function shouldAcceptStaticClassCallbackWithUnionTypehint()
//    {
//        self::assertTrue(_checkTypehint(['React\Promise\CallbackWithUnionTypehintClass', 'testCallbackStatic'], new \InvalidArgumentException()));
//        self::assertFalse(_checkTypehint(['React\Promise\CallbackWithUnionTypehintClass', 'testCallbackStatic'], new \Exception()));
//    }

//    /**
//     * @test
//     * @requires PHP 8.1
//     */
//    public function shouldAcceptInvokableObjectCallbackWithIntersectionTypehint()
//    {
//        self::assertFalse(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new \RuntimeException()));
//        self::assertFalse(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new CountableNonException()));
//        self::assertTrue(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new CountableException()));
//    }

//    /**
//     * @test
//     * @requires PHP 8.1
//     */
//    public function shouldAcceptObjectMethodCallbackWithIntersectionTypehint()
//    {
//        self::assertFalse(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new \RuntimeException()));
//        self::assertFalse(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new CountableNonException()));
//        self::assertTrue(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new CountableException()));
//    }

//    /**
//     * @test
//     * @requires PHP 8.1
//     */
//    public function shouldAcceptStaticClassCallbackWithIntersectionTypehint()
//    {
//        self::assertFalse(_checkTypehint(['React\Promise\CallbackWithIntersectionTypehintClass', 'testCallbackStatic'], new \RuntimeException()));
//        self::assertFalse(_checkTypehint(['React\Promise\CallbackWithIntersectionTypehintClass', 'testCallbackStatic'], new CountableNonException()));
//        self::assertTrue(_checkTypehint(['React\Promise\CallbackWithIntersectionTypehintClass', 'testCallbackStatic'], new CountableException()));
//    }

//    /**
//     * @test
//     * @requires PHP 8.2
//     */
//    public function shouldAcceptInvokableObjectCallbackWithDNFTypehint()
//    {
//        self::assertFalse(_checkTypehint(new CallbackWithDNFTypehintClass(), new \RuntimeException()));
//        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new ArrayAccessibleException()));
//        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new CountableException()));
//    }

//    /**
//     * @test
//     * @requires PHP 8.2
//     */
//    public function shouldAcceptObjectMethodCallbackWithDNFTypehint()
//    {
//        self::assertFalse(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new \RuntimeException()));
//        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new CountableException()));
//        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new ArrayAccessibleException()));
//    }

//    /**
//     * @test
//     * @requires PHP 8.2
//     */
//    public function shouldAcceptStaticClassCallbackWithDNFTypehint()
//    {
//        self::assertFalse(_checkTypehint(['React\Promise\CallbackWithDNFTypehintClass', 'testCallbackStatic'], new \RuntimeException()));
//        self::assertTrue(_checkTypehint(['React\Promise\CallbackWithDNFTypehintClass', 'testCallbackStatic'], new CountableException()));
//        self::assertTrue(_checkTypehint(['React\Promise\CallbackWithDNFTypehintClass', 'testCallbackStatic'], new ArrayAccessibleException()));
//    }

    public function testShouldAcceptClosureCallbackWithoutTypehint()
    {
        $this->assertTrue(_checkTypehint(function (\InvalidArgumentException $e) {
        }, new \InvalidArgumentException()));
    }

    public function testShouldAcceptFunctionStringCallbackWithoutTypehint()
    {
        $this->assertTrue(_checkTypehint('React\Promise\testCallbackWithoutTypehint', new \InvalidArgumentException()));
    }

    public function testShouldAcceptInvokableObjectCallbackWithoutTypehint()
    {
        $this->assertTrue(_checkTypehint(new CallbackWithoutTypehintClass(), new \InvalidArgumentException()));
    }

    public function testShouldAcceptObjectMethodCallbackWithoutTypehint()
    {
        $this->assertTrue(_checkTypehint([new CallbackWithoutTypehintClass(), 'testCallback'], new \InvalidArgumentException()));
    }

    public function testShouldAcceptStaticClassCallbackWithoutTypehint()
    {
        $this->assertTrue(_checkTypehint(['React\Promise\CallbackWithoutTypehintClass', 'testCallbackStatic'], new \InvalidArgumentException()));
    }
}

function testCallbackWithTypehint(\InvalidArgumentException $e)
{
}

function testCallbackWithoutTypehint()
{
}
