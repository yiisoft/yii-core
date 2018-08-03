<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\mutex;

use yii\mutex\FileMutex;
use yii\tests\TestCase;

/**
 * Class FileMutexTest.
 *
 * @group mutex
 */
class FileMutexTest extends TestCase
{
    use MutexTestTrait;

    /**
     * @return FileMutex
     * @throws \yii\exceptions\InvalidConfigException
     */
    protected function createMutex()
    {
        return $this->app->createObject([
            '__class' => FileMutex::class,
            'mutexPath' => '@yii/tests/runtime/mutex',
        ]);
    }

    public function testDeleteLockFile()
    {
        $mutex = $this->createMutex();
        $fileName = $mutex->mutexPath . '/' . md5(self::$mutexName) . '.lock';

        $mutex->acquire(self::$mutexName);
        $this->assertFileExists($fileName);

        $mutex->release(self::$mutexName);
        $this->assertFileNotExists($fileName);
    }
}
