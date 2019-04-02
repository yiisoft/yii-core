<?php


namespace yii\i18n\event;

class OnMissingTranslation
{
    private $category;
    private $language;
    private $id;

    /**
     * @var string
     */
    private $fallback;

    public function __construct(string $category, string $language, string $id)
    {
        $this->category = $category;
        $this->language = $language;
        $this->id = $id;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function language(): string
    {
        return $this->language;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function setFallback(string $translation): void
    {
        $this->fallback = $translation;
    }

    public function hasFallback(): bool
    {
        return $this->fallback !== null;
    }

    public function fallback(): string
    {
        return $this->fallback;
    }
}
