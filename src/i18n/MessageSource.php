<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Component;

/**
 * MessageSource is the base class for message translation repository classes.
 *
 * A message source stores message translations in some persistent storage.
 *
 * Child classes should override {@see MessageSource::loadMessages()} to provide translated messages.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class MessageSource extends Component
{
    /**
     * @var bool whether to force message translation when the source and target languages are the same.
     * Defaults to false, meaning translation is only performed when source and target languages are different.
     */
    public $forceTranslation = false;
    /**
     * @var string the language that the original messages are in. If not set, it will use the value of
     * {@see \yii\base\Application::sourceLanguage}.
     */
    public $sourceLanguage = 'en-US';

    private $_messages = [];


    /**
     * Loads the message translation for the specified language and category.
     * If translation for specific locale code such as `en-US` isn't found it
     * tries more generic `en`.
     *
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages. The keys are original messages, and the values
     * are translated messages.
     */
    abstract protected function loadMessages($category, $language): array;

    /**
     * Returns all messages for a given category in a given language.
     * Returned value is a result of {@see loadMessages()}
     *
     * @param string $category
     * @param string $language
     *
     * @return array
     */
    public function getMessages($category, $language)
    {
        $key = $language . '/' . $category;
        if (!isset($this->_messages[$key])) {
            $this->_messages[$key] = $this->loadMessages($category, $language);
        }

        return $this->_messages[$key];
    }

    /**
     * Translates a message to the specified language.
     *
     * Note that unless {@see forceTranslation} is true, if the target language
     * is the same as the {@see sourceLanguage|source language}, the message
     * will NOT be translated.
     *
     * If a translation is not found, a {@see TranslationEvent::MISSING|missingTranslation} event will be triggered.
     *
     * @param string $category the message category
     * @param string $message the message to be translated
     * @param string $language the target language
     * @return string|null the translated message or false if translation wasn't found or isn't required
     */
    public function translate($category, $message, $language): ?string
    {
        if ($this->forceTranslation || $language !== $this->sourceLanguage) {
            return $this->translateMessage($category, $message, $language);
        }

        return null;
    }

    /**
     * Translates the specified message.
     * If the message is not found, a {@see TranslationEvent::MISSING|missingTranslation} event will be triggered.
     * If there is an event handler, it may provide a {@see MissingTranslationEvent::$translatedMessage|fallback translation}.
     * If no fallback translation is provided this method will return `false`.
     * @param string $category the category that the message belongs to.
     * @param string $message the message to be translated.
     * @param string $language the target language.
     * @return string|null the translated message or null if translation wasn't found.
     */
    protected function translateMessage($category, $message, $language): ?string
    {
        $messages = $this->getMessages($category, $language);
        if (isset($messages[$message]) && $messages[$message] !== '') {
            return $messages[$message];
        }
        if ($this->hasEventHandlers(TranslationEvent::MISSING)) {
            $event = TranslationEvent::missing($category, $message, $language);
            $this->trigger($event);
            if ($event->translatedMessage !== null) {
                return $messages[$message] = $event->translatedMessage;
            }
        }

        return $messages[$message] = null;
    }
}
