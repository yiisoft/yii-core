<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Psr\Log\LogLevel;
use yii\base\Application;
use yii\exceptions\InvalidConfigException;
use yii\di\Container;
use yii\di\Reference;

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
     * @var Container the dependency injection (DI) container used by [[createObject()]].
     * You may use [[Container::set()]] to set up the needed dependencies of classes and
     * their initial property values.
     * @see createObject()
     * @see Container
     */
    protected static $container;

    /**
     * Sets default container to be used where needed.
     *
     * @param Container $container
     */
    public static function setContainer(Container $container): void
    {
        static::$container = $container;
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
     * Creates a new object using the given configuration and constructor arguments.
     *
     * You may view this method as an enhanced version of the `new` operator.
     * The method supports creating an object based on a class name, a configuration array or
     * an anonymous function.
     *
     * Below are some usage examples:
     *
     * ```php
     * // create an object using a class name
     * $object = Yii::createObject(\yii\db\Connection::class);
     *
     * // create an object using a configuration array
     * $object = Yii::createObject([
     *     '__class' => \yii\db\Connection::class,
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // create an object with two constructor parameters
     * $object = Yii::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * Using [[Container|dependency injection container]], this method can also identify
     * dependent objects, instantiate them and inject them into the newly created object.
     *
     * @param string|array|callable $type the object type. This can be specified in one of the following forms:
     *
     * - a string: representing the class name of the object to be created
     * - a configuration array: the array must contain a `class` element which is treated as the object class,
     *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
     * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
     *   The callable should return a new instance of the object being created.
     *
     * @param array $params the constructor parameters
     * @param Container $container the container, default one will be used if not given
     * @return object the created object
     * @throws InvalidConfigException if the configuration is invalid.
     * @see \yii\di\Container
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::get('factory')->create($type, [], $params);
        }

        if (is_array($type) && isset($type['__class'])) {
            $class = $type['__class'];
            unset($type['__class']);
            return static::get('factory')->create($class, $type, $params);
        }

        if (is_callable($type, true)) {
            return static::get('injector')->invoke($type, $params);
        }

        if (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "__class" element.');
        }

        throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
    }

    /**
     * Resolves the specified reference into the actual object and makes sure it is of the specified type.
     *
     * The reference may be specified as a string or an Reference object. If the former,
     * it will be treated as a component ID, a class/interface name or an alias, depending on the container type.
     *
     * If you do not specify a container, the method will first try `Yii::getApp()` followed by `Yii::$container`.
     *
     * For example,
     *
     * ```php
     * use yii\db\Connection;
     *
     * // returns Yii::getApp()->db
     * $db = Yii::instanceOf('db', Connection::class);
     * // returns an instance of Connection using the given configuration
     * $db = Yii::instanceOf(['dsn' => 'sqlite:path/to/my.db'], Connection::class);
     * ```
     *
     * @param object|string|array|static $reference an object or a reference to the desired object.
     * You may specify a reference in terms of a component ID or an Reference object.
     * Starting from version 2.0.2, you may also pass in a configuration array for creating the object.
     * If the "class" value is not specified in the configuration array, it will use the value of `$type`.
     * @param string $type the class/interface name to be checked. If null, type check will not be performed.
     * @param Container $container the container. This will be passed to [[get()]].
     * @return object the object referenced by the Reference, or `$reference` itself if it is an object.
     * @throws InvalidConfigException if the reference is invalid
     */
    public static function instanceOf($reference, $type = null, $container = null)
    {
        if (is_array($reference)) {
            $class = $type;
            if (isset($reference['__class'])) {
                $class = $reference['__class'];
                unset($reference['__class']);
            }

            if ($container === null) {
                $container = static::$container;
            }
            $component = $container->get($class, [], $reference);
            if ($type === null || $component instanceof $type) {
                return $component;
            }

            throw new InvalidConfigException('Invalid data type: ' . $class . '. ' . $type . ' is expected.');
        }

        if (empty($reference)) {
            throw new InvalidConfigException('The required component is not specified.');
        }

        if (is_string($reference)) {
            $reference = new Reference($reference);
        } elseif ($type === null || $reference instanceof $type) {
            return $reference;
        }

        if ($reference instanceof Reference) {
            try {
                $container->get($reference);
            } catch (\ReflectionException $e) {
                throw new InvalidConfigException('Failed to instantiate component or class "' . $reference->id . '".', 0, $e);
            }
            if ($type === null || $component instanceof $type) {
                return $component;
            }

            throw new InvalidConfigException('"' . $reference->id . '" refers to a ' . get_class($component) . " component. $type is expected.");
        }

        $valueType = is_object($reference) ? get_class($reference) : gettype($reference);
        throw new InvalidConfigException("Invalid data type: $valueType. $type is expected.");
    }

    /**
     * @return Container|null
     */
    public static function getContainer()
    {
        return static::$container;
    }

    /**
     * @deprecated 3.0.0 Use DI instead.
     * @return Application|null
     */
    public static function getApp()
    {
        return static::$container ? static::$container->get('app') : null;
    }

    private static function getFactory()
    {
        return static::get('factory');
    }

    private static function get(string $name)
    {
        return static::$container->get($name);
    }

    /**
     * Logs given message with level and category.
     *
     * Uses `logger` service if container is available.
     * Else logs message with PHP built-in `error_log`.
     *
     * @param string $level log level.
     * @param mixed $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @since 3.0.0
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
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @since 2.0.14
     */
    public static function debug($message, $category = 'application')
    {
        static::log(LogLevel::DEBUG, $message, $category);
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public static function error($message, $category = 'application')
    {
        static::log(LogLevel::ERROR, $message, $category);
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public static function warning($message, $category = 'application')
    {
        static::log(LogLevel::WARNING, $message, $category);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
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
     * This has to be matched with a call to [[endProfile]] with the same category name.
     * The begin- and end- calls must also be properly nested. For example,
     *
     * ```php
     * \Yii::beginProfile('block1');
     * // some code to be profiled
     *     \Yii::beginProfile('block2');
     *     // some other code to be profiled
     *     \Yii::endProfile('block2');
     * \Yii::endProfile('block1');
     * ```
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see endProfile()
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
     * @see beginProfile()
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
