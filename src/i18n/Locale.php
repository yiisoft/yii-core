<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\exceptions\InvalidConfigException;

/**
 * Locale stores locale information created from BCP 47 formatted string
 * https://tools.ietf.org/html/bcp47
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Locale implements SourceLocaleInterface
{
    /**
     *
     * @var string shortest ISO 639 code
     */
    private $language;

    private $extendedLanguage;

    private $extension;

    /**
     * @var string ISO 15924 code
     */
    private $script;

    /**
     * @var string ISO 3166-1 code / 3DIGIT              ; UN M.49 code
     */
    private $region;

    private $variant;

    private $currency;

    private $grandfathered;

    private $privateUse;

    /**
     * Locale constructor.
     * @param string $localeString BCP 47 formatted locale string
     * @see https://tools.ietf.org/html/bcp47
     * @throws InvalidConfigException
     */
    public function __construct(string $localeString)
    {
        $subtags = static::parseLocale($localeString);
        if ($subtags === null) {
            throw new InvalidConfigException($localeString . ' is not valid BCP 47 formatted locale string');
        }

        foreach (array_keys(get_class_vars(get_class())) as $tag) {
            if (isset($subtags[$tag])) {
                $this->{$tag} = $subtags[$tag];
            }
        }
    }

    /** {@inheritdoc} */
    public function getScript(): string
    {
        return $this->script;
    }

    /** {@inheritdoc} */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /** {@inheritdoc} */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /** {@inheritdoc} */
    public function withLanguage(string $language): LocaleInterface
    {
        $clone = clone $this;
        $clone->language = $language;
        return $clone;
    }

    /** {@inheritdoc} */
    public function getRegion(): string
    {
       return $this->region;
    }

    /** {@inheritdoc} */
    public function withRegion(string $region): LocaleInterface
    {
        $clone = clone $this;
        $clone->region = $region;
        return $clone;
    }

    /** {@inheritdoc} */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /** {@inheritdoc} */
    public function withCurrency(string $currency): LocaleInterface
    {
        $clone = clone $this;
        $clone->currency = $currency;

        return $clone;
    }

    /**
     * Returns a key-value array of locale ID subtag elements.
     * @param string $localeString BCP 47 formatted locale string
     * @param bool $forceFallback
     * @return array|null
     */
    public static function parseLocale(string $localeString, bool $forceFallback = false): ?array
    {
        if (!$forceFallback && class_exists(\Locale::class, false)) {
            $res = \Locale::parseLocale($localeString);
            if (empty($res['language'])) {
                return null;
            }

            // TODO get all variants?
            if (!empty($res['variant0'])) {
                $res['variant'] = $res['variant0'];
            }
            if (!empty($res['private0'])) {
                $res['privateUse'] = $res['private0'];
            }

            return $res;
        }

        if (!preg_match(static::getBCP47Regex(), $localeString, $matches)) {
            return null;
        }

        if (!empty($matches['language'])) {
            $subtags['language'] = strtolower($matches['language']);
        }

        if (!empty($matches['region'])) {
            $subtags['region'] = strtoupper($matches['region']);
        }

        if (!empty($matches['variant'])) {
            $subtags['variant'] = $matches['variant'];
        }

        if (!empty($matches['extendedLanguage'])) {
            $subtags['extendedLanguage'] = $matches['extendedLanguage'];
        }

        if (!empty($matches['extension'])) {
            $subtags['extension'] = $matches['extension'];
        }

        if (!empty($matches['script'])) {
            $subtags['script'] = ucfirst(strtolower($matches['script']));
        }

        if (!empty($matches['grandfathered'])) {
            $subtags['grandfathered'] = $matches['grandfathered'];
        }

        if (!empty($matches['privateUse'])) {
            $subtags['privateUse'] = $matches['privateUse'];
        }

        return $subtags;
    }

    /**
     * @return string regular expression for parsing BCP 47
     * @see https://tools.ietf.org/html/bcp47
     */
    private static function getBCP47Regex(): string
    {
        $regular = '(?:art-lojban|cel-gaulish|no-bok|no-nyn|zh-guoyu|zh-hakka|zh-min|zh-min-nan|zh-xiang)';
        $irregular = '(?:en-GB-oed|i-ami|i-bnn|i-default|i-enochian|i-hak|i-klingon|i-lux|i-mingo|i-navajo|i-pwn|i-tao|i-tay|i-tsu|sgn-BE-FR|sgn-BE-NL|sgn-CH-DE)';
        $grandfathered = '(?<grandfathered>' . $irregular . '|' . $regular . ')';
        $privateUse = '(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+)';
        $singleton = '[0-9A-WY-Za-wy-z]';
        $extension = '(?<extension>' . $singleton . '(:?-[A-Za-z0-9]{2,8})+)';
        $variant = '(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3})';
        $region = '(?<region>[A-Za-z]{2}|[0-9]{3})';
        $script = '(?<script>[A-Za-z]{4})';
        $extendedLanguage = '(?<extendedLanguage>[A-Za-z]{3}(:?-[A-Za-z]{3}){0,2})';
        $language = '(?<language>(?:[A-Za-z]{2,3}(?:-' . $extendedLanguage . ')?)|[A-Za-z]{4}|[A-Za-z]{5,8})';
        $languageTag = '(?:' . $language . '(?:-' . $script . ')?' . '(?:-' . $region . ')?' . '(?:-' . $variant . ')*' . '(?:-' . $extension . ')*' . '(?:-' . $privateUse . ')?' . ')';
        return '/^(?J:' . $grandfathered . '|' . $languageTag . '|' . $privateUse . ')$/';
    }

    public function __toString(): string
    {
        return $this->getID();
    }

    /** {@inheritdoc} */
    public function getID(): string
    {
        return static::composeLocale(array_filter(get_object_vars($this)));
    }

    public static function composeLocale(array $subtags, bool $forceFallback = false): string
    {
        if (!$forceFallback && class_exists(\Locale::class, false)) {
            return strtr(\Locale::composeLocale($subtags), '_', '-');
        }

        if (isset($subtags['grandfathered'])) {
            return $subtags['grandfathered'];
        }

        $result = [];
        if (isset($subtags['language'])) {
            $result[] = $subtags['language'];

            if (isset($subtags['extendedLanguage'])) {
                $result[] = $subtags['extendedLanguage'];
            }

            if (isset($subtags['script'])) {
                $result[] = $subtags['script'];
            }

            if (isset($subtags['region'])) {
                $result[] = $subtags['region'];
            }

            if (isset($subtags['variant'])) {
                $result[] = $subtags['variant'];
            }

            if (isset($subtags['extension'])) {
                $result[] = $subtags['extension'];
            }
        }

        if (isset($subtags['privateUse'])) {
           $result[] = $subtags['privateUse'];
        }

        return implode('-', $result);
    }
}
