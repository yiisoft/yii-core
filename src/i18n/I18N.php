<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use NumberFormatter;

/**
 * I18N provides features related with internationalization (I18N) and localization (L10N).
 * Stores:
 * - locale in `Locale` object
 * - encoding in PHP `default_charset` option and `mb_internal_encoding()`
 * - time zone in `date_default_timezone_set()`
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class I18N implements I18NInterface
{
    private $encoding;

    private $timezone;

    private $locale;

    private $translation;

    /**
     * @param string $encoding
     */
    public function __construct(
        string $encoding,
        string $timezone,
        LocaleInterface $locale,
        TranslationInterface $translation
    ) {
        $this->setLocale($locale);
        $this->setEncoding($encoding);
        $this->setTimeZone($timezone);
        $this->setTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): I18NInterface
    {
        $this->locale = Locale::create($locale);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncoding(): string
    {
        return mb_internal_encoding();
    }

    /**
     * {@inheritdoc}
     */
    public function setEncoding(string $encoding): I18NInterface
    {
        ini_set('default_charset', $encoding);
        mb_internal_encoding($encoding);

        return $this;
    }

    /**
     * {@inheritdoc}
     * This is a simple wrapper of PHP function date_default_timezone_get().
     * If time zone is not configured in php.ini or application config,
     * it will be set to UTC by default.
     * @see http://php.net/manual/en/function.date-default-timezone-get.php
     */
    public function getTimeZone(): string
    {
        return date_default_timezone_get();
    }

    /**
     * {@inheritdoc}
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     */
    public function setTimeZone(string $timezone): I18NInterface
    {
        date_default_timezone_set($timezone);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    /**
     * {@inheritdoc}
     */
    private function setTranslation(TranslationInterface $translation): I18NInterface
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Translates a message to the specified language.
     * Drops in the current locale when language is not given.
     * @see Translation::translate()
     */
    public function translate(string $category, string $message, array $params = [], string $language = null)
    {
        return $this->translation->translate($category, $message, $params, $language ?: (string)$this->locale);
    }

    /**
     * Formats a message using [[MessageFormatter]].
     *
     * @param string $message the message to be formatted.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`).
     * @return string the formatted message.
     */
    public function format(string $message, array $params, string $language = null): string
    {
        return $this->translation->format($message, $params, $language ?: (string)$this->locale);
    }

    /**
     * Returns a currency symbol
     *
     * @param string $currencyCode the 3-letter ISO 4217 currency code to get symbol for. If null,
     * method will attempt using currency code from current locale.
     * @return string
     * @throws InvalidConfigException
     */
    public function getCurrencySymbol($currencyCode = null)
    {
        if (!extension_loaded('intl')) {
            throw new InvalidConfigException('Locale component requires PHP intl extension to be installed.');
        }

        $locale = $this->locale;

        if ($currencyCode !== null) {
            $locale = $locale->withCurrency($currencyCode);
        }

        $formatter = new NumberFormatter((string)$locale, NumberFormatter::CURRENCY);
        return $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }
}
