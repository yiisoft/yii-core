<?php
namespace yii\tests\framework\i18n;

use yii\i18n\MessageSource;
use yii\i18n\TranslationEvent;

abstract class MessageSourceTest extends \PHPUnit\Framework\TestCase
{
    abstract protected function getMessageSource($sourceLanguage, $forceTranslation): MessageSource;
    abstract protected function prepareTranslations(TranslationsCollection $translationsCollection);

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

        $translationMissing = false;
        $messageSource->on(TranslationEvent::MISSING, function () use (&$translationMissing) {
            $translationMissing = true;
        });
        
        $result = $messageSource->translate('test', 'There is no such message', 'ru_RU');

        self::assertTrue($translationMissing);
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
}
