<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\base;

use yii\exceptions\InvalidArgumentException;
use yii\tests\TestCase;

/**
 * AliasesTest.
 * @group base
 */
class AliasesTest extends TestCase
{
    public $obj;

    protected function setUp()
    {
        parent::setUp();
        $this->obj = $this->container->get('aliases');
    }

    public function testDefaultAliases()
    {
        $this->assertEquals(YII_PATH, $this->obj->getAlias('@yii'));
        $this->assertEquals(dirname(__DIR__, 3), $this->obj->getAlias('@root'));
        $this->assertEquals(dirname(__DIR__, 3) . '/vendor', $this->obj->getAlias('@vendor'));
        $this->assertEquals(dirname(__DIR__, 3) . '/runtime', $this->obj->getAlias('@runtime'));
    }

    public function testGetAlias()
    {
        $this->assertEquals(YII_PATH, $this->obj->getAlias('@yii'));

        $this->assertFalse($this->obj->getAlias('@nonexisting', false));

        $aliasNotBeginsWithAt = 'alias not begins with @';
        $this->assertEquals($aliasNotBeginsWithAt, $this->obj->getAlias($aliasNotBeginsWithAt));

        $this->obj->setAlias('@yii', '/yii/framework');
        $this->assertEquals('/yii/framework', $this->obj->getAlias('@yii'));
        $this->assertEquals('/yii/framework/test/file', $this->obj->getAlias('@yii/test/file'));
        $this->obj->setAlias('yii/gii', '/yii/gii');
        $this->assertEquals('/yii/framework', $this->obj->getAlias('@yii'));
        $this->assertEquals('/yii/framework/test/file', $this->obj->getAlias('@yii/test/file'));
        $this->assertEquals('/yii/gii', $this->obj->getAlias('@yii/gii'));
        $this->assertEquals('/yii/gii/file', $this->obj->getAlias('@yii/gii/file'));

        $this->obj->setAlias('@tii', '@yii/test');
        $this->assertEquals('/yii/framework/test', $this->obj->getAlias('@tii'));

        $this->obj->setAlias('@yii', null);
        $this->assertFalse($this->obj->getAlias('@yii', false));
        $this->assertEquals('/yii/gii/file', $this->obj->getAlias('@yii/gii/file'));

        $this->obj->setAlias('@some/alias', '/www');
        $this->assertEquals('/www', $this->obj->getAlias('@some/alias'));

        $erroneousAlias = '@alias_not_exists';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid path alias: %s', $erroneousAlias));
        $this->obj->getAlias($erroneousAlias, true);
    }

    public function testGetRootAlias()
    {
        $this->obj->setAlias('@yii', '/yii/framework');
        $this->assertEquals('@yii', $this->obj->getRootAlias('@yii'));
        $this->assertEquals('@yii', $this->obj->getRootAlias('@yii/test/file'));
        $this->obj->setAlias('@yii/gii', '/yii/gii');
        $this->assertEquals('@yii/gii', $this->obj->getRootAlias('@yii/gii'));
    }

    public function testSetAlias()
    {
        $this->obj->setAlias('@yii/gii', '/yii/gii');
        $this->assertEquals('/yii/gii', $this->obj->getAlias('@yii/gii'));
        $this->obj->setAlias('@yii/tii', '/yii/tii');
        $this->assertEquals('/yii/tii', $this->obj->getAlias('@yii/tii'));
    }
}
