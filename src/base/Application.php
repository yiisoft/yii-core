<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Application is the base class for all application classes.
 *
 * For more details and usage information on Application, see the [guide article on applications](guide:structure-applications).
 *
 * @property \yii\web\AssetManager $assetManager The asset manager application component. This property is
 * read-only.
 * @property \yii\rbac\ManagerInterface $authManager The auth manager application component. Null is returned
 * if auth manager is not configured. This property is read-only.
 * @property string $basePath The root directory of the application.
 * @property \yii\caching\CacheInterface $cache The cache application component. Null if the component is not
 * enabled. This property is read-only.
 * @property array $container Values given in terms of name-value pairs. This property is write-only.
 * @property \yii\db\Connection $db The database connection. This property is read-only.
 * @property \yii\web\ErrorHandler|\yii\console\ErrorHandler $errorHandler The error handler application
 * component. This property is read-only.
 * @property \yii\i18n\Formatter $formatter The formatter application component. This property is read-only.
 * @property \yii\i18n\I18N $i18n The internationalization application component. This property is read-only.
 * @property \psr\log\LoggerInterface $logger The logger.
 * @property \yii\profile\ProfilerInterface $profiler The profiler.
 * @property \yii\mail\MailerInterface $mailer The mailer application component. This property is read-only.
 * @property \yii\web\Request|\yii\console\Request $request The request component. This property is read-only.
 * @property \yii\web\Response|\yii\console\Response $response The response component. This property is
 * read-only.
 * @property string $runtimePath The directory that stores runtime files. Defaults to the "runtime"
 * subdirectory under [[basePath]].
 * @property \yii\base\Security $security The security application component. This property is read-only.
 * @property string $timeZone The time zone used by this application.
 * @property string $uniqueId The unique ID of the module. This property is read-only.
 * @property \yii\web\UrlManager $urlManager The URL manager for this application. This property is read-only.
 * @property string $vendorPath The directory that stores vendor files. Defaults to "vendor" directory under
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
     * @event Event an event raised before the application starts to handle a request.
     */
    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    /**
     * @event Event an event raised after the application successfully handles a request (before the response is sent out).
     */
    const EVENT_AFTER_REQUEST = 'afterRequest';
    /**
     * Application state used by [[state]]: application just started.
     */
    const STATE_BEGIN = 0;
    /**
     * Application state used by [[state]]: application is initializing.
     */
    const STATE_INIT = 1;
    /**
     * Application state used by [[state]]: application is triggering [[EVENT_BEFORE_REQUEST]].
     */
    const STATE_BEFORE_REQUEST = 2;
    /**
     * Application state used by [[state]]: application is handling the request.
     */
    const STATE_HANDLING_REQUEST = 3;
    /**
     * Application state used by [[state]]: application is triggering [[EVENT_AFTER_REQUEST]]..
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
     * @var string the charset currently used for the application.
     */
    public $charset = 'UTF-8';
    /**
     * @var string the language that is meant to be used for end users. It is recommended that you
     * use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
     * for English, while `en-US` stands for English (United States).
     * @see sourceLanguage
     */
    public $language = 'en-US';
    /**
     * @var string the language that the application is written in. This mainly refers to
     * the language that the messages and view files are written in.
     * @see language
     */
    public $sourceLanguage = 'en-US';
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


    /**
     * Constructor.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     * Note that the configuration must contain both [[id]] and [[basePath]].
     * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
     */
    public function __construct($config = [])
    {
        Yii::$app = $this;
        static::setInstance($this);

        $this->state = self::STATE_BEGIN;

        $this->preInit($config);

        $this->registerErrorHandler($config);

        Component::__construct($config);
    }

    /**
     * Pre-initializes the application.
     * This method is called at the beginning of the application constructor.
     * It initializes several important application properties.
     * If you override this method, please make sure you call the parent implementation.
     * @param array $config the application configuration
     * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
     */
    public function preInit(&$config)
    {
        if (!isset($config['id'])) {
            throw new InvalidConfigException('The "id" configuration for the Application is required.');
        }
        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            throw new InvalidConfigException('The "basePath" configuration for the Application is required.');
        }

        if (isset($config['vendorPath'])) {
            $this->setVendorPath($config['vendorPath']);
            unset($config['vendorPath']);
        } else {
            // set "@vendor"
            $this->getVendorPath();
        }
        if (isset($config['runtimePath'])) {
            $this->setRuntimePath($config['runtimePath']);
            unset($config['runtimePath']);
        } else {
            // set "@runtime"
            $this->getRuntimePath();
        }

        if (isset($config['timeZone'])) {
            $this->setTimeZone($config['timeZone']);
            unset($config['timeZone']);
        }

        if (isset($config['container'])) {
            $this->setContainer($config['container']);
            unset($config['container']);
        }

        if (isset($config['logger'])) {
            $this->setLogger($config['logger']);
            unset($config['logger']);
        }

        if (isset($config['profiler'])) {
            $this->setProfiler($config['profiler']);
            unset($config['profiler']);
        }

        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['__class'])) {
                $config['components'][$id]['__class'] = $component['__class'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->state = self::STATE_INIT;
        $this->bootstrap();
    }

    /**
     * Initializes extensions and executes bootstrap components.
     * This method is called by [[init()]] after the application has been fully configured.
     * If you override this method, make sure you also call the parent implementation.
     */
    protected function bootstrap()
    {
        if ($this->extensions === null) {
            $file = Yii::getAlias('@vendor/yiisoft/extensions.php');
            $this->extensions = is_file($file) ? include $file : [];
        }
        foreach ($this->extensions as $extension) {
            if (!empty($extension['alias'])) {
                foreach ($extension['alias'] as $name => $path) {
                    Yii::setAlias($name, $path);
                }
            }
            if (isset($extension['bootstrap'])) {
                $component = Yii::createObject($extension['bootstrap']);
                if ($component instanceof BootstrapInterface) {
                    Yii::debug('Bootstrap with ' . get_class($component) . '::bootstrap()', __METHOD__);
                    $component->bootstrap($this);
                } else {
                    Yii::debug('Bootstrap with ' . get_class($component), __METHOD__);
                }
            }
        }

        foreach ($this->bootstrap as $mixed) {
            $component = null;
            if ($mixed instanceof \Closure) {
                Yii::debug('Bootstrap with Closure', __METHOD__);
                if (!$component = call_user_func($mixed, $this)) {
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
                $component = Yii::createObject($mixed);
            }

            if ($component instanceof BootstrapInterface) {
                Yii::debug('Bootstrap with ' . get_class($component) . '::bootstrap()', __METHOD__);
                $component->bootstrap($this);
            } else {
                Yii::debug('Bootstrap with ' . get_class($component), __METHOD__);
            }
        }
    }

    /**
     * Registers the errorHandler component as a PHP error handler.
     * @param array $config application config
     */
    protected function registerErrorHandler(&$config)
    {
        if (YII_ENABLE_ERROR_HANDLER) {
            if (!isset($config['components']['errorHandler']['__class'])) {
                echo "Error: no errorHandler component is configured.\n";
                exit(1);
            }
            $this->set('errorHandler', $config['components']['errorHandler']);
            unset($config['components']['errorHandler']);
            $this->getErrorHandler()->register();
        }
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
        Yii::setAlias('@app', $this->getBasePath());
    }

    /**
     * Runs the application.
     * This is the main entrance of an application.
     * @return int the exit status (0 means normal, non-zero values mean abnormal)
     */
    public function run()
    {
        try {
            $this->state = self::STATE_BEFORE_REQUEST;
            $this->trigger(self::EVENT_BEFORE_REQUEST);

            $this->state = self::STATE_HANDLING_REQUEST;
            $response = $this->handleRequest($this->getRequest());

            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(self::EVENT_AFTER_REQUEST);

            $this->state = self::STATE_SENDING_RESPONSE;
            $response->send();

            $this->state = self::STATE_END;

            return $response->exitStatus;
        } catch (ExitException $e) {
            $this->end($e->statusCode, $response ?? null);
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
     * Returns the directory that stores runtime files.
     * @return string the directory that stores runtime files.
     * Defaults to the "runtime" subdirectory under [[basePath]].
     */
    public function getRuntimePath()
    {
        if ($this->_runtimePath === null) {
            $this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
        }

        return $this->_runtimePath;
    }

    /**
     * Sets the directory that stores runtime files.
     * @param string $path the directory that stores runtime files.
     */
    public function setRuntimePath($path)
    {
        $this->_runtimePath = Yii::getAlias($path);
        Yii::setAlias('@runtime', $this->_runtimePath);
    }

    private $_vendorPath;

    /**
     * Returns the directory that stores vendor files.
     * @return string the directory that stores vendor files.
     * Defaults to "vendor" directory under [[basePath]].
     */
    public function getVendorPath()
    {
        if ($this->_vendorPath === null) {
            $this->setVendorPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     * @param string $path the directory that stores vendor files.
     */
    public function setVendorPath($path)
    {
        $this->_vendorPath = Yii::getAlias($path);
        Yii::setAlias('@vendor', $this->_vendorPath);
        Yii::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower');
        Yii::setAlias('@npm', $this->_vendorPath . DIRECTORY_SEPARATOR . 'npm');
    }

    /**
     * Returns the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_get().
     * If time zone is not configured in php.ini or application config,
     * it will be set to UTC by default.
     * @return string the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-get.php
     */
    public function getTimeZone()
    {
        return date_default_timezone_get();
    }

    /**
     * Sets the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * @param string $value the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     */
    public function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }

    /**
     * Returns the database connection component.
     * @return \yii\db\Connection the database connection.
     */
    public function getDb()
    {
        return $this->get('db');
    }

    /**
     * Sets up or configure the logger instance.
     * @param \psr\log\LoggerInterface|\Closure|array|null $logger the logger object or its DI compatible configuration.
     * @since 3.0.0
     */
    public function setLogger($logger)
    {
        Yii::setLogger($logger);
    }

    /**
     * Returns the logger instance.
     * @return \psr\log\LoggerInterface the logger instance.
     * @since 3.0.0
     */
    public function getLogger()
    {
        return Yii::getLogger();
    }

    /**
     * Sets up or configure the profiler instance.
     * @param \yii\profile\ProfilerInterface|\Closure|array|null $profiler the profiler object or its DI compatible configuration.
     * @since 3.0.0
     */
    public function setProfiler($profiler)
    {
        Yii::setProfiler($profiler);
    }

    /**
     * Returns the profiler instance.
     * @return \yii\profile\ProfilerInterface profiler instance.
     * @since 3.0.0
     */
    public function getProfiler()
    {
        return Yii::getProfiler();
    }

    /**
     * Returns the error handler component.
     * @return \yii\web\ErrorHandler|\yii\console\ErrorHandler the error handler application component.
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * Returns the cache component.
     * @return \yii\caching\CacheInterface the cache application component. Null if the component is not enabled.
     */
    public function getCache()
    {
        return $this->get('cache', false);
    }

    /**
     * Returns the formatter component.
     * @return \yii\i18n\Formatter the formatter application component.
     */
    public function getFormatter()
    {
        return $this->get('formatter');
    }

    /**
     * Returns the request component.
     * @return \yii\web\Request|\yii\console\Request the request component.
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Returns the response component.
     * @return \yii\web\Response|\yii\console\Response the response component.
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * Returns the view object.
     * @return View|\yii\web\View the view application component that is used to render various view files.
     */
    public function getView()
    {
        return $this->get('view');
    }

    protected $aliases = [];

    /**
     * Translates a path alias into an actual path.
     *
     * The translation is done according to the following procedure:
     *
     * 1. If the given alias does not start with '@', it is returned back without change;
     * 2. Otherwise, look for the longest registered alias that matches the beginning part
     *    of the given alias. If it exists, replace the matching part of the given alias with
     *    the corresponding registered path.
     * 3. Throw an exception or return false, depending on the `$throwException` parameter.
     *
     * For example, by default '@yii' is registered as the alias to the Yii framework directory,
     * say '/path/to/yii'. The alias '@yii/web' would then be translated into '/path/to/yii/web'.
     *
     * If you have registered two aliases '@foo' and '@foo/bar'. Then translating '@foo/bar/config'
     * would replace the part '@foo/bar' (instead of '@foo') with the corresponding registered path.
     * This is because the longest alias takes precedence.
     *
     * However, if the alias to be translated is '@foo/barbar/config', then '@foo' will be replaced
     * instead of '@foo/bar', because '/' serves as the boundary character.
     *
     * Note, this method does not check if the returned path exists or not.
     *
     * See the [guide article on aliases](guide:concept-aliases) for more information.
     *
     * @param string $alias the alias to be translated.
     * @param bool $throwException whether to throw an exception if the given alias is invalid.
     * If this is false and an invalid alias is given, false will be returned by this method.
     * @return string|bool the path corresponding to the alias, false if the root alias is not previously registered.
     * @throws InvalidArgumentException if the alias is invalid while $throwException is true.
     * @see setAlias()
     */
    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }

        $result = static::findAlias($alias);

        if (is_array($result)) {
            return $result['path'];
        }

        if ($throwException) {
            throw new InvalidArgumentException("Invalid path alias: $alias");
        }

        return false;
    }

    /**
     * Returns the root alias part of a given alias.
     * A root alias is an alias that has been registered via [[setAlias()]] previously.
     * If a given alias matches multiple root aliases, the longest one will be returned.
     * @param string $alias the alias
     * @return string|bool the root alias, or false if no root alias is found
     */
    public static function getRootAlias($alias)
    {
        $result = static::findAlias($alias);
        if (is_array($result)) {
            $result = $result['root'];
        }
        return $result;
    }

    /**
     * @param string $alias
     * @return array|bool
     */
    protected static function findAlias(string $alias)
    {
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return ['root' => $root, 'path' => $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos)];
            }

            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return ['root' => $name, 'path' => $path . substr($alias, strlen($name))];
                }
            }
        }

        return false;
    }

    /**
     * Registers a path alias.
     *
     * A path alias is a short name representing a long path (a file path, a URL, etc.)
     * For example, we use '@yii' as the alias of the path to the Yii framework directory.
     *
     * A path alias must start with the character '@' so that it can be easily differentiated
     * from non-alias paths.
     *
     * Note that this method does not check if the given path exists or not. All it does is
     * to associate the alias with the path.
     *
     * Any trailing '/' and '\' characters in the given path will be trimmed.
     *
     * See the [guide article on aliases](guide:concept-aliases) for more information.
     *
     * @param string $alias the alias name (e.g. "@yii"). It must start with a '@' character.
     * It may contain the forward slash '/' which serves as boundary character when performing
     * alias translation by [[getAlias()]].
     * @param string $path the path corresponding to the alias. If this is null, the alias will
     * be removed. Trailing '/' and '\' characters will be trimmed. This can be
     *
     * - a directory or a file path (e.g. `/tmp`, `/tmp/main.txt`)
     * - a URL (e.g. `http://www.yiiframework.com`)
     * - a path alias (e.g. `@yii/base`). In this case, the path alias will be converted into the
     *   actual path first by calling [[getAlias()]].
     *
     * @throws InvalidArgumentException if $path is an invalid alias.
     * @see getAlias()
     */
    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }
}
