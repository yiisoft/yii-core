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
    public function getId(): string;

    /**
     * @return string
     */
    public function getLanguage(): string;

    /**
     * @return string
     */
    public function getRegion(): string;

    /**
     * @return string
     */
    public function getScript(): string;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @return string
     */
    public function getVariant(): string;

    /**
     * @return LocaleInterface
     */
    public function withLanguage(string $language): self;

    /**
     * @return LocaleInterface
     */
    public function withRegion(string $region): self;

    /**
     * @return LocaleInterface
     */
    public function withCurrency(string $currency): self;
}
