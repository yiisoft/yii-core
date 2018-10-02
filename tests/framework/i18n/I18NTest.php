<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\i18n;

use yii\helpers\Yii;
use yii\base\Event;
use yii\i18n\Translation;
use yii\i18n\PhpMessageSource;
use yii\i18n\TranslationEvent;
use yii\tests\TestCase;

/**
 * @author Andrii Vasyliev <sol@hiqdev.com>
 * @since 2.0
 * @group i18n
 */
class I18NTest extends TestCase
{
    public function setUp()
    {
        $this->i18n = $this->container->get('i18n');
    }

    public function testGetCurrencySymbol()
    {
        $this->i18n->setLocale('de-DE');
        $this->assertSame('€', $this->i18n->getCurrencySymbol('EUR'));
        $this->assertSame('€', $this->i18n->getCurrencySymbol());

        $this->i18n->setLocale('ru-RU');
        $this->assertIsOneOf($this->i18n->getCurrencySymbol('RUR'), ['р.', '₽', 'руб.']);
        $this->assertIsOneOf($this->i18n->getCurrencySymbol(), ['р.', '₽', 'руб.']);
    }
}
