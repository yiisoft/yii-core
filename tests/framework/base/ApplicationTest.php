<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\base;

use Psr\Log\NullLogger;
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
        $this->container->setAll([
            'logger' => [
                '__class' => \yii\log\Logger::class,
            ],
            'withoutBootstrapInterface' => [
                '__class' => Component::class,
            ],
            'withBootstrapInterface' => [
                '__class' => BootstrapComponentMock::class,
            ],
        ]);
        $this->mockApplication([
            'modules' => [
                'moduleX' => [
                    '__class' => Module::class,
                ],
            ],
            'bootstrap' => [
                'withoutBootstrapInterface',
                'withBootstrapInterface',
                'moduleX',
                function () {},
            ],
        ]);

        $this->assertSame('Bootstrap with yii\base\Component', $this->app->getLogger()->messages[0][1] ?? null);
        $this->assertSame('Bootstrap with yii\tests\framework\base\BootstrapComponentMock::bootstrap()', $this->app->getLogger()->messages[1][1] ?? null);
        $this->assertSame('Loading module: moduleX', $this->app->getLogger()->messages[2][1] ?? null);
        $this->assertSame('Bootstrap with yii\base\Module', $this->app->getLogger()->messages[3][1] ?? null);
        $this->assertSame('Bootstrap with Closure', $this->app->getLogger()->messages[4][1] ?? null);
    }
}

class BootstrapComponentMock extends Component implements BootstrapInterface
{
    public function bootstrap($app)
    {
    }
}
