<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yiisoft\Db\Connection;
use yii\exceptions\ExitException;
use yii\exceptions\InvalidConfigException;
use yii\exceptions\InvalidArgumentException;
use yii\i18n\I18N;
use yii\i18n\Locale;
use yii\web\Session;
use yii\web\User;
use yii\profile\ProfilerInterface;

/**
 * Application is the base class for all application classes.
 *
 * For more details and usage information on Application, see the [guide article on applications](guide:structure-applications).
 *
 * @property \yii\web\AssetManager $assetManager The asset manager application component. This property is
 * read-only.
 * @property \Yiisoft\Rbac\ManagerInterface $authManager The auth manager application component. Null is returned
 * if auth manager is not configured. This property is read-only.
 * @property string $basePath The root directory of the application.
 * @property \Yiisoft\Cache\CacheInterface $cache The cache application component. Null if the component is not
 * enabled. This property is read-only.
 * @property array $container Values given in terms of name-value pairs. This property is write-only.
 * @property Connection $db The database connection. This property is read-only.
 * @property \yii\web\ErrorHandler|\Yiisoft\Yii\Console\ErrorHandler $errorHandler The error handler application
 * component. This property is read-only.
 * @property \yii\i18n\Formatter $formatter The formatter application component. This property is read-only.
 * @property \yii\i18n\I18N $i18n The internationalization application component. This property is read-only.
 * @property \PSR\Log\LoggerInterface $logger The logger. This property is read-only.
 * @property \yii\profile\ProfilerInterface $profiler The profiler. This property is read-only.
 * @property \yii\mail\MailerInterface $mailer The mailer application component. This property is read-only.
 * @property \yii\web\Request|\Yiisoft\Yii\Console\Request $request The request component. This property is read-only.
 * @property \yii\web\Response|\Yiisoft\Yii\Console\Response $response The response component. This property is
 * read-only.
 * @property string $runtimePath The directory that stores runtime files. Defaults to the "runtime"
 * subdirectory under [[basePath]].
 * @property \yii\base\Security $security The security application component. This property is read-only.
 * @property string $timeZone The time zone used by this application.
 * @property string $uniqueId The unique ID of the module. This property is read-only.
 * @property \yii\web\UrlManager $urlManager The URL manager for this application. This property is read-only.
 * [[basePath]].
 * @property View|\yii\web\View $view The view application component that is used to render various view
 * files. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Application extends Module
{
    /**
     * Application state used by [[state]]: application just started.
     */
    const STATE_BEGIN = 0;
    /**
     * Application state used by [[state]]: application is triggering [[RequestEvent::BEFORE]].
     */
    const STATE_BEFORE_REQUEST = 2;
    /**
     * Application state used by [[state]]: application is handling the request.
     */
    const STATE_HANDLING_REQUEST = 3;
    /**
     * Application state used by [[state]]: application is triggering [[RequestEvent::AFTER]].
     */
    const STATE_AFTER_REQUEST = 4;
    /**
     * Application state used by [[state]]: application is about to send response.
     */
    const STATE_SENDING_RESPONSE = 5;
    /**
     * Application state used by [[state]]: application has ended.
     */
    const STATE_END = 6;

    /**
     * @var string the namespace that controller classes are located in.
     * This namespace will be used to load controller classes by prepending it to the controller class name.
     * The default namespace is `app\controllers`.
     *
     * Please refer to the [guide about class autoloading](guide:concept-autoloading.md) for more details.
     */
    public $controllerNamespace = 'app\\controllers';
    /**
     * @var string the application name.
     */
    public $name = 'My Application';
    /**
     * @var Controller the currently active controller instance
     */
    public $controller;
    /**
     * @var string|bool the layout that should be applied for views in this application. Defaults to 'main'.
     * If this is false, layout will be disabled.
     */
    public $layout = 'main';
    /**
     * @var string the requested route
     */
    public $requestedRoute;
    /**
     * @var Action the requested Action. If null, it means the request cannot be resolved into an action.
     */
    public $requestedAction;
    /**
     * @var array the parameters supplied to the requested action.
     */
    public $requestedParams;
    /**
     * @var array list of installed Yii extensions. Each array element represents a single extension
     * with the following structure:
     *
     * ```php
     * [
     *     'name' => 'extension name',
     *     'version' => 'version number',
     *     'bootstrap' => 'BootstrapClassName',  // optional, may also be a configuration array
     *     'alias' => [
     *         '@alias1' => 'to/path1',
     *         '@alias2' => 'to/path2',
     *     ],
     * ]
     * ```
     *
     * The "bootstrap" class listed above will be instantiated during the application
     * [[bootstrap()|bootstrapping process]]. If the class implements [[BootstrapInterface]],
     * its [[BootstrapInterface::bootstrap()|bootstrap()]] method will be also be called.
     *
     * If not set explicitly in the application config, this property will be populated with the contents of
     * `@vendor/yiisoft/extensions.php`.
     */
    public $extensions;
    /**
     * @var array list of components that should be run during the application [[bootstrap()|bootstrapping process]].
     *
     * Each component may be specified in one of the following formats:
     *
     * - an application component ID as specified via [[components]].
     * - a module ID as specified via [[modules]].
     * - a class name.
     * - a configuration array.
     * - a Closure
     *
     * During the bootstrapping process, each component will be instantiated. If the component class
     * implements [[BootstrapInterface]], its [[BootstrapInterface::bootstrap()|bootstrap()]] method
     * will be also be called.
     */
    public $bootstrap = [];
    /**
     * @var int the current application state during a request handling life cycle.
     * This property is managed by the application. Do not modify this property.
     */
    public $state;
    /**
     * @var array list of loaded modules indexed by their class names.
     */
    public $loadedModules = [];

    protected $request;

    protected $response;

    /**
     * Constructor.
     * @param ContainerInterface $object that must contain both [[id]] and [[basePath]].
     * Note that the configuration must contain both [[id]] and [[basePath]].
     * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->app = $this;

        $this->_container = $container;

        $this->state = self::STATE_BEGIN;

        $this->bootstrap();
    }

    /**
     * Initializes extensions and executes bootstrap components.
     * This method is called by [[init()]] after the application has been fully configured.
     * If you override this method, make sure you also call the parent implementation.
     */
    public function bootstrap(): void
    {
        foreach ($this->bootstrap as $mixed) {
            $component = null;
            if ($mixed instanceof \Closure) {
                $this->debug('Bootstrap with Closure', __METHOD__);
                if (empty($component = call_user_func($mixed, $this))) {
                    continue;
                }
            } elseif (is_string($mixed)) {
                if ($this->has($mixed)) {
                    $component = $this->get($mixed);
                } elseif ($this->hasModule($mixed)) {
                    $component = $this->getModule($mixed);
                } elseif (strpos($mixed, '\\') === false) {
                    throw new InvalidConfigException("Unknown bootstrapping component ID: $mixed");
                }
            }

            if (!isset($component)) {
                $component = $this->createObject($mixed);
            }

            if ($component instanceof BootstrapInterface) {
                $this->debug('Bootstrap with ' . get_class($component) . '::bootstrap()', __METHOD__);
                $component->bootstrap($this);
            } else {
                $this->debug('Bootstrap with ' . get_class($component), __METHOD__);
            }
        }
    }

    public function getApp(): self
    {
        return $this;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->_container;
    }

    public function getRequest()
    {
        if ($this->request === null) {
            $this->request = $this->get('request');
        }

        return $this->request;
    }

    public function getResponse()
    {
        if ($this->response === null) {
            $this->response = $this->get('response');
        }

        return $this->response;
    }

    /**
     * Creates a new object using the given configuration and constructor parameters.
     *
     * @param string|array|callable $config the object configuration.
     * @param array $params the constructor parameters.
     * @return object the created object.
     * @see \Yiisoft\Factory\Factory::create()
     */
    public function createObject($config, array $params = [])
    {
        return $this->get('factory')->create($config, $params);
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
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current language will be used.
     * @return string the translated message.
     */
    public function t(string $category, string $message, array $params = [], string $language = null)
    {
        return $this->get('i18n')->translate($category, $message, $params, $language);
    }

    /**
     * Logs a debug message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     * @param mixed $message the message to be logged.
     * @param string $category the category of the message.
     */
    public function debug($message, string $category = 'application'): void
    {
        $this->log(LogLevel::DEBUG, $message, $category);
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string|array $message the message to be logged.
     * @param string $category the category of the message.
     */
    public function error($message, string $category = 'application'): void
    {
        $this->log(LogLevel::ERROR, $message, $category);
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string|array $message the message to be logged.
     * @param string $category the category of the message.
     */
    public function warning($message, string $category = 'application'): void
    {
        $this->log(LogLevel::WARNING, $message, $category);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string|array $message the message to be logged.
     * @param string $category the category of the message.
     */
    public function info($message, string $category = 'application'): void
    {
        $this->log(LogLevel::INFO, $message, $category);
    }

    /**
     * Logs given message through `logger` service.
     *
     * @param string $level log level.
     * @param mixed $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @since 3.0.0
     */
    public function log(string $level, $message, $category = 'application'): void
    {
        $this->getLogger()->log($level, $message, ['category' => $category]);
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->get('logger');
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
    public function beginProfile(string $token, string $category = 'application'): void
    {
        $this->getProfiler()->begin($token, ['category' => $category]);
    }

    /**
     * Marks the end of a code block for profiling.
     *
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see beginProfile()
     */
    public function endProfile(string $token, string $category = 'application'): void
    {
        $this->getProfiler()->end($token, ['category' => $category]);
    }

    /**
     * @return ProfilerInterface
     */
    public function getProfiler(): ProfilerInterface
    {
        return $this->get('profiler');
    }

    /**
     * @return ErrorHandler
     */
    public function getErrorHandler(): ErrorHandler
    {
        return $this->get('errorHandler');
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->get('view');
    }

    /**
     * @return View
     */
    public function getFormatter()
    {
        return $this->get('formatter');
    }

    /**
     * @return Security
     */
    public function getSecurity()
    {
        return $this->get('security');
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * @deprecated 3.0 use DI instead.
     * @return I18N
     */
    public function getI18n()
    {
        return $this->get('i18n');
    }

    /**
     * Returns the database connection component.
     * @return Connection the database connection.
     */
    public function getDb()
    {
        return $this->get('db');
    }

    /**
     * Returns an ID that uniquely identifies this module among all modules within the current application.
     * Since this is an application instance, it will always return an empty string.
     * @return string the unique ID of the module.
     */
    public function getUniqueId()
    {
        return '';
    }

    /**
     * Sets the root directory of the application and the @app alias.
     * This method can only be invoked at the beginning of the constructor.
     * @param string $path the root directory of the application.
     * @property string the root directory of the application.
     * @throws InvalidArgumentException if the directory does not exist.
     */
    public function setBasePath($path)
    {
        parent::setBasePath($path);
        $this->setAlias('@app', $this->getBasePath());
        if (empty($this->getAlias('@root', false))) {
            $this->setAlias('@root', dirname(__DIR__, 5));
        }
    }

    /**
     * Runs the application.
     * This is the main entrance of an application.
     * @return int the exit status (0 means normal, non-zero values mean abnormal)
     */
    public function run()
    {
        if (YII_ENABLE_ERROR_HANDLER) {
            $this->get('errorHandler')->register();
        }

        try {
            $this->state = self::STATE_BEFORE_REQUEST;
            $this->trigger(RequestEvent::BEFORE);

            $this->state = self::STATE_HANDLING_REQUEST;
            $this->response = $this->handleRequest($this->getRequest());

            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(RequestEvent::AFTER);

            $this->state = self::STATE_SENDING_RESPONSE;
            $this->response->send();

            $this->state = self::STATE_END;

            return $this->response->exitStatus;
        } catch (ExitException $e) {
            $this->end($e->statusCode, $this->response ?? null);
            return $e->statusCode;
        }
    }

    /**
     * Handles the specified request.
     *
     * This method should return an instance of [[Response]] or its child class
     * which represents the handling result of the request.
     *
     * @param Request $request the request to be handled
     * @return Response the resulting response
     */
    abstract public function handleRequest($request);

    private $_runtimePath;

    /**
     * TODO: remove completely, use alias instead.
     * Returns the directory that stores runtime files.
     * @return string the directory that stores runtime files.
     * Defaults to the "runtime" subdirectory under [[basePath]].
     */
    public function getRuntimePath()
    {
        if ($this->_runtimePath === null) {
            $this->_runtimePath = $this->getAlias('@runtime');
        }

        return $this->_runtimePath;
    }

    /**
     * Sets the directory that stores runtime files.
     * @param string $path the directory that stores runtime files.
     */
    public function setRuntimePath($path)
    {
        $this->_runtimePath = $this->getAlias($path);
        $this->setAlias('@runtime', $this->_runtimePath);
    }


    /**
     * Returns the I18N time zone.
     * @return string the time zone.
     */
    public function getTimeZone(): string
    {
        return $this->get('i18n')->getTimeZone();
    }

    /**
     * Sets the I18N time zone.
     * @param string $timezone the time zone.
     * @return self
     */
    public function setTimeZone(string $timezone): self
    {
        $this->get('i18n')->setTimeZone($timezone);

        return $this;
    }

    /**
     * @param string
     * @return self
     */
    public function setEncoding(string $encoding): self
    {
        $this->get('i18n')->setEncoding($encoding);

        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->get('i18n')->getEncoding();
    }

    /**
     * @param Locale|string
     * @return self
     */
    public function setLocale($locale): self
    {
        $this->get('i18n')->setLocale($locale);

        return $this;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->get('i18n')->getLocale();
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getLocale()->getLanguage();
    }

    /**
     * Terminates the application.
     * This method replaces the `exit()` function by ensuring the application life cycle is completed
     * before terminating the application.
     * @param int $status the exit status (value 0 means normal exit while other values mean abnormal exit).
     * @param Response $response the response to be sent. If not set, the default application [[response]] component will be used.
     * @throws ExitException if the application is in testing mode
     */
    public function end($status = 0, $response = null)
    {
        if ($this->state === self::STATE_BEFORE_REQUEST || $this->state === self::STATE_HANDLING_REQUEST) {
            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(RequestEvent::AFTER);
        }

        if ($this->state !== self::STATE_SENDING_RESPONSE && $this->state !== self::STATE_END) {
            $this->state = self::STATE_END;
            $this->response = $response ?: $this->getResponse();
            $this->response->send();
        }

        if (YII_ENV_TEST) {
            throw new ExitException($status);
        }

        exit($status);
    }

    /**
     * Translates a path alias into an actual path.
     * @param string $alias the alias to be translated.
     * @param bool $throwException whether to throw an exception if the given alias is invalid.
     * @return string|bool the path corresponding to the alias, false if the root alias is not previously registered.
     * @throws InvalidArgumentException if the alias is invalid while $throwException is true.
     * @see Aliases::setAlias()
     */
    public function getAlias($alias, $throwException = true)
    {
        return $this->get('aliases')->get($alias, $throwException);
    }

    /**
     * Returns the root alias part of a given alias.
     * @param string $alias the alias
     * @return string|bool the root alias, or false if no root alias is found
     * @see Aliases::getRoot()
     */
    public function getRootAlias($alias)
    {
        return $this->get('aliases')->getRoot($alias);
    }

    /**
     * Registers a path alias.
     * @param string $alias the alias name (e.g. "@yii"). It must start with a '@' character.
     * @param string $path the path corresponding to the alias. If this is null, the alias will be removed.
     * @throws InvalidArgumentException if $path is an invalid alias.
     * @see Aliases::get()
     */
    public function setAlias($alias, $path)
    {
        return $this->get('aliases')->set($alias, $path);
    }
}
