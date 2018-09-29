<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * This implementation stores:
 * - locale in `Locale` object
 * - encoding in PHP `default_charset` option and `mb_internal_encoding()`
 * - time zone in `date_default_timezone_set()`
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class I18N implements I18NInterface
{
    /**
     * @param string $encoding
     */
    public function __construct(LocaleInterface $locale, string $encoding, string $timezone)
    {
        $this->setLocale($locale);
        $this->setEncoding($encoding);
        $this->setTimeZone($timezone);
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
    private function setLocale(LocaleInterface $locale): I18NInterface
    {
        $this->locale = $locale;

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
    private function setEncoding(string $encoding): I18NInterface
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
