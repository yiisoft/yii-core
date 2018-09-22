<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * Encoding stores character encoding.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
interface EncodingInterface
{
    /**
     * @return string
     */
    public function asString(): string;

    /**
     * @return self
     */
    public function withEncoding(?string $language): self;
}
