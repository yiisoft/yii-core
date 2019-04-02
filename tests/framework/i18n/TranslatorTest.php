<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\i18n;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yii\EventDispatcher\Dispatcher;
use Yii\EventDispatcher\Provider\Provider;
use yii\i18n\event\OnMissingTranslation;
use yii\i18n\I18N;
use yii\i18n\PhpMessageSource;
use yii\i18n\Translator;
use yii\tests\TestCase;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 * @group i18n
 */
class TranslatorTest extends TestCase
{
    /**
     * @var Translator
     */
    public $translator;

    /**
     * @var I18N
     */
    private $i18n;

    private $listenerProvider;
    private $eventDispatcher;

    public function getListenerProvider(): Provider
    {
        if ($this->listenerProvider === null) {
            $this->listenerProvider = new Provider();
        }
        return $this->listenerProvider;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new Dispatcher($this->getListenerProvider());
        }
        return $this->eventDispatcher;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->setTranslator();
        $this->i18n = $this->container->get('i18n');
    }

    protected function setTranslator()
    {
        $this->translator = $this->factory->create([
            '__class' => Translator::class,
            'translations' => [
                'test' => [
                    '__class' => $this->getMessageSourceClass(),
                    'basePath' => '@yii/tests/data/i18n/messages',
                ],
            ],
        ]);
    }

    public function testDI()
    {
        $translator = $this->container->get('translator');
        $this->assertInstanceOf(Translator::class, $translator);
    }

    private function getMessageSourceClass()
    {
        return PhpMessageSource::class;
    }

    public function testTranslate()
    {
        $msg = 'The dog runs fast.';

        // source = target. Should be returned as is.
        $this->assertEquals('The dog runs fast.', $this->translator->translate('test', $msg, [], 'en-US'));

        // exact match
        $this->assertEquals('Der Hund rennt schnell.', $this->translator->translate('test', $msg, [], 'de-DE'));

        // fallback to just language code with absent exact match
        $this->assertEquals('Собака бегает быстро.', $this->translator->translate('test', $msg, [], 'ru-RU'));

        // fallback to just langauge code with present exact match
        $this->assertEquals('Hallo Welt!', $this->translator->translate('test', 'Hello world!', [], 'de-DE'));
    }

    public function testDefaultSource()
    {
        $translator = $this->factory->create([
            '__class' => Translator::class,
            'translations' => [
                '*' => [
                    '__class' => $this->getMessageSourceClass(),
                    'basePath' => '@yii/tests/data/i18n/messages',
                    'fileMap' => [
                        'test' => 'test.php',
                        'foo' => 'test.php',
                    ],
                ],
            ],
        ]);

        $msg = 'The dog runs fast.';

        // source = target. Should be returned as is.
        $this->assertEquals($msg, $translator->translate('test', $msg, [], 'en-US'));

        // exact match
        $this->assertEquals('Der Hund rennt schnell.', $translator->translate('test', $msg, [], 'de-DE'));
        $this->assertEquals('Der Hund rennt schnell.', $translator->translate('foo', $msg, [], 'de-DE'));
        $this->assertEquals($msg, $translator->translate('bar', $msg, [], 'de-DE'));

        // fallback to just language code with absent exact match
        $this->assertEquals('Собака бегает быстро.', $translator->translate('test', $msg, [], 'ru-RU'));

        // fallback to just langauge code with present exact match
        $this->assertEquals('Hallo Welt!', $translator->translate('test', 'Hello world!', [], 'de-DE'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/7964
     */
    public function testSourceLanguageFallback()
    {
        $translator = $this->factory->create([
            '__class' => Translator::class,
            'translations' => [
                '*' => [
                    '__class' => PhpMessageSource::class,
                    'basePath' => '@yii/tests/data/i18n/messages',
                    'sourceLanguage' => 'de-DE',
                    'fileMap' => [
                        'test' => 'test.php',
                        'foo' => 'test.php',
                    ],
                ],
            ],
        ]);

        $msg = 'The dog runs fast.';

        // source = target. Should be returned as is.
        $this->assertEquals($msg, $translator->translate('test', $msg, [], 'de-DE'));

        // target is less specific, than a source. Messages from sourceLanguage file should be loaded as a fallback
        $this->assertEquals('Der Hund rennt schnell.', $translator->translate('test', $msg, [], 'de'));
        $this->assertEquals('Hallo Welt!', $translator->translate('test', 'Hello world!', [], 'de'));

        // target is a different language than source
        $this->assertEquals('Собака бегает быстро.', $translator->translate('test', $msg, [], 'ru-RU'));
        $this->assertEquals('Собака бегает быстро.', $translator->translate('test', $msg, [], 'ru'));
    }

    public function testTranslateParams()
    {
        $msg = 'His speed is about {n} km/h.';
        $params = ['n' => 42];
        $this->assertEquals('His speed is about 42 km/h.', $this->translator->translate('test', $msg, $params, 'en-US'));
        $this->assertEquals('Seine Geschwindigkeit beträgt 42 km/h.', $this->translator->translate('test', $msg, $params, 'de-DE'));
    }

    public function testTranslateParams2()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl not installed. Skipping.');
        }
        $msg = 'His name is {name} and his speed is about {n, number} km/h.';
        $params = [
            'n' => 42,
            'name' => 'DA VINCI', // http://petrix.com/dognames/d.html
        ];
        $this->assertEquals('His name is DA VINCI and his speed is about 42 km/h.', $this->translator->translate('test', $msg, $params, 'en-US'));
        $this->assertEquals('Er heißt DA VINCI und ist 42 km/h schnell.', $this->translator->translate('test', $msg, $params, 'de-DE'));
    }

    public function testSpecialParams()
    {
        $msg = 'His speed is about {0} km/h.';

        $this->assertEquals('His speed is about 0 km/h.', $this->translator->translate('test', $msg, 0, 'en-US'));
        $this->assertEquals('His speed is about 42 km/h.', $this->translator->translate('test', $msg, 42, 'en-US'));
        $this->assertEquals('His speed is about {0} km/h.', $this->translator->translate('test', $msg, null, 'en-US'));
        $this->assertEquals('His speed is about {0} km/h.', $this->translator->translate('test', $msg, [], 'en-US'));
    }

    /**
     * When translation is missing source language should be used for formatting.
     *
     * @see https://github.com/yiisoft/yii2/issues/2209
     */
    public function testMissingTranslationFormatting()
    {
        $this->assertEquals('1 item', $this->translator->translate('test', '{0, number} {0, plural, one{item} other{items}}', 1, 'hu'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/7093
     */
    public function testRussianPlurals()
    {
        $this->assertEquals('На диване лежит 6 кошек!', $this->translator->translate('test', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}} lying on the sofa!', ['n' => 6], 'ru'));
    }

    /**
     * We try translating from en-US to ru-RU. In case of missing translation source string is used which is in en-US.
     * Therefore locale data used for translation should be for en-US.
     */
    public function testUsingSourceLanguageForMissingTranslation()
    {
        $targetLanguage = 'ru-RU';

        // There are only "one" and "other" in English unlike Russian where there are "one", "few", "many" and "other"
        $this->assertEquals('one', $this->translator->translate('test', '{n, plural, one{one} few{few} many{many} other{other}}', ['n' => 1], $targetLanguage));
        $this->assertEquals('other', $this->translator->translate('test', '{n, plural, one{one} few{few} many{many} other{other}}', ['n' => 2], $targetLanguage));
        $this->assertEquals('other', $this->translator->translate('test', '{n, plural, one{one} few{few} many{many} other{other}}', ['n' => 5], $targetLanguage));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/2519
     */
    public function testMissingTranslationEvent()
    {
        $this->assertEquals('Hallo Welt!', $this->translator->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->translator->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->translator->translate('test', 'Hello world!', [], 'de-DE'));

        $listenerProvider = $this->getListenerProvider();

        $listenerProvider->attach(function (OnMissingTranslation $missingTranslation) {
            // do nothing
        });

        $this->assertEquals('Hallo Welt!', $this->translator->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->translator->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->translator->translate('test', 'Hello world!', [], 'de-DE'));
        $listenerProvider->detach(OnMissingTranslation::class);

        $listenerProvider->attach(function (OnMissingTranslation $missingTranslation) {
            if ($missingTranslation->id() === 'New missing translation message.') {
                $missingTranslation->setFallback('TRANSLATION MISSING HERE!');
            }
        });

        $this->assertEquals('Hallo Welt!', $this->translator->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Another missing translation message.', $this->translator->translate('test', 'Another missing translation message.', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->translator->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('TRANSLATION MISSING HERE!', $this->translator->translate('test', 'New missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->translator->translate('test', 'Hello world!', [], 'de-DE'));

        $listenerProvider->detach(OnMissingTranslation::class);
    }

    public function sourceLanguageDataProvider()
    {
        return [
            ['en-GB'],
            ['en'],
        ];
    }

    /**
     * @dataProvider sourceLanguageDataProvider
     * @param $sourceLanguage
     * TODO: FIXME
     */
    public function tmpoff_testIssue11429($sourceLanguage)
    {
        $this->destroyApplication();
        $this->mockApplication();
        $this->setTranslator();

        $this->app->sourceLanguage = $sourceLanguage;
        $logger = $this->app->getLogger();
        $logger->messages = [];
        $filter = function ($array) {
            // Ensures that error message is related to PhpMessageSource
            $className = $this->getMessageSourceClass();
            return substr_compare($array[2]['category'], $className, 0, strlen($className)) === 0;
        };

        $this->assertEquals('The dog runs fast.', $this->translator->translate('test', 'The dog runs fast.', [], 'en-GB'));
        $this->assertEquals([], array_filter($logger->messages, $filter));

        $this->assertEquals('The dog runs fast.', $this->translator->translate('test', 'The dog runs fast.', [], 'en'));
        $this->assertEquals([], array_filter($logger->messages, $filter));

        $this->assertEquals('The dog runs fast.', $this->translator->translate('test', 'The dog runs fast.', [], 'en-CA'));
        $this->assertEquals([], array_filter($logger->messages, $filter));

        $this->assertEquals('The dog runs fast.', $this->translator->translate('test', 'The dog runs fast.', [], 'hz-HZ'));
        $this->assertCount(1, array_filter($logger->messages, $filter));
        $logger->messages = [];

        $this->assertEquals('The dog runs fast.', $this->translator->translate('test', 'The dog runs fast.', [], 'hz'));
        $this->assertCount(1, array_filter($logger->messages, $filter));
        $logger->messages = [];
    }

    /**
     * Formatting a message that contains params but they are not provided.
     * @see https://github.com/yiisoft/yii2/issues/10884
     */
    public function testFormatMessageWithNoParam()
    {
        $message = 'Incorrect password (length must be from {min, number} to {max, number} symbols).';
        $expected = 'Incorrect password (length must be from {min} to {max} symbols).';
        $this->assertEquals($expected, $this->translator->format($message, ['attribute' => 'password'], 'en'));
    }
}
