<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\i18n;

use m150207_210500_i18n_init;
use Yiisoft\Cache\ArrayCache;
use yii\db\Connection;
use yii\db\Migration;
use yii\i18n\DbMessageSource;
use yii\i18n\MessageSource;

class DbMessageSourceTest extends MessageSourceTest
{
    private $connection;

    private function getMigration(Connection $connection): Migration
    {
        require_once \dirname(__DIR__, 3) . '/src/i18n/migrations/m150207_210500_i18n_init.php';
        $migration = new m150207_210500_i18n_init();
        $migration->db = $connection;
        return $migration;
    }

    private function getConnection(): Connection
    {
        if ($this->connection === null) {
            $this->connection = new Connection();
            $this->connection->dsn = 'sqlite::memory:';
            ob_start();
            $this->getMigration($this->connection)->up();
            ob_end_clean();
        }

        return $this->connection;
    }

    protected function getMessageSource($sourceLanguage, $forceTranslation): MessageSource
    {
        $cache = new ArrayCache();
        $messageSource = new DbMessageSource();
        $messageSource->cache = $cache;
        $messageSource->db = $this->getConnection();
        return $messageSource;
    }

    protected function prepareTranslations(TranslationsCollection $translationsCollection)
    {
        $connection = $this->getConnection();

        $sourceMessages = [];
        $messages = [];

        $pk = 1;
        foreach ($translationsCollection->all() as $translation) {
            $sourceMessages[] = [$pk, $translation->getCategory(), $translation->getMessage()];
            $messages[] = [$pk, $translation->getLanguage(), $translation->getTranslation()];
            $pk++;
        }

        $connection->createCommand()->batchInsert('source_message', ['id', 'category', 'message'], $sourceMessages)->execute();
        $connection->createCommand()->batchInsert('message', ['id', 'language', 'translation'], $messages)->execute();
    }
}
