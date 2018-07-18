<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;
use yii\di\Container;
use yii\di\Instance;
use yii\helpers\VarDumper;

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
     * Returns a string representing the current version of the Yii framework.
     * @return string the version of Yii framework
     */
    public static function getVersion()
    {
        return '3.0.0-dev';
    }

    /**
     * Creates a new object using the given configuration.
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
     * $object = \Yii::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * Using [[\yii\di\Container|dependency injection container]], this method can also identify
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
     * @return object the created object
     * @throws InvalidConfigException if the configuration is invalid.
     * @see \yii\di\Container
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::get('factory')->create($type, [], $params);
        } elseif (is_array($type) && isset($type['__class'])) {
            $class = $type['__class'];
            unset($type['__class']);
            return static::get('factory')->create($class, $type, $params);
        } elseif (is_callable($type, true)) {
            return static::get('injector')->invoke($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "__class" element.');
        }

        throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
    }

    /**
     * @var LoggerInterface logger instance.
     */
    private static $_logger;

    /**
     * @return LoggerInterface message logger
     */
    public static function getLogger()
    {
        if (self::$_logger !== null) {
            return self::$_logger;
        }

        return self::$_logger = Instance::ensure(['__class' => Logger::class], LoggerInterface::class);
    }

    /**
     * Sets the logger object.
     * @param LoggerInterface|\Closure|array|null $logger the logger object or its DI compatible configuration.
     */
    public static function setLogger($logger)
    {
        if ($logger === null) {
            self::$_logger = null;
            return;
        }

        if (is_array($logger)) {
            if (!isset($logger['__class']) && is_object(self::$_logger)) {
                static::configure(self::$_logger, $logger);
                return;
            }
            $logger = array_merge(['__class' => Logger::class], $logger);
        } elseif ($logger instanceof \Closure) {
            $logger = call_user_func($logger);
        }

        self::$_logger = Instance::ensure($logger, LoggerInterface::class);
    }

    /**
     * @var ProfilerInterface profiler instance.
     * @since 3.0.0
     */
    private static $_profiler;

    /**
     * @return ProfilerInterface profiler instance.
     * @since 3.0.0
     */
    public static function getProfiler()
    {
        if (self::$_profiler !== null) {
            return self::$_profiler;
        }
        return self::$_profiler = Instance::ensure(['__class' => Profiler::class], ProfilerInterface::class);
    }

    /**
     * @param ProfilerInterface|\Closure|array|null $profiler profiler instance or its DI compatible configuration.
     * @since 3.0.0
     */
    public static function setProfiler($profiler)
    {
        if ($profiler === null) {
            self::$_profiler = null;
            return;
        }

        if (is_array($profiler)) {
            if (!isset($profiler['__class']) && is_object(self::$_profiler)) {
                static::configure(self::$_profiler, $profiler);
                return;
            }
            $profiler = array_merge(['__class' => Profiler::class], $profiler);
        } elseif ($profiler instanceof \Closure) {
            $profiler = call_user_func($profiler);
        }

        self::$_profiler = Instance::ensure($profiler, ProfilerInterface::class);
    }

    /**
     * Logs a message with category.
     * @param string $level log level.
     * @param mixed $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @since 3.0.0
     */
    public static function log($level, $message, $category = 'application')
    {
        $context = ['category' => $category];
        if (!is_string($message)) {
            if ($message instanceof \Throwable) {
                // exceptions are string-convertable, thus should be passed as it is to the logger
                // if exception instance is given to produce a stack trace, it MUST be in a key named "exception".
                $context['exception'] = $message;
            } else {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                $message = VarDumper::export($message);
            }
        }
        static::getLogger()->log($level, $message, $context);
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
        if (YII_DEBUG) {
            static::log(LogLevel::DEBUG, $message, $category);
        }
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
        static::getProfiler()->begin($token, ['category' => $category]);
    }

    /**
     * Marks the end of a code block for profiling.
     * This has to be matched with a previous call to [[beginProfile]] with the same category name.
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see beginProfile()
     */
    public static function endProfile($token, $category = 'application')
    {
        static::getProfiler()->end($token, ['category' => $category]);
    }

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\yii\i18n\I18N::translate()]].
     *
     * The translation will be conducted according to the message category and the target language will be used.
     *
     * You can add parameters to a translation message that will be substituted with the corresponding value after
     * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
     *
     * ```php
     * $username = 'Alexander';
     * echo \Yii::t('app', 'Hello, {username}!', ['username' => $username]);
     * ```
     *
     * Further formatting of message parameters is supported using the [PHP intl extensions](http://www.php.net/manual/en/intro.intl.php)
     * message formatter. See [[\yii\i18n\I18N::translate()]] for more details.
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
        if (static::$app !== null) {
            return static::$app->getI18n()->translate($category, $message, $params, $language ?: static::$app->language);
        }

        $placeholders = [];
        foreach ((array) $params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }

    /**
     * Returns the public member variables of an object.
     * This method is provided such that we can get the public member variables of an object.
     * It is different from "get_object_vars()" because the latter will return private
     * and protected variables if it is called within the object itself.
     * @param object $object the object to be handled
     * @return array the public member variables of the object
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }
}
