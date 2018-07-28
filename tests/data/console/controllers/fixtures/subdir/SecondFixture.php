<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\data\console\controllers\fixtures\subdir;

use yii\test\Fixture;
use yii\tests\data\console\controllers\fixtures\FixtureStorage;

class SecondFixture extends Fixture
{
    public function load()
    {
        FixtureStorage::$subdirSecondFixtureData[] = 'some data set for subdir/second fixture';
    }

    public function unload()
    {
        FixtureStorage::$subdirSecondFixtureData = [];
    }
}
