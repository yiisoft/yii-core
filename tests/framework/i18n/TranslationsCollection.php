<?php


namespace yii\tests\framework\i18n;

class TranslationsCollection
{
    /**
     * @var Translation[]
     */
    private $translations = [];

    public function addTranslation(Translation $translation): void
    {
        $this->translations[] = $translation;
    }

    /**
     * @return Translation[]
     */
    public function all(): array
    {
        return $this->translations;
    }
}
