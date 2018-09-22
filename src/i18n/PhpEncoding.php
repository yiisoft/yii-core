<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * This encoding implementation stores charset encoding
 * in PHP `default_charset` configuration option.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class PhpEncoding implements EncodingInterface
{
    /**
     * @param string $encoding
     */
    public function __construct(string $encoding)
    {
        $this->setEncoding($encoding);
    }

    /**
     * {@inheritdoc}
     */
    public function withEncoding(?string $encoding): EncodingInterface
    {
        return $this->setEncoding($encoding);
    }

    private function setEncoding(?string $encoding): EncodingInterface
    {
        if ($encoding) {
            ini_set('default_charset', $encoding);
            mb_internal_encoding($encoding);
        }

        return $this;
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
        return mb_internal_encoding();
    }
}
