<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\exceptions\InvalidConfigException;
use Yiisoft\Strings\StringHelper;

/**
 * BaseHtmlPurifier provides concrete implementation for {@see HtmlPurifier}.
 *
 * Do not use BaseHtmlPurifier. Use {@see HtmlPurifier} instead.
 *
 * This helper requires `ezyang/htmlpurifier` library to be installed. This can be done via composer:
 *
 * ```
 * composer require --prefer-dist "ezyang/htmlpurifier:~4.6"
 * ```
 *
 * @see http://htmlpurifier.org/
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseHtmlPurifier
{
    /**
     * Passes markup through HTMLPurifier making it safe to output to end user.
     *
     * @param string $content The HTML content to purify
     * @param array|\Closure|null $config The config to use for HtmlPurifier.
     * If not specified or `null` the default config will be used.
     * You can use an array or an anonymous function to provide configuration options:
     *
     * - An array will be passed to the `HTMLPurifier_Config::create()` method.
     * - An anonymous function will be called after the config was created.
     *   The signature should be: `function($config)` where `$config` will be an
     *   instance of `HTMLPurifier_Config`.
     *
     *   Here is a usage example of such a function:
     *
     *   ```php
     *   // Allow the HTML5 data attribute `data-type` on `img` elements.
     *   $content = HtmlPurifier::process($content, function ($config) {
     *     $config->getHTMLDefinition(true)
     *            ->addAttribute('img', 'data-type', 'Text');
     *   });
     * ```
     * @return string the purified HTML content.
     * @throws InvalidConfigException
     */
    public static function process(string $content, $config = null): string
    {
        $configInstance = static::createConfig($config);
        $configInstance->autoFinalize = false;

        return \HTMLPurifier::instance($configInstance)->purify($content);
    }

    /**
     * Truncate a HTML string to count of characters specified.
     *
     * @param string $content The string to be truncated.
     * @param int $count
     * @param string $suffix String to append to the end of the truncated string. Default is empty string.
     * @param string $encoding
     * @param array|\Closure|null $config The config to use for HtmlPurifier.
     * @return string
     * @throws InvalidConfigException
     * @throws \HTMLPurifier_Exception
     */
    public static function truncateCharacters(string $content, int $count, string $suffix = '...', string $encoding = 'utf-8', $config = null): string
    {
        $config = static::createConfig($config);

        $tokens = \HTMLPurifier_Lexer::create($config)->tokenizeHTML($content, $config, new \HTMLPurifier_Context());
        $openTokens = [];
        $totalCount = 0;
        $depth = 0;
        $truncated = [];
        foreach ($tokens as $token) {
            if ($token instanceof \HTMLPurifier_Token_Start) { //Tag begins
                $openTokens[$depth] = $token->name;
                $truncated[] = $token;
                ++$depth;
            } elseif ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $count) { //Text
                $token->data = StringHelper::truncateCharacters($token->data, $count - $totalCount, '', $encoding);
                $currentCount = mb_strlen($token->data, $encoding);
                $totalCount += $currentCount;
                $truncated[] = $token;
            } elseif ($token instanceof \HTMLPurifier_Token_End) { //Tag ends
                if ($token->name === $openTokens[$depth - 1]) {
                    --$depth;
                    unset($openTokens[$depth]);
                    $truncated[] = $token;
                }
            } elseif ($token instanceof \HTMLPurifier_Token_Empty) { //Self contained tags, i.e. <img/> etc.
                $truncated[] = $token;
            }
            if ($totalCount >= $count) {
                if (0 < count($openTokens)) {
                    krsort($openTokens);
                    foreach ($openTokens as $name) {
                        $truncated[] = new \HTMLPurifier_Token_End($name);
                    }
                }
                break;
            }
        }
        $context = new \HTMLPurifier_Context();
        $generator = new \HTMLPurifier_Generator($config, $context);

        return $generator->generateFromTokens($truncated) . ($totalCount >= $count ? $suffix : '');
    }

    /**
     * Truncate a HTML string to count of words specified.
     *
     * @param string $content The string to be truncated.
     * @param int $count
     * @param string $suffix String to append to the end of the truncated string. Default is empty string.
     * @param string $encoding
     * @param array|\Closure|null $config The config to use for HtmlPurifier.
     * @return string
     * @throws InvalidConfigException
     * @throws \HTMLPurifier_Exception
     */
    public static function truncateWords(string $content, int $count, string $suffix = '...', string $encoding = 'utf-8', $config = null): string
    {
        $config = static::createConfig($config);

        $tokens = \HTMLPurifier_Lexer::create($config)->tokenizeHTML($content, $config, new \HTMLPurifier_Context());
        $openTokens = [];
        $totalCount = 0;
        $depth = 0;
        $truncated = [];
        foreach ($tokens as $token) {
            if ($token instanceof \HTMLPurifier_Token_Start) { //Tag begins
                $openTokens[$depth] = $token->name;
                $truncated[] = $token;
                ++$depth;
            } elseif ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $count) { //Text
                preg_match('/^(\s*)/um', $token->data, $prefixSpace) ?: $prefixSpace = ['', ''];
                $token->data = $prefixSpace[1] . StringHelper::truncateWords(ltrim($token->data), $count - $totalCount, '');
                $currentCount = StringHelper::countWords($token->data);
                $totalCount += $currentCount;
                $truncated[] = $token;
            } elseif ($token instanceof \HTMLPurifier_Token_End) { //Tag ends
                if ($token->name === $openTokens[$depth - 1]) {
                    --$depth;
                    unset($openTokens[$depth]);
                    $truncated[] = $token;
                }
            } elseif ($token instanceof \HTMLPurifier_Token_Empty) { //Self contained tags, i.e. <img/> etc.
                $truncated[] = $token;
            }
            if ($totalCount >= $count) {
                if (0 < count($openTokens)) {
                    krsort($openTokens);
                    foreach ($openTokens as $name) {
                        $truncated[] = new \HTMLPurifier_Token_End($name);
                    }
                }
                break;
            }
        }
        $context = new \HTMLPurifier_Context();
        $generator = new \HTMLPurifier_Generator($config, $context);

        return $generator->generateFromTokens($truncated) . ($totalCount >= $count ? $suffix : '');
    }

    /**
     * Creates a HtmlPurifier configuration instance.
     * @see \HTMLPurifier_Config::create()
     * @param array|\Closure|null $config The config to use for HtmlPurifier.
     * If not specified or `null` the default config will be used.
     * You can use an array or an anonymous function to provide configuration options:
     *
     * - An array will be passed to the `HTMLPurifier_Config::create()` method.
     * - An anonymous function will be called after the config was created.
     *   The signature should be: `function($config)` where `$config` will be an
     *   instance of `HTMLPurifier_Config`.
     *
     *   Here is a usage example of such a function:
     *
     *   ```php
     *   // Allow the HTML5 data attribute `data-type` on `img` elements.
     *   $content = HtmlPurifier::process($content, function ($config) {
     *     $config->getHTMLDefinition(true)
     *            ->addAttribute('img', 'data-type', 'Text');
     *   });
     *   ```
     *
     * @return \HTMLPurifier_Config HTMLPurifier config instance.
     * @throws InvalidConfigException in case "ezyang/htmlpurifier" package is not available.
     * @since 3.0.0
     */
    public static function createConfig($config = null): \HTMLPurifier_Config
    {
        if (!class_exists(\HTMLPurifier_Config::class)) {
            throw new InvalidConfigException('Unable to load "' . \HTMLPurifier_Config::class . '" class. Make sure you have installed "ezyang/htmlpurifier:~4.6" composer package.');
        }

        $configInstance = \HTMLPurifier_Config::create($config instanceof \Closure ? null : $config);
        if (Yii::getApp() !== null) {
            $configInstance->set('Cache.SerializerPath', Yii::getApp()->getRuntimePath());
            $configInstance->set('Cache.SerializerPermissions', 0775);
        }

        static::configure($configInstance);
        if ($config instanceof \Closure) {
            $config($configInstance);
        }

        return $configInstance;
    }

    /**
     * Allow the extended HtmlPurifier class to set some default config options.
     * @param \HTMLPurifier_Config $config HTMLPurifier config instance.
     * @since 2.0.3
     */
    protected static function configure(\HTMLPurifier_Config $config): void
    {
    }
}
