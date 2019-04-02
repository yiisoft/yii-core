<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\helpers;

use Psr\Log\LogLevel;
use yii\helpers\BaseYii;
use yii\log\Logger;
use yii\tests\data\base\Singer;
use yii\tests\TestCase;

/**
 * BaseYiiTest.
 * @group base
 */
class BaseYiiTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    public function testAliases()
    {
        $this->mockApplication();
        $this->app->setAlias('@test', __METHOD__);
        $this->assertEquals(__METHOD__, $this->app->getAlias('@test'));
    }

    public function testGetVersion()
    {
        $this->assertTrue((bool) preg_match('~\d+\.\d+(?:\.\d+)?(?:-\w+)?~', $this->app->getVersion()));
    }

    public function testCreateObject()
    {
        $object = $this->app->createObject([
            '__class' => Singer::class,
            'firstName' => 'John',
        ]);
        $this->assertTrue($object instanceof Singer);
        $this->assertSame('John', $object->firstName);

        $object = $this->app->createObject([
            '__class' => Singer::class,
            'firstName' => 'Michael',
        ]);
        $this->assertTrue($object instanceof Singer);
        $this->assertSame('Michael', $object->firstName);

        $this->expectException(\yii\di\exceptions\InvalidConfigException::class);
        $this->expectExceptionMessage('Object configuration array must contain a "__class" element.');
        $object = $this->app->createObject([
            'firstName' => 'John',
        ]);
    }

    /**
     * @depends testCreateObject
     */
    public function testCreateObjectCallable()
    {
        // Test passing in of normal params combined with DI params.
        $this->assertNotEmpty($this->app->createObject(function (Singer $singer, $a) {
            return $a === 'a';
        }, ['a']));


        $singer = new Singer();
        $singer->firstName = 'Bob';
        $this->assertNotEmpty($this->app->createObject(function (Singer $singer, $a) {
            return $singer->firstName === 'Bob';
        }, [$singer, 'a']));


        $this->assertNotEmpty($this->app->createObject(function (Singer $singer, $a = 3) {
            return true;
        }));
    }

    public function testCreateObjectEmptyArrayException()
    {
        $this->expectException(\yii\di\exceptions\InvalidConfigException::class);
        $this->expectExceptionMessage('Object configuration array must contain a "__class" element.');

        $this->app->createObject([]);
    }

    public function testCreateObjectInvalidConfigException()
    {
        $this->expectException(\yii\di\exceptions\InvalidConfigException::class);
        $this->expectExceptionMessage('Unsupported configuration type: ' . gettype(null));

        $this->app->createObject(null);
    }

    /**
     * @covers \yii\helpers\BaseYii::info()
     * @covers \yii\helpers\BaseYii::warning()
     * @covers \yii\helpers\BaseYii::debug()
     * @covers \yii\helpers\BaseYii::error()
     */
    public function testLog()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([[]])
            ->setMethods(['log'])
            ->getMock();
        $this->container->set('logger', $logger);

        $logger->expects($this->exactly(4))
            ->method('log')
            ->withConsecutive(
                [
                    $this->equalTo(LogLevel::INFO),
                    $this->equalTo('info message'),
                    $this->equalTo(['category' => 'info category'])
                ],
                [
                    $this->equalTo(LogLevel::WARNING),
                    $this->equalTo('warning message'),
                    $this->equalTo(['category' => 'warning category']),
                ],
                [
                    $this->equalTo(LogLevel::DEBUG),
                    $this->equalTo('trace message'),
                    $this->equalTo(['category' => 'trace category'])
                ],
                [
                    $this->equalTo(LogLevel::ERROR),
                    $this->equalTo('error message'),
                    $this->equalTo(['category' => 'error category'])
                ]
            );

        BaseYii::info('info message', 'info category');
        BaseYii::warning('warning message', 'warning category');
        BaseYii::debug('trace message', 'trace category');
        BaseYii::error('error message', 'error category');
    }

    /*
     * Phpunit calculate coverage better in case of small tests
     */
    public function testLoggerWithException()
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([[]])
            ->setMethods(['log'])
            ->getMock();
        $this->container->set('logger', $logger);
        $throwable = new \Exception('test');

        $logger
            ->expects($this->once())
            ->method('log')->with(
                $this->equalTo(LogLevel::ERROR),
                $this->equalTo($throwable),
                $this->equalTo(['category' => 'error category'])
            );

        BaseYii::error($throwable, 'error category');
    }

    /**
     * @covers \yii\helpers\BaseYii::beginProfile()
     * @covers \yii\helpers\BaseYii::endProfile()
     */
    public function testProfile()
    {
        $profiler = $this->getMockBuilder('yii\profile\Profiler')
            ->setMethods(['begin', 'end'])
            ->getMock();
        $this->container->set('profiler', $profiler);

        $profiler->expects($this->exactly(2))
            ->method('begin')
            ->withConsecutive(
                [
                    $this->equalTo('Profile message 1'),
                    $this->equalTo(['category' => 'Profile category 1'])
                ],
                [
                    $this->equalTo('Profile message 2'),
                    $this->equalTo(['category' => 'Profile category 2']),
                ]
            );

        $profiler->expects($this->exactly(2))
            ->method('end')
            ->withConsecutive(
                [
                    $this->equalTo('Profile message 1'),
                    $this->equalTo(['category' => 'Profile category 1'])
                ],
                [
                    $this->equalTo('Profile message 2'),
                    $this->equalTo(['category' => 'Profile category 2']),
                ]
            );

        BaseYii::beginProfile('Profile message 1', 'Profile category 1');
        BaseYii::endProfile('Profile message 1', 'Profile category 1');
        BaseYii::beginProfile('Profile message 2', 'Profile category 2');
        BaseYii::endProfile('Profile message 2', 'Profile category 2');
    }
}
