<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * I18N interface.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
interface I18NInterface
{
    /**
     * @return LocaleInterface
     */
    public function getLocale(): LocaleInterface;

    /**
     * @return self
     */
    public function setLocale(LocaleInterface $locale): self;

    /**
     * @return string
     */
    public function getEncoding(): string;

    /**
     * @return self
     */
    public function setEncoding(string $language): self;

    /**
     * Returns the time zone set for this i18n.
     * @return string the time zone.
     */
    public function getTimeZone(): string;

    /**
     * Sets the time zone for this i18n.
     * @param self
     */
    public function setTimeZone(string $timezone): self;
}
