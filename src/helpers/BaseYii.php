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
use yii\exceptions\InvalidArgumentException;
use yii\exceptions\InvalidConfigException;
use yii\i18n\Translator;

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
        return static::get('factory', $container)->create($config, $params);
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
        return static::get('factory', $container)->ensure($reference, $type);
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
     * @param ContainerInterface $container the container to get the app from.
     * @return Application|null
     */
    public static function getApp(ContainerInterface $container = null): ?Application
    {
        return static::get('app', $container, false);
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

        $logger = static::get('logger', null, false);
        if ($logger) {
            return $logger->log($level, $message, ['category' => $category]);
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
     * Uses `profiler` service.
     *
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see \yii\profile\ProfilerInterface::begin()
     */
    public static function beginProfile($token, $category = 'application'): void
    {
        static::get('profiler')->begin($token, ['category' => $category]);
    }

    /**
     * Marks the end of a code block for profiling.
     * Uses `profiler` service.
     *
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see \yii\profile\ProfilerInterface::end()
     */
    public static function endProfile($token, $category = 'application'): void
    {
        static::get('profiler')->end($token, ['category' => $category]);
    }

    /**
     * Translates a message to the specified language.
     *
     * Uses @see Application::t() if container is set.
     * Else leaves message not translated, only params are substituted.
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (static::$container !== null) {
            return static::getApp()->t($category, $message, $params, $language);
        }

        return Translator::substitute($message, $params);
    }

    /**
     * Translates a path alias into an actual path.
     *
     * Uses @see \yii\base\Aliases::get() if container is set.
     * Else throws exception.
     *
     * @deprecated 3.0.0 Use [[yii\base\Application::get()|Application::get()]] instead
     * @param string $alias the alias to be translated.
     * @param bool $throwException whether to throw an exception if the given alias is invalid.
     * If this is false and an invalid alias is given, false will be returned by this method.
     * @return string|bool the path corresponding to the alias, false if the root alias is not previously registered.
     * @throws InvalidArgumentException if the alias is invalid while $throwException is true.
     * @throws InvalidConfigException if application is not available.
     * @see setAlias()
     */
    public static function getAlias(string $alias, bool $throwException = true)
    {
        return static::get('aliases')->get($alias, $throwException);
    }

    /**
     * Registers a path alias.
     *
     * Uses @see \yii\base\Aliases::setAlias() if container is set.
     * Else throws exception.
     *
     * @deprecated 3.0.0 Use [[yii\base\Application::setAlias()|Application::setAlias()]] instead
     * @param string $alias the alias name (e.g. "@yii"). It must start with a '@' character.
     * It may contain the forward slash '/' which serves as boundary character when performing
     * alias translation by [[get()]].
     * @param string $path the path corresponding to the alias. If this is null, the alias will
     * be removed. Trailing '/' and '\' characters will be trimmed.
     * @throws InvalidArgumentException if $path is an invalid alias.
     * @throws InvalidConfigException if application is not available.
     * @see getAlias()
     */
    public static function setAlias(string $alias, string $path)
    {
        return static::get('aliases')->set($alias, $path);
    }

    /**
     * Returns current locale if set or default.
     * @return string
     */
    public static function getLocaleString(string $default = 'en-US'): string
    {
        $i18n = static::get('i18n', null, false);

        return $i18n ? (string)$i18n->getLocale() : $default;
    }

    /**
     * Returns current source locale if set or default.
     * @return string
     */
    public static function getSourceLocaleString(string $default = 'en-US'): string
    {
        $view = static::get('view', null, false);

        return $view ? (string)$view->getSourceLocale() : $default;
    }

    /**
     * Returns current timezone if set or default.
     * @return string
     */
    public static function getTimeZone(string $default = 'UTC'): string
    {
        $i18n = static::get('i18n', null, false);

        return $i18n ? (string)$i18n->getTimeZone() : $default;
    }

    /**
     * Returns current application encoding.
     * @param ContainerInterface|null $container
     * @return string
     */
    public static function getEncoding(ContainerInterface $container = null): string
    {
        $i18n = static::get('i18n', $container, false);

        return $i18n ? $i18n->getEncoding() : mb_internal_encoding();
    }

    /**
     * Returns service from container.
     * @param string $name service or class/interface name.
     * @param ContainerInterface $container DI container, default one will be used if not given.
     * @param bool $throwException whether to throw an exception or return null.
     * @return object service object.
     */
    public static function get(string $name, ContainerInterface $container = null, bool $throwException = true)
    {
        if ($container === null) {
            $container = static::$container;
        }

        if ($container !== null && $container->has($name)) {
            return static::$container->get($name);
        }

        if ($throwException) {
            throw new InvalidConfigException("No '$name' service can be found");
        } else {
            return null;
        }
    }
}
