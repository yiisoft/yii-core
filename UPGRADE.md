Upgrading Instructions for Yii Framework 3.0
============================================

This file contains the upgrade notes for Yii 3.0. These notes highlight changes that
could break your application when you upgrade Yii from one version to another.
Even though we try to ensure backwards compatibility (BC) as much as possible, sometimes
it is not possible or very complicated to avoid it and still create a good solution to
a problem. While upgrade to Yii 3.0 might require substantial changes to both your application and extensions,
the changes are bearable and require "refactoring", not "rewrite".
All the "Yes, it is" cool stuff and Yii soul are still in place.

Changes summary:

* PHP requirements were raised to 7.1 and HHVM support dropped.
* Yii switches to [semver](https://semver.org/) since 3.0.
* Subtree-split is not used anymore.
* Dropped Yii own class autoloader in favor of the one provided with Composer.
* Framework GitHub repository and Packagist package are renamed and split into parts:
    * [yiisoft/yii-core] - this package, the Yii Framework Core.
    * Yii 2.0 development stays at [yiisoft/yii2] repository.
      Bug and security fixes are expected. New features and enhancements are not accepted.
      Pull requests and maintainers are very welcome.
    * [yiisoft/di] - [PSR-11] compatible Dependency Injection container.
    * [yiisoft/log] - [PSR-3] compatible logging library.
    * [yiisoft/cache] - [PSR-16] compatible caching library.
    * [yiisoft/db] - DataBase abstraction and QueryBuilder.
    * [yiisoft/db-mysql] - MySQL Server DB extension.
    * [yiisoft/db-pgsql] - PostgreSQL Server DB extension.
    * [yiisoft/db-sqlite] - SQLite Server DB extension.
    * [yiisoft/db-mssql] - MSSQL Server DB extension.
    * [yiisoft/db-oracle] - Oracle DB extension.
    * [yiisoft/active-record] - ActiveRecord library.
    * [yiisoft/rbac] - Role-Based Access Control library.
    * [yiisoft/view] - view rendering library: view and widgets.
    * [yiisoft/yii-web] - Web extension.
    * [yiisoft/yii-rest] - REST API extension.
    * [yiisoft/yii-console] - console extension.
    * [yiisoft/yii-jquery] - JQuery extension.
    * [yiisoft/yii-bootstrap3] - Bootstrap 3 extension.
    * [yiisoft/yii-bootstrap4] - Bootstrap 4 extension.
    * [yiisoft/yii-dataview] - data displaying extension.
    * [yiisoft/yii-masked-input] - Masked input field widget.
    * [yiisoft/yii-captcha] - CAPTCHA extension.
    * Please check [package naming convention] to get an idea about package names.
    * Also please see full [list of packages].
* More PSR compatibility: [PSR-3], [PSR-11], [PSR-16]
* Framework core requires only virtual PSR implementation packages, you are free
  to choose your logger and cache implementations.
  More PSR implementations compatibility is expected later.
* Removed `ServiceLocator` from `Application` and `Module`, DI container is used instead.
* Database abstraction is split in several packages allowing independent use.
* Removed PJAX support.
* Cubrid DB is not supported anymore.
* Profiling and logging got separated.
* [yiisoft/yii2-composer] plugin is not used anymore.
* All the configuration made explicit in `config` folders of all the packages
  and recommended to be used with [composer-config-plugin].
* [yii\base\Configurable] interface and logic are removed in favour of DI and [yii\di\Initiable] interface.
  So `BaseObject` class and it's descendants don't have `$config` parameter in their constructors anymore.
  But it can be added where appropriate. E.g. see `yii\validators\Validator::__construct()`.
* No advanced app anymore. Application templating approach has been changed, please see:
    * [yiisoft/yii-project-template] - project template;
    * [yiisoft/yii-base-web] - web application base.

[yiisoft/yii2]:                 https://github.com/yiisoft/yii2
[yiisoft/di]:                   https://github.com/yiisoft/di
[yiisoft/log]:                  https://github.com/yiisoft/log
[yiisoft/cache]:                https://github.com/yiisoft/cache
[yiisoft/db]:                   https://github.com/yiisoft/db
[yiisoft/active-record]:        https://github.com/yiisoft/active-record
[yiisoft/data-mapper]:          https://github.com/yiisoft/data-mapper
[yiisoft/db-mysql]:             https://github.com/yiisoft/db-mysql
[yiisoft/db-pgsql]:             https://github.com/yiisoft/db-pgsql
[yiisoft/db-sqlite]:            https://github.com/yiisoft/db-sqlite
[yiisoft/db-mssql]:             https://github.com/yiisoft/db-mssql
[yiisoft/db-oracle]:            https://github.com/yiisoft/db-oracle
[yiisoft/rbac]:                 https://github.com/yiisoft/rbac
[yiisoft/view]:                 https://github.com/yiisoft/view
[yiisoft/yii-core]:             https://github.com/yiisoft/yii-core
[yiisoft/yii-web]:              https://github.com/yiisoft/yii-web
[yiisoft/yii-console]:          https://github.com/yiisoft/yii-console
[yiisoft/yii-jquery]:           https://github.com/yiisoft/yii-jquery
[yiisoft/yii-bootstrap3]:       https://github.com/yiisoft/yii-bootstrap3
[yiisoft/yii-bootstrap4]:       https://github.com/yiisoft/yii-bootstrap4
[yiisoft/yii-dataview]:         https://github.com/yiisoft/yii-dataview
[yiisoft/yii-masked-input]:     https://github.com/yiisoft/yii-masked-input
[yiisoft/yii-captcha]:          https://github.com/yiisoft/yii-captcha
[yiisoft/yii-rest]:             https://github.com/yiisoft/yii-rest
[yiisoft/yii-project-template]: https://github.com/yiisoft/yii-project-template
[yiisoft/yii-base-web]:         https://github.com/yiisoft/yii-base-web
[yiisoft/yii2-composer]:        https://github.com/yiisoft/yii2-composer
[recommended entry script]:     https://github.com/yiisoft/yii-app-template/blob/master/public/index.php
[package naming convention]:    https://github.com/yiisoft/yii-core/blob/master/docs/guide/structure-extensions.md#package-naming
[list of packages]:             https://github.com/yiisoft/docs/blob/master/packages.md
[PSR-3]:                        https://www.php-fig.org/psr/psr-3/
[PSR-11]:                       https://www.php-fig.org/psr/psr-11/
[PSR-16]:                       https://www.php-fig.org/psr/psr-16/
[composer-config-plugin]:       https://github.com/hiqdev/composer-config-plugin
[yii\base\Configurable]:        https://github.com/yiisoft/yii2/blob/master/framework/base/Configurable.php
[yii\di\Initiable]:              https://github.com/yiisoft/di/blob/master/src/Initiable.php

> Tip: Upgrading dependencies of a complex software project always comes at the risk of breaking something, so make sure
you have a backup. You should back up anyway ;)

After upgrading you should check whether your application still works as expected and no tests are broken.
See the following notes on which changes to consider when upgrading from one version to another.

> Note: The following upgrading instructions are cumulative. That is,
if you want to upgrade from version A to version C and there is
version B between A and C, you need to follow the instructions
for both A and B.

Upgrade from Yii 2.0.x
----------------------

* PHP requirements were raised to 7.1. Make sure your code is updated accordingly.
* `memcache` PECL extension support was dropped. Use `memcached` PECL extension instead.
* Removed `Configurable` and `init()` from `BaseObject`
    * `__construct(array $config = [])` is not supported
    * use [yii\di\Initiable] interface if you want `init()` function to be called by DI
      after construction of your class object
* `Yii` helper is redone and doesn't provide "global vars" anymore:
    * change `use Yii` to `use yii\helpers\Yii`
    * `Yii::$app` is not available:
        * prefer to use constructor DI to get application and services
        * use `Yii::get('service')` instead of `Yii::$app->getService()`
        * use `Yii::getApp()` if everything else fails
    * aliases were moved to `Aliases` service:
        * prefered wat to resolve aliases:
            * DI injected `aliases` service or
            * `$this->app->getAlias()`
        * prefered way to set aliases is through configuration
    * added and used `Application::t()`
        * prefer `$this->app->t()` over `Yii::t()`
    * removed `Yii::configure()`
        * use `yii\di\AbstractContainer::configure()` if everything else fails
    * `Yii::$container` made private:
        * don't use it explicitly
        * use container implicitly with constructor DI
    * no need to require Yii class from entry script
    * to use `Yii` features use `Yii::setContainer()`
        * for it could access logger, profiler and i18n
        * else Yii functions will have generic behavior
        * see [recommended entry script]
    * removed `Yii::$logger` and `Yii::$profiler`, use DI instead
    * constant definitions moved to `config/defines.php`
        * `YII_DEBUG` defaults to `YII_ENV_DEV`
        * `YII2_PATH` renamed to `YII_PATH`
    * `getObjectVars()` moved to `ArrayHelper`
* Removed `ServiceLocator` from `Application` and `Module`:
    * no own components in app and modules
    * get services implicitly with DI
* Refactored internationalization:
    * Added Locale class able to parse BCP 47 locale strings. It's used instead of language.
    * `Application::setEncoding` method
        * encoding saved to `ini_set('default_charset')` and `mb_internal_encoding()`
          and thus applied by default to all PHP string functions like `mb_*`
* Moved files around for more logic organization and more readable directories:
    * globally:
        * all exceptions moved into separate dirs in all packages
    * yii-web:
        * moved web formatters to their own directory
    * cache:
        * renamed namespace `yii\caching` -> `Yiisoft\Cache`
        * moved cache dependencies to own directory
    * yii-project-template:
        * moved web server root dir to `public` (was web)
* Moved data displaying widgets to own package [yiisoft/yii-dataview]:
    * `yii\grid` -> `Yiisoft\Yii\DataView`
    * `yii\grid\*Column` -> `Yiisoft\Yii\DataView\Columns\*`
    * `yii\widgets\ListView` -> `Yiisoft\Yii\DataView\ListView`
    * `yii\widgets\DetailView` -> `Yiisoft\Yii\DataView\DetailView`
* DI:
    * Config changed to be DI container config instead of application.
    * Removed `yii\di\Instance` class:
        * Use `yii\di\Reference` instead
        * Use `yii\di\Factory::ensure()` or `Yii::ensureObject()` instead of `Instance::ensure()`
* Events were refactored:
    * event name constants moved to event classes:
        * `yii\base\Application::EVENT_BEFORE_ACTION` -> `yii\base\ActionEvent::BEFORE`
        * `yii\base\Application::EVENT_AFTER_ACTION` -> `yii\base\ActionEvent::AFTER`
        * `yii\base\Application::EVENT_BEFORE_REQUEST` -> `yii\base\RequestEvent::BEFORE`
        * `yii\base\Application::EVENT_AFTER_REQUEST` -> `yii\base\RequestEvent::AFTER`
        * `yii\base\View::EVENT_END_BODY` -> `yii\view\BodyEvent::END`
        * `yii\base\View::EVENT_BEGIN_BODY` -> `yii\view\BodyEvent::BEGIN`
        * `yii\base\Response::EVENT_BEFORE_PREPARE` -> `yii\base\PrepareEvent::BEFORE` (to be done)
        * `yii\base\Response::EVENT_AFTER_PREPARE` -> `yii\base\PrepareEvent::AFTER` (to be done)
* Added default application configuration and support for config assembling with
  [composer-config-plugin](https://github.com/hiqdev/composer-config-plugin).
* Tests:
    * Renamed `yiiunit` namespace to `yii\tests`
    * Added `tests` config

* Following new methods have been added to `yii\mail\MessageInterface` `addHeader()`, `setHeader()`, `getHeader()`, `setHeaders()`
  providing ability to setup custom mail headers. Make sure your provide implementation for those methods, while
  creating your own mailer solution.
* `::className()` method calls should be replaced with [native](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class) `::class`.
  When upgrading to Yii 3.0, You should do a global search and replace for `::className()` to `::class`.
  All calls on objects via `->className()` should be replaced by a call to `get_class()`.
* Dependency injection (DI) layer has been replaced by "yiisoft/di" package. Make sure to update class/object definitions at
  your code to match the syntax used by it. In particular: you should use '__class' array key instead of 'class' for
  class name specification.
* XCache and Zend data cache support was removed. Switch to another caching backends.
* Rename `InvalidParamException` usage to `InvalidArgumentException`.
* CAPTCHA package has been moved into separate extension https://github.com/yiisoft/yii-captcha.
  Include it in your composer.json if you use it.
* JQuery related code (e.g. `yii.js`, `yiiActiveForm.js`, `yiiGridView.js`) has been moved into separate extension https://github.com/yiisoft/yii-jquery.
  Include it in your composer.json if you use it.
* REST API package has been moved into separate extension https://github.com/yiisoft/yii-rest.
  Include it in your composer.json if you use it.
* MSSQL Server DB package has been moved into separate extension https://github.com/yiisoft/yii-mssql.
  Include it in your composer.json if you use it.
* Oracle DB package has been moved into separate extension https://github.com/yiisoft/yii-oracle.
  Include it in your composer.json if you use it.
* CUBRID support has been removed, package `Yiisoft\Db\cubrid\*` is no longer available.
  If you need to use CUBRID further you should create your own integration for it.
* Masked input field widget was moved into separate extension https://github.com/yiisoft/yii-masked-input.
  Include it in your composer.json if you use it.
* PJAX support has been removed: widget `yii\widget\Pjax`, method `yii\web\Request::getIsPjax()`, PJAX related checks and
  headers are no longer available. If you wish to use PJAX further you should create your own integration for it.
* If you've used ApcCache and set `useApcu` in your config, remove the option.
* During mail view rendering the `$message` variable is no longer set by default to be an instance of `yii\mail\MessageInterface`. Instead it is available via `$this->context->message` expression.
* `yii\mail\BaseMailer::render()` method has been removed. Make sure you do not use it anywhere in your program.
  Mail view rendering is now encapsulated into `yii\mail\Template` class.
* Properties `view`, `viewPath`, `htmlLayout` and `textLayout` have been moved from `yii\mail\BaseMailer` to `yii\mail\Composer` class,
  which now encapsulates message composition.
* Interface of `Yiisoft\Log\Logger` has been changed according to [PSR-3] `Psr\Log\LoggerInterface`.
  Make sure you update your code accordingly in case you invoke `Logger` methods directly.
* Constants `Yiisoft\Log\Logger::LEVEL_ERROR`, `Yiisoft\Log\Logger::LEVEL_WARNING` and so on have been removed.
  Use constants from `Psr\Log\LogLevel` instead.
* Method `yii\BaseYii::trace()` has been renamed to `debug()`. Make sure you use correct name for it.
* Class `Yiisoft\Log\Dispatcher` has been removed as well as application 'log' component. Log targets
  now should be configured using `yii\base\Application::$logger` property. Neither 'log' or 'logger'
  components should be present at `yii\base\Application::$bootstrap`
* Profiling related functionality has been extracted into a separated component under `yii\profile\ProfilerInterface`.
  Profiling messages should be collection using `yii\base\Application::$profiler`. In case you wish to
  continue storing profiling messages along with the log ones, you may use `yii\profile\LogTarget` profiling target.
* Classes `yii\web\Request` and `yii\web\Response` have been updated to match interfaces `Psr\Http\Message\ServerRequestInterface`
  and `Psr\Http\Message\ResponseInterface` accordingly. Make sure you use their methods and properties correctly.
  In particular: method `getHeaders()` and corresponding virtual property `$headers` are no longer return `HeaderCollection`
  instance, you can use `getHeaderCollection()` in order to use old headers setup syntax; `Request|Response::$version` renamed
  to `Request|Response::$protocolVersion`; `Response::$statusText` renamed `Response::$reasonPhrase`;
  `Request::$bodyParams` renamed to `Request::$parsedBody`; `Request::getBodyParam()` renamed to `Request::getParsedBodyParam()`;
* `yii\web\Response::$stream` is no longer available, use `yii\web\Response::withBody()` to setup stream response.
  You can use `Response::$bodyRange` to setup stream content range.
* Classes `yii\web\CookieCollection`, `yii\web\HeaderCollection` and `yii\web\UploadedFile` have been moved under
  namespace `yii\http\*`. Make sure to refer to those classes using correct fully qualified name.
* Public interface of `UploadedFile` class has been changed according to `Psr\Http\Message\UploadedFileInterface`.
  Make sure you refer to its properties and methods with correct names.
* `Yiisoft\Yii\Captcha\CaptchaAction` has been refactored. Rendering logic was extracted into `Yiisoft\Yii\Captcha\DriverInterface`, which
  instance is available via `Yiisoft\Yii\Captcha\CaptchaAction::$driver` field. All image settings now should be passed to
  the driver fields instead of action. Automatic detection of the rendering driver is no longer supported.
* `Yiisoft\Yii\Captcha\Captcha::checkRequirements()` method has been removed.
* All cache related classes interface has been changed according to [PSR-16] "Simple Cache" specification. Make sure you
  change your invocations for the cache methods accordingly. The most notable changes affects methods `get()` and `getMultiple()`
  as they now accept `$default` argument, which value will be returned in case there is no value in the cache. This makes
  the default return value to be `null` instead of `false`.
* Particular cache implementation should now be configured as `yii\caching\Cache::$handler` property instead of the
  component itself. Properties `$defaultTtl`, `$serializer` and `$keyPrefix` has been moved to cache handler and should
  be configured there. Creating your own cache implementation you should implement `\Psr\SimpleCache\CacheInterface` or
  extend `yii\caching\SimpleCache` abstract class. Use `yii\caching\CacheInterface` only if you wish to replace `yii\caching\Cache`
  component providing your own solution for cache dependency handling.
* `yii\caching\SimpleCache::$serializer` now should be `yii\serialize\SerializerInterface` instance or its DI compatible configuration.
  Thus it does no longer accept pair of serialize/unserialize functions as an array. Use `yii\serialize\CallbackSerializer` or
  other predefined serializer class from `yii\serialize\*` namespace instead.
* Console command used to clear cache now calls related actions "clear" instead of "flush".
* Yii autoloader was removed in favor of Composer-generated one. You should remove explicit inclusion of `Yii.php` from
  your entry `index.php` scripts. In case you have relied on class map, use `composer.json` instead of configuring it
  with PHP. For details please refer to [guide on autoloading](https://github.com/yiisoft/yii2/blob/3.0/docs/guide/concept-autoloading.md),
  [guide on customizing helpers](https://github.com/yiisoft/yii2/blob/3.0/docs/guide/helper-overview.md#customizing-helper-classes-)
  and [guide on Working with Third-Party Code](https://github.com/yiisoft/yii2/blob/3.0/docs/guide/tutorial-yii-integration.md).
* The signature of `yii\web\RequestParserInterface::parse()` was changed. The method now accepts the `yii\web\Request` instance
  as a sole argument. Make sure you declare and implement this method correctly, while creating your own request parser.
* Uploaded file retrieve methods have been moved from `yii\http\UploadedFile` to `yii\web\Request`. You should use `Request::getUploadedFileByName()`
  instead of `UploadedFile::getInstanceByName()` and `Request::getUploadedFilesByName()` instead of `UploadedFile::getInstancesByName()`.
  Instead of `UploadedFile::getInstance()` and `UploadedFile::getInstances()` use construction `$model->load(Yii::$app->request->getUploadedFiles())`.
* Result of `yii\web\Request::getBodyParams()` now includes uploaded files (e.g. result of `yii\web\Request::getUploadedFiles()`).
  You should aware that instances of `yii\http\UploadedFile` may appear inside body params.
* The following method signature have changed. If you override any of them in your code, you have to adjust these places:
  `Yiisoft\Db\QueryBuilder::buildGroupBy($columns)` -> `buildGroupBy($columns, &$params)`
  `Yiisoft\Db\QueryBuilder::buildOrderByAndLimit($sql, $orderBy, $limit, $offset)` -> `buildOrderByAndLimit($sql, $orderBy, $limit, $offset, &$params)`
  `yii\widgets\ActiveField::hint($content = null, $options = [])`
  `yii\base\View::renderDynamic($statements)` -> `yii\base\View::renderDynamic($statements, array $params = [])`
* `yii\filters\AccessControl` has been optimized by only instantiating rules at the moment of use.
   This could lead to a potential BC-break if you are depending on $rules to be instantiated in init().
* `yii\widgets\BaseListView::run()` and `yii\widgets\GridView::run()` now return content, instead of echoing it.
  Normally we call `BaseListView::widget()` and for this case behavior is NOT changed.
  In case you call `::run()` method, ensure that its return is processed correctly.
* `yii\web\UrlNormalizer` is now enabled by default in `yii\web\UrlManager`.
  If you are using `yii\web\Request::resolve()` or `yii\web\UrlManager::parseRequest()` directly, make sure that
  all potential exceptions are handled correctly or set `yii\web\UrlNormalizer::$normalizer` to `false` to disable normalizer.
* `yii\base\InvalidParamException` was renamed to `yii\base\InvalidArgumentException`.
* Classes `yii\widgets\ActiveForm`, `yii\widgets\ActiveField`, `yii\grid\GridView`, `yii\web\View` have been refactored
  to be more generic without including any 'JQuery' support and client-side processing (validation, automatic submit etc.).
  You should use widget behaviors from `Yiisoft\Yii\JQuery\*` package to make old code function as before. E.g. attach `Yiisoft\Yii\JQuery\ActiveFormClientScript`
  to `yii\widgets\ActiveForm`, `Yiisoft\Yii\JQuery\GridViewClientScript` to `yii\grid\GridView` and so on.
* Fields `$enableClientScript` and `$attributes` have been removed from `yii\widgets\ActiveForm`. Make sure
  you do not use them or specify them during `ActiveForm::begin()` invocation.
* Field `yii\grid\GridView::$filterSelector` has been removed. Make sure you do not use it or specify it during
  `GridView::widget()` invocation. Use `Yiisoft\Yii\JQuery\GridViewClientScript::$filterSelector` instead.
* Method `getClientOptions()` has been removed from `yii\validators\Validator` and all its descendants.
  All implementations of `clientValidateAttribute()` around built-in validators now return `null`.
  Use classes from `Yiisoft\Yii\JQuery\Validators\Client\*` for building client validation (JavaScript) code.
* Assets `yii\web\JqueryAsset`, `yii\web\YiiAsset`, `yii\validators\ValidationAsset` have been moved under `Yiisoft\Yii\JQuery\*`
  namespace. Make sure you refer to the new full-qualified names of this classes.
* Methods `yii\validators\Validator::formatMessage()`, `yii\validators\IpValidator::getIpParsePattern()` and
  `yii\validators\FileValidator::buildMimeTypeRegexp()` have been made `public`. Make sure you use correct
  access level specification in case you override these methods.
* Default script position for the `yii\web\View::registerJs()` changed to `View::POS_END`.
* Package "ezyang/htmlpurifier" has been made optional and is not installed by default. If you need to use
  `yii\helpers\HtmlPurifier` or `yii\i18n\Formatter::asHtml()` (e.g. 'html' data format), you'll have to install
  this package manually for your project.
* `yii\BaseYii::powered()` method has been removed. Please add "Powered by Yii" link either right into HTML or using
  `yii\helpers\Html::a()`.
* Public interface of the `yii\base\Event` class have been changed to match commonly used event notations. Field `$sender`
  converted into virtual property `$target`, field `$data` converted into virtual property `$params`. Public field `$handled`
  has been removed - use methods `stopPropagation()` and `isPropagationStopped()` instead.
* Signature of the methods `yii\base\Component::trigger()` and `yii\base\Event::trigger()` has been changed, removing
  `$name` argument and allowing `$event` to be a string event name. Make sure you invoke these methods correctly and apply
  new signature in case you override them.
* `yii\i18n\MessageFormatter` no longer supports parameter names with `.`, `-`, `=` and other symbols that are used in
  pattern syntax following directly how it works in intl/ICU. If you use such parameters names, replace special
  symbols with `_`.
* `yii\i18n\MessageFormatter::parse()` method was removed. If you have a rare case where it's used copy-paste it from
  2.0 branch to your project. 
* `yii\helpers\Markdown` was removed. Use `cebe/markdown` composer package directly.
* `yii\helpers\FileHelper` now always uses `/` as normalized directory separator regardless of operating system used.
  Adjust your code that work with paths if needed.
* Ability to truncate content taking HTML into account was removed from `StringHelper` in favor of `HtmlPurifier` helper.

