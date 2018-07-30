<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\base;

use Psr\Log\NullLogger;
use yii\helpers\Yii;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Module;
use yii\log\Logger;
use yii\tests\TestCase;

/**
 * @group base
 */
class ApplicationTest extends TestCase
{
    public function testContainerSettingsAffectBootstrap()
    {
        $this->container->setAll([
            'logger' => [
                '__class' => NullLogger::class,
            ],
        ]);
        $this->mockApplication();

        $this->assertInstanceOf(NullLogger::class, $this->app->getLogger());
    }

    public function testBootstrap()
    {
        Yii::getLogger()->flush();

        $this->mockApplication([
            'components' => [
                'withoutBootstrapInterface' => [
                    '__class' => Component::class
                ],
                'withBootstrapInterface' => [
                    '__class' => BootstrapComponentMock::class
                ]
            ],
            'modules' => [
                'moduleX' => [
                    '__class' => Module::class
                ]
            ],
            'bootstrap' => [
                'withoutBootstrapInterface',
                'withBootstrapInterface',
                'moduleX',
                function () {
                },
            ],
        ]);
        $this->assertSame('Bootstrap with yii\base\Component', Yii::getLogger()->messages[0][1]);
        $this->assertSame('Bootstrap with yii\tests\framework\base\BootstrapComponentMock::bootstrap()', Yii::getLogger()->messages[1][1]);
        $this->assertSame('Loading module: moduleX', Yii::getLogger()->messages[2][1]);
        $this->assertSame('Bootstrap with yii\base\Module', Yii::getLogger()->messages[3][1]);
        $this->assertSame('Bootstrap with Closure', Yii::getLogger()->messages[4][1]);
    }
}

class BootstrapComponentMock extends Component implements BootstrapInterface
{
    public function bootstrap($app)
    {
    }
}
