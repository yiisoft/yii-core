<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use yii\base\Application;
use yii\di\FactoryInterface;
use yii\exceptions\InvalidConfigException;

/**
 * BaseYii is the core helper class for the Yii framework.
 *
 * Do not use BaseYii directly. Instead, use its child class [[\Yii]] which you can replace to
 * customize methods of BaseYii.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseYii
{
    /**
     * @var ContainerInterface the dependency injection (DI) container used by [[createObject()]].
     * You may use [[ContainerInterface::set()]] to set up the needed dependencies of classes and
     * their initial property values.
     * @see createObject()
     * @see ContainerInterface
     */
    protected static $container;

    /**
     * Sets the DI container to be used to find services.
     *
     * @param ContainerInterface $container
     * @return ContainerInterface the given container
     */
    public static function setContainer(ContainerInterface $container): ContainerInterface
    {
        return static::$container = $container;
    }

    /**
     * Returns a string representing the current version of the Yii framework.
     * @return string the version of Yii framework
     */
    public static function getVersion(): string
    {
        return '3.0.0-dev';
    }

    /**
     * Creates a new object using the given configuration and constructor parameters.
     *
     * @param string|array|callable $config the object configuration.
     * @param array $params the constructor parameters.
     * @param ContainerInterface $container the container, default one will be used if not given.
     * @return object the created object.
     * @see \yii\di\Factory::create()
     */
    public static function createObject($config, array $params = [], ContainerInterface $container = null)
    {
        return static::getFactory($container)->create($config, $params);
    }

    /**
     * Resolves the specified reference into the actual object and makes sure it is of the specified type.
     *
     * @param object|string|array|\yii\di\Reference $reference an object, configuration or a reference to the desired object.
     * @param string $type the class/interface name to be checked. If null, type check will not be performed.
     * @param ContainerInterface $container the container. This will be passed to [[get()]].
     * @return object the object referenced by the Reference, or `$reference` itself if it is an object.
     * @see \yii\di\Factory::ensure()
     */
    public static function ensureObject($reference, string $type = null, ContainerInterface $container = null)
    {
        return static::getFactory($container)->ensure($reference, $type);
    }

    /**
     * Returns factory.
     *
     * @param ContainerInterface $container
     * @return FactoryInterface
     * @throws InvalidConfigException if no factory can be found
     */
    private static function getFactory(ContainerInterface $container = null): FactoryInterface
    {
        if ($container === null) {
            $container = static::$container;
        }
        if ($container === null || !$container->has('factory')) {
            throw new InvalidConfigException('No factory can be found');
        }

        return $container->get('factory');
    }

    /**
     * @return ContainerInterface|null
     */
    public static function getContainer(): ?ContainerInterface
    {
        return static::$container;
    }

    /**
     * @deprecated 3.0.0 Use DI instead.
     * @return Application|null
     */
    public static function getApp(): ?Application
    {
        return static::$container ? static::$container->get('app') : null;
    }

    /**
     * Logs given message with level and category.
     *
     * Uses `logger` service if container is available.
     * Else logs message with PHP built-in `error_log()`.
     *
     * @param string $level log level.
     * @param mixed $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @since 3.0.0
     * @see Psr\Log\LoggerInterface::log()
     */
    public static function log($level, $message, $category = 'application')
    {
        if (LogLevel::DEBUG === $level && !YII_DEBUG) {
            return;
        }

        if (static::$container !== null) {
            return static::$container->get('logger')->log($level, $message, ['category' => $category]);
        }

        error_log($message);
    }

    /**
     * Logs a debug message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     *
     * @param mixed $message the message to be logged.
     * @param string $category the category of the message.
     * @since 2.0.14
     * @see log()
     */
    public static function debug($message, $category = 'application')
    {
        static::log(LogLevel::DEBUG, $message, $category);
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     *
     * @param mixed $message the message to be logged.
     * @param string $category the category of the message.
     * @see log()
     */
    public static function error($message, $category = 'application')
    {
        static::log(LogLevel::ERROR, $message, $category);
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     *
     * @param mixed $message the message to be logged.
     * @param string $category the category of the message.
     * @see log()
     */
    public static function warning($message, $category = 'application')
    {
        static::log(LogLevel::WARNING, $message, $category);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     *
     * @param mixed $message the message to be logged.
     * @param string $category the category of the message.
     * @see log()
     */
    public static function info($message, $category = 'application')
    {
        static::log(LogLevel::INFO, $message, $category);
    }

    /**
     * Marks the beginning of a code block for profiling.
     *
     * Uses `profiler` service if container is available.
     * Else logs warning message only.
     *
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see \yii\profile\ProfilerInterface::begin()
     */
    public static function beginProfile($token, $category = 'application')
    {
        if (static::$container !== null) {
            static::$container->get('profiler')->begin($token, ['category' => $category]);
        } else {
            static::warning('Profiling not available without container');
        }
    }

    /**
     * Marks the end of a code block for profiling.
     *
     * Uses `profiler` service if container is available.
     * Else logs warning message only.
     *
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see \yii\profile\ProfilerInterface::end()
     */
    public static function endProfile($token, $category = 'application')
    {
        if (static::$container !== null) {
            static::$container->get('profiler')->end($token, ['category' => $category]);
        } else {
            static::warning('Profiling not available without container');
        }
    }

    /**
     * Translates a message to the specified language.
     *
     * Uses @see \yii\base\Application::t() if container is set.
     * Else leaves message not translated, only params are substituted.
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (static::$container !== null) {
            return static::getApp()->t($category, $message, $params, $language);
        }

        $placeholders = [];
        foreach ((array) $params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }
}
