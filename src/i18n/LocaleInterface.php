<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * Locale stores locale information created from BCP 47 formatted string
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
interface LocaleInterface
{
    /**
     * @return string
     */
    public function asString(): string;

    /**
     * @return string|null Two-letter ISO-639-2 language code
     * @see http://www.loc.gov/standards/iso639-2/
     */
    public function getLanguage(): ?string;

    /**
     * @param null|string $language Two-letter ISO-639-2 language code
     * @see http://www.loc.gov/standards/iso639-2/
     * @return self
     */
    public function withLanguage(?string $language): self;

    /**
     * @return string Two-letter ISO 3166-1 country code
     * @see https://www.iso.org/iso-3166-country-codes.html
     */
    public function getRegion(): ?string;

    /**
     * @param null|string $region Two-letter ISO 3166-1 country code
     * @see https://www.iso.org/iso-3166-country-codes.html
     * @return self
     */
    public function withRegion(?string $region): self;

    /**
     * @return string Four-letter ISO 15924 script code
     * @see http://www.unicode.org/iso15924/iso15924-codes.html
     */
    public function getScript(): ?string;

    /**
     * @param null|string $script Four-letter ISO 15924 script code
     * @see http://www.unicode.org/iso15924/iso15924-codes.html
     * @return self
     */
    public function withScript(?string $script): self;

    /**
     * @return string ICU currency
     */
    public function getCurrency(): ?string;

    /**
     * @param null|string $currency ICU currency
     * @return self
     */
    public function withCurrency(?string $currency): self;

    /**
     * @return string variant of language conventions to use
     */
    public function getVariant(): ?string;

    /**
     * @param null|string $variant variant of language conventions to use
     * @return self
     */
    public function withVariant(?string $variant): self;

    /**
     * @return null|string ICU calendar
     */
    public function getCalendar(): ?string;

    /**
     * @param null|string $calendar ICU calendar
     * @return self
     */
    public function withCalendar(?string $calendar): self;

    /**
     * @return null|string ICU collation
     */
    public function getCollation(): ?string;

    /**
     * @param null|string $collation ICU collation
     * @return self
     */
    public function withCollation(?string $collation): self;

    /**
     * @return null|string ICU numbers
     */
    public function getNumbers(): ?string;

    /**
     * @param null|string $numbers ICU numbers
     * @return self
     */
    public function withNumbers(?string $numbers): self;

    /**
     * @return null|string extended language subtags
     */
    public function getExtendedLanguage(): ?string;

    /**
     * @param null|string $extendedLanguage extended language subtags
     * @return self
     */
    public function withExtendedLanguage(?string $extendedLanguage): self;

    /**
     * @return null|string
     */
    public function getPrivate(): ?string;

    /**
     * @param null|string $private
     * @return self
     */
    public function withPrivate(?string $private): self;


    /**
     * Returns fallback locale
     *
     * @return self fallback locale
     */
    public function getFallbackLocale(): self;
}
