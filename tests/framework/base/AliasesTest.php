<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\base;

use yii\base\Aliases;
use yii\exceptions\InvalidArgumentException;
use yii\tests\TestCase;

/**
 * AliasesTest.
 * @group base
 */
class AliasesTest extends TestCase
{
    /**
     * @var Aliases
     */
    public $aliases;

    protected function setUp()
    {
        parent::setUp();
        $this->aliases = new Aliases;
    }

    public function testDI()
    {
        $aliases = $this->container->get('aliases');
        $this->assertSame($aliases, $this->container->get(Aliases::class));

        $this->assertEquals(YII_PATH, $aliases->get('@yii'));
    }

    public function testGet()
    {

        $this->assertFalse($this->aliases->get('@nonexisting', false));

        $aliasNotBeginsWithAt = 'alias not begins with @';
        $this->assertEquals($aliasNotBeginsWithAt, $this->aliases->get($aliasNotBeginsWithAt));

        $this->aliases->set('@yii', '/yii/framework');
        $this->assertEquals('/yii/framework', $this->aliases->get('@yii'));
        $this->assertEquals('/yii/framework/test/file', $this->aliases->get('@yii/test/file'));
        $this->aliases->set('yii/gii', '/yii/gii');
        $this->assertEquals('/yii/framework', $this->aliases->get('@yii'));
        $this->assertEquals('/yii/framework/test/file', $this->aliases->get('@yii/test/file'));
        $this->assertEquals('/yii/gii', $this->aliases->get('@yii/gii'));
        $this->assertEquals('/yii/gii/file', $this->aliases->get('@yii/gii/file'));

        $this->aliases->set('@tii', '@yii/test');
        $this->assertEquals('/yii/framework/test', $this->aliases->get('@tii'));

        $this->aliases->set('@yii', null);
        $this->assertFalse($this->aliases->get('@yii', false));
        $this->assertEquals('/yii/gii/file', $this->aliases->get('@yii/gii/file'));

        $this->aliases->set('@some/alias', '/www');
        $this->assertEquals('/www', $this->aliases->get('@some/alias'));

        $erroneousAlias = '@alias_not_exists';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid path alias: %s', $erroneousAlias));
        $this->aliases->get($erroneousAlias, true);
    }

    public function testGetRoot()
    {
        $this->aliases->set('@yii', '/yii/framework');
        $this->assertEquals('@yii', $this->aliases->getRoot('@yii'));
        $this->assertEquals('@yii', $this->aliases->getRoot('@yii/test/file'));
        $this->aliases->set('@yii/gii', '/yii/gii');
        $this->assertEquals('@yii/gii', $this->aliases->getRoot('@yii/gii'));
    }

    public function testSet()
    {
        $this->aliases->set('@yii/gii', '/yii/gii');
        $this->assertEquals('/yii/gii', $this->aliases->get('@yii/gii'));
        $this->aliases->set('@yii/tii', '/yii/tii');
        $this->assertEquals('/yii/tii', $this->aliases->get('@yii/tii'));
    }
}
