<?php
namespace yii\tests\framework\i18n;

use yii\base\Aliases;
use yii\helpers\FileHelper;
use yii\i18n\MessageSource;
use yii\i18n\PhpMessageSource;

class PhpMessageSourceTest extends MessageSourceTest
{
    private static function getMessagesPath()
    {
        return sys_get_temp_dir() . '/yii_messages';
    }

    protected function getMessageSource($sourceLanguage, $forceTranslation): MessageSource
    {
        $aliases = new Aliases();
        $phpMessageSource = new PhpMessageSource($aliases);
        $phpMessageSource->basePath = self::getMessagesPath();
        return $phpMessageSource;
    }

    protected function prepareTranslations(TranslationsCollection $translationsCollection)
    {
        $files = [];
        foreach ($translationsCollection->all() as $translation) {
            $fileName = $translation->getLanguage() . '/' . $translation->getCategory() . '.php';
            if (!isset($files[$fileName])) {
                $files[$fileName] = [];
            }

            $files[$fileName][$translation->getMessage()] = $translation->getTranslation();
        }

        foreach ($files as $fileName => $data) {
            $filePath = self::getMessagesPath() . '/' . $fileName;
            $fileContent = "<?php\nreturn " . var_export($data, true) . ';';
            FileHelper::createDirectory(\dirname($filePath));

            file_put_contents($filePath, $fileContent);
        }
    }

    public static function tearDownAfterClass()
    {
        FileHelper::removeDirectory(self::getMessagesPath());
    }
}