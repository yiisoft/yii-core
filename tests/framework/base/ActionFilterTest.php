<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\base;

use yii\helpers\Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\web\Controller;
use yii\web\User;
use yii\tests\TestCase;

/**
 * @group base
 */
class ActionFilterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testFilter()
    {
        // no filters
        $controller = new FakeController('fake', $this->app);

        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertEquals('x', $result);
        $this->assertNull($controller->result);

        // all filters pass
        $controller = new FakeController('fake', $this->app);
        $controller->behaviors = [
            'filter1' => Filter1::class,
            'filter3' => Filter3::class,
        ];
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertEquals('x-3-1', $result);
        $this->assertEquals([1, 3], $controller->result);

        // a filter stops in the middle
        $controller = new FakeController('fake', $this->app);
        $controller->behaviors = [
            'filter1' => Filter1::class,
            'filter2' => Filter2::class,
            'filter3' => Filter3::class,
        ];
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertNull($result);
        $this->assertEquals([1, 2], $controller->result);

        // the first filter stops
        $controller = new FakeController('fake', $this->app);
        $controller->behaviors = [
            'filter2' => Filter2::class,
            'filter1' => Filter1::class,
            'filter3' => Filter3::class,
        ];
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertNull($result);
        $this->assertEquals([2], $controller->result);

        // the last filter stops
        $controller = new FakeController('fake', $this->app);
        $controller->behaviors = [
            'filter1' => Filter1::class,
            'filter3' => Filter3::class,
            'filter2' => Filter2::class,
        ];
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertNull($result);
        $this->assertEquals([1, 3, 2], $controller->result);
    }


    public function actionFilterProvider()
    {
        return [
            [['__class' => \yii\web\filters\AccessControl::class, 'user' => \yii\tests\framework\base\MockUser::class]],
            [\yii\web\filters\ContentNegotiator::class],
            [\yii\web\filters\Cors::class],
            [\yii\web\filters\HttpCache::class],
            [\yii\web\filters\PageCache::class],
            [\yii\web\filters\RateLimiter::class],
        ];
    }

    /**
     * @dataProvider actionFilterProvider
     * @param string|array $filterClass
     */
    public function testActive($filterClass)
    {
        /** @var $filter ActionFilter */
        $filter = Yii::createObject($filterClass);
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('isActive');
        $method->setAccessible(true);

        $controller = new Controller('test', $this->app);

        // active by default
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertTrue($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index'];
        $filter->except = [];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index', 'view'];
        $filter->except = ['view'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only;
        $filter->except = ['view'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));
    }

    /**
     * @depends testActive
     */
    public function testActiveWildcard()
    {
        $filter = new ActionFilter();
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('isActive');
        $method->setAccessible(true);

        $controller = new Controller('test', $this->app);

        $filter->only = ['test/*'];
        $filter->except = [];
        $this->assertFalse($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertTrue($method->invokeArgs($filter, [new Action('test/index', $controller)]));

        $filter->only = [];
        $filter->except = ['test/*'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('test/index', $controller)]));
    }
}

class FakeController extends \yii\base\Controller
{
    public $result;
    public $behaviors = [];

    public function behaviors()
    {
        return $this->behaviors;
    }

    public function actionTest()
    {
        return 'x';
    }
}

class Filter1 extends ActionFilter
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $action->controller->result[] = 1;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        return $result . '-1';
    }
}

class Filter2 extends ActionFilter
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $action->controller->result[] = 2;
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        return $result . '-2';
    }
}

class Filter3 extends ActionFilter
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $action->controller->result[] = 3;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        return $result . '-3';
    }
}

class MockUser extends User
{
    public function init()
    {
        // do not call parent to avoid the need to mock configuration
    }
}
