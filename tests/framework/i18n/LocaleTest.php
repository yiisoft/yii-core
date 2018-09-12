<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\i18n;

use yii\exceptions\InvalidConfigException;
use yii\i18n\Locale;
use yii\tests\TestCase;

/**
 * @group i18n
 */
class LocaleTest extends TestCase
{
    public function testParse()
    {
        $locale = new Locale('rU-Ua');
        static::assertEquals('UA', $locale->getRegion());
        static::assertEquals('ru', $locale->getLanguage());
        static::assertEquals('ru-UA', (string)$locale);
    }

    public function testInvalidInput()
    {
        $this->expectException(InvalidConfigException::class);
        new Locale('_invalid');
    }
}
