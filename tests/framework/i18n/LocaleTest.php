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

    public function testParseLocale()
    {
        foreach ([false, true] as $forceFallback) {
            // $this->checkParseLocaleExtendedLanguage($forceFallback);
            $this->checkParseLocaleVariant($forceFallback);
            // $this->checkParseLocalePrivate($forceFallback);
        }
    }

    public function checkParseLocaleExtendedLanguage(bool $forceFallback)
    {
        $locale = 'zh-cmn-Hans-CN';
        $subtags = Locale::parseLocale($locale, $forceFallback);
        var_dump($subtags);die;
        $this->assertSame('zh',         $subtags['language']);
        $this->assertSame('cmn',        $subtags['extendedLanguage']);
        $this->assertSame('Hans',       $subtags['script']);
        $this->assertSame('CN',         $subtags['region']);
        $this->assertSame($locale, Locale::composeLocale($subtags, $forceFallback));
    }

    public function checkParseLocaleVariant(bool $forceFallback)
    {
        $locale = 'hy-Latn-IT-AREVELA';
        $subtags = Locale::parseLocale($locale, $forceFallback);
        $this->assertSame('hy',         $subtags['language']);
        $this->assertSame('Latn',       $subtags['script']);
        $this->assertSame('IT',         $subtags['region']);
        $this->assertSame('AREVELA',    $subtags['variant']);
        $this->assertSame($locale, Locale::composeLocale($subtags, $forceFallback));
    }

    public function checkParseLocalePrivate(bool $forceFallback)
    {
        $locale = 'az-Arab-AZ-x-phonebk';
        $subtags = Locale::parseLocale($locale, $forceFallback);
        $this->assertSame('az',         $subtags['language']);
        $this->assertSame('Arab',       $subtags['script']);
        $this->assertSame('AZ',         $subtags['region']);
        $this->assertSame('x-phonebk',  $subtags['privateUse']);
        $this->assertSame($locale, Locale::composeLocale($subtags, $forceFallback));
    }
}
