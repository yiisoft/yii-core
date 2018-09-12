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
 */
class Locale
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
        if (!preg_match($this->getBCP47Regex(), $localeString, $matches)) {
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

        if (!empty($matches['extendedLaguage'])) {
            $this->extendedLanguage = $matches['extendedLaguage'];
        }

        if (!empty($matches['extension'])) {
            $this->extension = $matches['extension'];
        }

        if (!empty($matches['script'])) {
            $this->script = ucfirst($matches['script']);
        }

        if (!empty($matches['grandfathered'])) {
            $this->grandfathered = $matches['grandfathered'];
        }

        if (!empty($matches['privateUse'])) {
            $this->privateUse = $matches['privateUse'];
        }
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->script;
    }

    /**
     * @return string
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function withLanguage($language): self
    {
        $clone = clone $this;
        $clone->language = $language;
        return $clone;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function withRegion($region): self
    {
        $clone = clone $this;
        $clone->region = $region;
        return $clone;
    }

    /**
     * @return mixed
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function withCurrency($currency): self
    {
        $clone = clone $this;
        $clone->currency = $currency;

        return $clone;
    }

    /**
     * @return string regular expression for parsing BCP 47
     * @see https://tools.ietf.org/html/bcp47
     */
    private function getBCP47Regex(): string
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

        if ($this->privateUse !== null) {
           $result[] = $this->privateUse;
         }

        return implode('-', $result);
    }
}
