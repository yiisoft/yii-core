<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\data\console\controllers\fixtures\subdir;

use yii\test\Fixture;
use yii\tests\data\console\controllers\fixtures\FixtureStorage;

class FirstFixture extends Fixture
{
    public function load()
    {
        FixtureStorage::$subdirFirstFixtureData[] = 'some data set for first fixture in subdir';
    }

    public function unload()
    {
        FixtureStorage::$subdirFirstFixtureData = [];
    }
}
