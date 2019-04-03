<?php
namespace yii\tests\framework\i18n;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yii\EventDispatcher\Dispatcher;
use Yii\EventDispatcher\Provider\Provider;
use yii\i18n\event\OnMissingTranslation;
use yii\i18n\MessageSource;

abstract class MessageSourceTest extends \PHPUnit\Framework\TestCase
{
    abstract protected function getMessageSource($sourceLanguage, $forceTranslation): MessageSource;
    abstract protected function prepareTranslations(TranslationsCollection $translationsCollection);

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

    public function testSameLanguagesNoTranslation()
    {
        $language = 'en_US';
        $message = 'This message is not translated.';

        $messageSource = $this->getMessageSource($language, false);
        $result = $messageSource->translate('test', $message, $language);

        self::assertNull($result);
    }

    public function testSameLanguagesTranslatedWithForceTranslation()
    {
        $language = 'en_US';
        $category = 'test';
        $message = 'This message will be translated.';
        $translation = 'This message is translated.';
        $translations = new TranslationsCollection();
        $translations->addTranslation(new Translation($language, 'test', $message, $translation));
        $this->prepareTranslations($translations);

        $messageSource = $this->getMessageSource($language, true);
        $actualMessage = $messageSource->translate($category, $message, $language);

        self::assertSame($translation, $actualMessage);
    }

    public function testMissingTranslation()
    {
        $messageSource = $this->getMessageSource('en_US', false);

        $isMissing = false;
        $this->getListenerProvider()->attach(function (OnMissingTranslation $missingTranslation) use (&$isMissing) {
            $isMissing = true;
        });
        
        $result = $messageSource->translate('test', 'There is no such message', 'ru_RU');

        self::assertTrue($isMissing);
        self::assertNull($result);
    }

    public function testLanguageFallback()
    {
        $language = 'en_US';
        $targetLanguage = 'de_DE';
        $fallbackLanguage = 'de';
        $category = 'test';
        $message = 'This message will be translated.';

        $translation = 'This message is translated.';
        $translations = new TranslationsCollection();
        $translations->addTranslation(new Translation($fallbackLanguage, 'test', $message, $translation));
        $this->prepareTranslations($translations);

        $messageSource = $this->getMessageSource($language, true);
        $actualMessage = $messageSource->translate($category, $message, $targetLanguage);

        self::assertSame($translation, $actualMessage);
    }

    public function testGetMessages()
    {
        $language = 'en_US';
        $targetLanguage = 'de_DE';
        $fallbackLanguage = 'de';
        $category = 'test';
        $messages = [
            'This message will be translated.' => 'This message is translated.',
            'The second message to be translated.' => 'The second translated message',
        ];

        $translations = new TranslationsCollection();
        foreach ($messages as $source => $translated) {
            $translations->addTranslation(new Translation($fallbackLanguage, 'test', $source, $translated));
        }
        $this->prepareTranslations($translations);

        $messageSource = $this->getMessageSource($language, true);
        $messagesTranslated = $messageSource->getMessages($category, $targetLanguage);

        self::assertSame($messages, $messagesTranslated);
    }
}
