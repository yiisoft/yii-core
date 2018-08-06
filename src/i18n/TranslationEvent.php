<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Event;

/**
 * TranslationEvent represents the parameter for the [[MessageSource::EVENT_MISSING_TRANSLATION]] event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TranslationEvent extends Event
{
    /**
     * @event triggered when a message translation is not found.
     */
    const MISSING = 'translation.missing';

    /**
     * @var string the message to be translated. An event handler may use this to provide a fallback translation
     * and set [[translatedMessage]] if possible.
     */
    public $message;
    /**
     * @var string the translated message. An event handler may overwrite this property
     * with a translated version of [[message]] if possible. If not set (null), it means the message is not translated.
     */
    public $translatedMessage;
    /**
     * @var string the category that the message belongs to
     */
    public $category;
    /**
     * @var string the language ID (e.g. en-US) that the message is to be translated to
     */
    public $language;

    /**
     * @param string $name event name
     * @param string $category message category
     * @param string $message the translated message
     * @param string $language the language ID e.g. en-US
     */
    public function __construct(string $name, string $category, $message, string $language)
    {
        parent::__construct($name);
        $this->category = $category;
        $this->message = $message;
        $this->language = $language;
    }

    /**
     * Create MISSING translation event.
     * @param string $category message category
     * @param string $message the translated message
     * @param string $language the language ID e.g. en-US
     */
    public static function missing(string $category, $message, string $language)
    {
        return new static(static::MISSING, $category, $message, $language);
    }
}
