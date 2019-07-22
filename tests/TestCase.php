<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests;

use yii\base\Application;
use yii\helpers\FileHelper;
use yii\helpers\Yii;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static $params;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var null|\yii\di\Container
     */
    protected $container;

    /**
     * @var null|\Yiisoft\Factory\Factory
     */
    protected $factory;

    protected $defaultAppConfig = [];

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->container = Yii::getContainer();
        if ($this->container !== null) {
            $this->factory = $this->container->get('factory');
            $this->defaultAppConfig = $this->container->getDefinition('app');
        }
    }

    /**
     * Returns a test configuration param from /data/config.php.
     * @param  string $name params name
     * @param  mixed $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require dirname(__DIR__) . '/config/tests/params.php';
        }

        return static::$params[$name] ?? $default;
    }

    /**
     * Clean up after test.
     * By default the application created with {@see mockApplication} will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    /**
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = null, array $services = [])
    {
        if ($this->app && empty($config) && empty($appClass)) {
            return;
        }
        if ($appClass) {
            $config['__class'] = $appClass;
        }
        $this->container->setAll(array_merge($services, [
            'app' => array_merge($this->defaultAppConfig, $config),
        ]));
        $this->app = $this->container->get('app');
    }

    protected function mockWebApplication($config = [], $appClass = \yii\web\Application::class, array $services = [])
    {
        $this->container->setAll([
            'request' => [
                '__class' => \yii\web\Request::class,
                'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                'scriptFile' => __DIR__ . '/index.php',
                'scriptUrl' => '/index.php',
            ],
            'response' => [
                '__class' => \yii\web\Response::class,
                'formatters' => [
                    \yii\web\Response::FORMAT_HTML => [
                        '__class' => \yii\web\formatters\HtmlResponseFormatter::class,
                    ],
                    \yii\web\Response::FORMAT_XML => [
                        '__class' => \yii\web\formatters\XmlResponseFormatter::class,
                    ],
                    \yii\web\Response::FORMAT_JSON => [
                        '__class' => \yii\web\formatters\JsonResponseFormatter::class,
                    ],
                    \yii\web\Response::FORMAT_JSONP => [
                        '__class' => \yii\web\formatters\JsonResponseFormatter::class,
                        'useJsonp' => true,
                    ],
                ],
            ],
            'errorHandler' => [
                '__class' => \yii\web\ErrorHandler::class,
                'errorAction' => 'site/error',
            ],
        ]);
        return $this->mockApplication($config, $appClass, $services);
    }

    protected function getVendorPath()
    {
        $vendor = dirname(dirname(__DIR__)) . '/vendor';
        if (!is_dir($vendor)) {
            $vendor = dirname(dirname(dirname(dirname(__DIR__))));
        }

        return $vendor;
    }

    /**
     * TODO how to destory application?
     */
    protected function destroyApplication()
    {
        if ($this->app && $this->app->has('session')) {
            $this->app->getSession()->close();
        }
        $this->app = null;
        $this->container->get('i18n')->setLocale('en-US');
        // TODO: this actually makes application unavailable
        // But requires fixing of many tests.
        // $this->container->set('app', null);
    }

    /**
     * Asserting two strings equality ignoring line endings.
     * @param string $expected
     * @param string $actual
     * @param string $message
     */
    protected function assertEqualsWithoutLE(string $expected, string $actual, string $message = ''): void
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * Invokes a inaccessible method.
     * @param $object
     * @param $method
     * @param array $args
     * @param bool $revoke whether to make method inaccessible after execution
     * @return mixed
     * @throws \ReflectionException
     */
    protected function invokeMethod($object, $method, $args = [], $revoke = true)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        if ($revoke) {
            $method->setAccessible(false);
        }

        return $result;
    }

    /**
     * Sets an inaccessible object property to a designated value.
     * @param $object
     * @param $propertyName
     * @param $value
     * @param bool $revoke whether to make property inaccessible after setting
     * @throws \ReflectionException
     */
    protected function setInaccessibleProperty($object, $propertyName, $value, $revoke = true)
    {
        $class = new \ReflectionClass($object);
        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        if ($revoke) {
            $property->setAccessible(false);
        }
    }

    /**
     * Gets an inaccessible object property.
     * @param $object
     * @param $propertyName
     * @param bool $revoke whether to make property inaccessible after getting
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getInaccessibleProperty($object, $propertyName, $revoke = true)
    {
        $class = new \ReflectionClass($object);
        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);
        if ($revoke) {
            $property->setAccessible(false);
        }

        return $result;
    }

    /**
     * Asserts that value is one of expected values.
     *
     * @param mixed $actual
     * @param array $expected
     * @param string $message
     */
    public function assertIsOneOf($actual, array $expected, $message = '')
    {
        self::assertThat($actual, new IsOneOfAssert($expected), $message);
    }

    /**
     * Creates test files structure.
     * @param string $baseDirectory base directory path.
     * @param array $items file system objects to be created in format: objectName => objectContent
     * Arrays specifies directories, other values - files.
     */
    protected function createFileStructure(array $items, string $baseDirectory = null): void
    {
        foreach ($items as $name => $content) {
            $itemName = $baseDirectory . '/' . $name;
            if (\is_array($content)) {
                if (isset($content[0], $content[1]) && $content[0] === 'symlink') {
                    symlink($baseDirectory . DIRECTORY_SEPARATOR . $content[1], $itemName);
                } else {
                    if (!mkdir($itemName, 0777, true) && !is_dir($itemName)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $itemName));
                    }
                    $this->createFileStructure($content, $itemName);
                }
            } else {
                file_put_contents($itemName, $content);
            }
        }
    }
}
