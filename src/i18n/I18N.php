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
class I18N
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param string $encoding
     */
    public function __construct(
        string $encoding,
        string $timezone,
        Locale $locale,
        Translator $translator
    ) {
        $this->setLocale($locale);
        $this->setEncoding($encoding);
        $this->setTimeZone($timezone);
        $this->setTranslator($translator);
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function setLocale($locale): self
    {
        $this->locale = Locale::create($locale);

        return $this;
    }

    public function getEncoding(): string
    {
        return mb_internal_encoding();
    }

    public function setEncoding(string $encoding): self
    {
        ini_set('default_charset', $encoding);
        mb_internal_encoding($encoding);

        return $this;
    }

    /**
     * Returns the time zone set for this i18n.
     *
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
     * Sets the time zone for this i18n.
     *
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     * @param string $timezone
     * @return I18N
     */
    public function setTimeZone(string $timezone): self
    {
        date_default_timezone_set($timezone);

        return $this;
    }

    public function getTranslator(): Translator
    {
        return $this->translator;
    }

    private function setTranslator(Translator $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Translates a message to the specified language.
     * Drops in the current locale when language is not given.
     * @see Translator::translate()
     */
    public function translate(string $category, string $message, array $params = [], string $language = null)
    {
        return $this->translator->translate($category, $message, $params, $language ?: (string)$this->locale);
    }

    /**
     * Formats a message using {@see MessageFormatter}.
     *
     * @param string $message the message to be formatted.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`).
     * @return string the formatted message.
     */
    public function format(string $message, array $params, string $language = null): string
    {
        return $this->translator->format($message, $params, $language ?: (string)$this->locale);
    }

    /**
     * Returns a currency symbol
     *
     * @param string $currencyCode the 3-letter ISO 4217 currency code to get symbol for. If null,
     * method will attempt using currency code from current locale.
     * @return string
     * @throws InvalidConfigException
     */
    public function getCurrencySymbol($currencyCode = null): string
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
