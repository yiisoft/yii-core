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
     * @var string|null Two-letter ISO-639-2 language code
     * @see http://www.loc.gov/standards/iso639-2/
     */
    private $language;

    /**
     * @var string|null extended language subtags
     */
    private $extendedLanguage;

    /**
     * @var string|null
     */
    private $extension;

    /**
     * @var string|null Four-letter ISO 15924 script code
     * @see http://www.unicode.org/iso15924/iso15924-codes.html
     */
    private $script;

    /**
     * @var string|null Two-letter ISO 3166-1 country code
     * @see https://www.iso.org/iso-3166-country-codes.html
     */
    private $region;

    /**
     * @var string|null variant of language conventions to use
     */
    private $variant;

    /**
     * @var string|null ICU currency
     */
    private $currency;

    /**
     * @var string|null ICU calendar
     */
    private $calendar;

    /**
     * @var string ICU collation
     */
    private $collation;

    /**
     * @var string|null ICU numbers
     */
    private $numbers;

    /**
     * @var string|null
     */
    private $grandfathered;

    /**
     * @var string|null
     */
    private $private;

    /**
     * Locale constructor.
     * @param string $localeString BCP 47 formatted locale string
     * @see https://tools.ietf.org/html/bcp47
     * @throws InvalidConfigException
     */
    public function __construct(string $localeString)
    {
        if (!preg_match(static::getBCP47Regex(), $localeString, $matches)) {
            throw new InvalidConfigException($localeString . ' is not valid BCP 47 formatted locale string');
        }

        if (!empty($matches['language'])) {
            $this->language = strtolower($matches['language']);
        }

        if (!empty($matches['region'])) {
            $this->region = strtoupper($matches['region']);
        }

        if (!empty($matches['variant'])) {
            $this->variant = $matches['variant'];
        }

        if (!empty($matches['extendedLanguage'])) {
            $this->extendedLanguage = $matches['extendedLanguage'];
        }

        if (!empty($matches['extension'])) {
            $this->extension = $matches['extension'];
        }

        if (!empty($matches['script'])) {
            $this->script = ucfirst(strtolower($matches['script']));
        }

        if (!empty($matches['grandfathered'])) {
            $this->grandfathered = $matches['grandfathered'];
        }

        if (!empty($matches['private'])) {
            $this->private = $matches['private'];
        }

        if (!empty($matches['keywords'])) {
            foreach (explode(';', $matches['keywords']) as $pair) {
                [$key, $value] = explode('=', $pair);

                if ($key === 'calendar') {
                    $this->calendar = $value;
                }

                if ($key === 'collation') {
                    $this->collation = $value;
                }

                if ($key === 'currency') {
                    $this->currency = $value;
                }

                if ($key === 'numbers') {
                    $this->numbers = $value;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScript(): string
    {
        return $this->script;
    }

    /**
     * {@inheritdoc}
     */
    public function withScript(?string $script): LocaleInterface
    {
        $clone = clone $this;
        $clone->script = $script;
        return $clone;
    }


    /**
     * {@inheritdoc}
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * {@inheritdoc}
     */
    public function withVariant(?string $variant): LocaleInterface
    {
        $clone = clone $this;
        $clone->variant = $variant;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function withLanguage(?string $language): LocaleInterface
    {
        $clone = clone $this;
        $clone->language = $language;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendar(): string
    {
        return $this->calendar;
    }

    /**
     * {@inheritdoc}
     */
    public function withCalendar(?string $calendar): LocaleInterface
    {
        $clone = clone $this;
        $clone->calendar = $calendar;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollation(): string
    {
        return $this->collation;
    }

    /**
     * {@inheritdoc}
     */
    public function withCollation(?string $collation): LocaleInterface
    {
        $clone = clone $this;
        $clone->collation = $collation;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumbers(): string
    {
        return $this->numbers;
    }

    /**
     * {@inheritdoc}
     */
    public function withNumbers(?string $numbers): LocaleInterface
    {
        $clone = clone $this;
        $clone->numbers = $numbers;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * {@inheritdoc}
     */
    public function withRegion(?string $region): LocaleInterface
    {
        $clone = clone $this;
        $clone->region = $region;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * {@inheritdoc}
     */
    public function withCurrency(?string $currency): LocaleInterface
    {
        $clone = clone $this;
        $clone->currency = $currency;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedLanguage(): string
    {
        return $this->extendedLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function withExtendedLanguage(?string $extendedLanguage): LocaleInterface
    {
        $clone = clone $this;
        $clone->extendedLanguage = $extendedLanguage;

        return $clone;
    }


    /**
     * {@inheritdoc}
     */
    public function getPrivate(): ?string
    {
        return $this->private;
    }

    /**
     * {@inheritdoc}
     */
    public function withPrivate(?string $private): LocaleInterface
    {
        $clone = clone $this;
        $clone->private = $private;

        return $clone;
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
        $private = '(?<private>x(?:-[A-Za-z0-9]{1,8})+)';
        $singleton = '[0-9A-WY-Za-wy-z]';
        $extension = '(?<extension>' . $singleton . '(?:-[A-Za-z0-9]{2,8})+)';
        $variant = '(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3})';
        $region = '(?<region>[A-Za-z]{2}|[0-9]{3})';
        $script = '(?<script>[A-Za-z]{4})';
        $extendedLanguage = '(?<extendedLanguage>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2})';
        $language = '(?:(?<language>[A-Za-z]{4,8})|(?<language>[A-Za-z]{2,3})(?:-' . $extendedLanguage . ')?)';
        $icuKeywords = '(?:@(?<keywords>.*?))?';
        $languageTag = '(?:' . $language . '(?:-' . $script . ')?' . '(?:-' . $region . ')?' . '(?:-' . $variant . ')*' . '(?:-' . $extension . ')*' . '(?:-' . $private . ')?' . ')';
        return '/^(?J:' . $grandfathered . '|' . $languageTag . '|' . $private . ')' . $icuKeywords . '$/';
    }

    public function __toString(): string
    {
        return $this->asString();
    }

    /**
     * {@inheritdoc}
     */
    public function asString(): string
    {
        if ($this->grandfathered !== null) {
            return $this->grandfathered;
        }

        $result = [];
        if ($this->language !== null) {
            $result[] = $this->language;

            if ($this->extendedLanguage !== null) {
                $result[] = $this->extendedLanguage;
            }

            if ($this->script !== null) {
                $result[] = $this->script;
            }

            if ($this->region !== null) {
                $result[] = $this->region;
            }

            if ($this->variant !== null) {
                $result[] = $this->variant;
            }

            if ($this->extension !== null) {
                $result[] = $this->extension;
            }
        }

        if ($this->private !== null) {
            $result[] = $this->private;
        }

        $keywords = [];
        if ($this->currency !== null) {
            $keywords[] = 'currency=' . $this->currency;
        }
        if ($this->collation !== null) {
            $keywords[] = 'collation=' . $this->collation;
        }
        if ($this->calendar !== null) {
            $keywords[] = 'calendar=' . $this->calendar;
        }
        if ($this->numbers !== null) {
            $keywords[] = 'numbers=' . $this->numbers;
        }

        $string = implode('-', $result);

        if ($keywords !== []) {
            $string .= '@' . implode(';', $keywords);
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale(): LocaleInterface
    {
        if ($this->variant !== null) {
            return $this->withVariant(null);
        }

        if ($this->region !== null) {
            return $this->withRegion(null);
        }

        if ($this->script !== null) {
            return $this->withScript(null);
        }

        return $this;
    }
}
