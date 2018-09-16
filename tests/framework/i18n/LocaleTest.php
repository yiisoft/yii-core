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
    public function testBasic()
    {
        $locale = new Locale('rU-Ua');
        static::assertSame('UA', $locale->getRegion());
        static::assertSame('ru', $locale->getLanguage());
        static::assertSame('ru-UA', (string)$locale);
    }

    public function testInvalidInput()
    {
        $this->expectException(InvalidConfigException::class);
        new Locale('_invalid');
    }

    public function testExtendedLanguage()
    {
        $localeString = 'zh-cmn-Hans-CN';
        $locale = new Locale($localeString);

        static::assertSame('zh', $locale->getLanguage());
        static::assertSame('cmn', $locale->getExtendedLanguage());
        static::assertSame('Hans', $locale->getScript());
        static::assertSame('CN', $locale->getRegion());
        static::assertSame($localeString, (string)$locale);
    }

    public function testVariant()
    {
        $localeString = 'hy-Latn-IT-AREVELA';
        $locale = new Locale($localeString);

        static::assertSame('hy', $locale->getLanguage());
        static::assertSame('Latn', $locale->getScript());
        static::assertSame('IT', $locale->getRegion());
        static::assertSame('AREVELA', $locale->getVariant());
        static::assertSame($localeString, (string)$locale);
    }

    public function testPrivate()
    {
        $localeString = 'az-Arab-AZ-x-phonebk';
        $locale = new Locale($localeString);

        $this->assertSame('az', $locale->getLanguage());
        $this->assertSame('Arab', $locale->getScript());
        $this->assertSame('AZ', $locale->getRegion());
        $this->assertSame('x-phonebk', $locale->getPrivate());
        static::assertSame($localeString, (string)$locale);
    }

    public function testKeywords()
    {
        $localeString = 'sr-Latn-RS-REVISED@currency=USD;collation=c;calendar=d;numbers=e';
        $locale = new Locale($localeString);

        $this->assertSame('sr', $locale->getLanguage());
        $this->assertSame('Latn', $locale->getScript());
        $this->assertSame('RS', $locale->getRegion());
        $this->assertSame('REVISED', $locale->getVariant());
        $this->assertSame('USD', $locale->getCurrency());
        $this->assertSame('c', $locale->getCollation());
        $this->assertSame('d', $locale->getCalendar());
        $this->assertSame('e', $locale->getNumbers());

        static::assertSame($localeString, (string)$locale);
    }

    public function longLanguageDataProvider()
    {
        return [
            ['aaaa'],
            ['aaaaa'],
            ['aaaaaa'],
            ['aaaaaaa'],
            ['aaaaaaaa'],
        ];
    }

    /**
     * @dataProvider longLanguageDataProvider
     */
    public function testLongLanguage($localeString)
    {
        $locale = new Locale($localeString);
        $this->assertSame($localeString, $locale->getLanguage());
        static::assertSame($localeString, (string)$locale);
    }
}
