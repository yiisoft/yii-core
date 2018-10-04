<?php


namespace yii\tests\framework\i18n;


class Translation
{
    private $language;
    private $category;
    private $message;
    private $translation;

    /**
     * Translation constructor.
     * @param $language
     * @param $category
     * @param $message
     * @param $translation
     */
    public function __construct(string $language, string $category, string $message, string $translation)
    {
        $this->language = $language;
        $this->category = $category;
        $this->message = $message;
        $this->translation = $translation;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTranslation(): string
    {
        return $this->translation;
    }
}
