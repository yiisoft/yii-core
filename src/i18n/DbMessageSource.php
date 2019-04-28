<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yiisoft\Cache\CacheInterface;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use Yiisoft\Arrays\ArrayHelper;
use yii\helpers\Yii;

/**
 * DbMessageSource extends [[MessageSource]] and represents a message source that stores translated
 * messages in database.
 *
 * The database must contain the following two tables: source_message and message.
 *
 * The `source_message` table stores the messages to be translated, and the `message` table stores
 * the translated messages. The name of these two tables can be customized by setting [[sourceMessageTable]]
 * and [[messageTable]], respectively.
 *
 * The database connection is specified by [[db]]. Database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/i18n/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * @author resurtm <resurtm@gmail.com>
 */
class DbMessageSource extends MessageSource
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     *
     * After the DbMessageSource object is created, if you want to change this property, you should only assign
     * it with a DB connection object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var CacheInterface|array|string the cache object or the application component ID of the cache object.
     * The messages data will be cached using this cache object.
     * Note, that to enable caching you have to set [[enableCaching]] to `true`, otherwise setting this property has no effect.
     *
     * After the DbMessageSource object is created, if you want to change this property, you should only assign
     * it with a cache object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     * @see cachingDuration
     * @see enableCaching
     */
    public $cache = 'cache';
    /**
     * @var string the name of the source message table.
     */
    public $sourceMessageTable = '{{%source_message}}';
    /**
     * @var string the name of the translated message table.
     */
    public $messageTable = '{{%message}}';
    /**
     * @var int the time in seconds that the messages can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCaching
     */
    public $cachingDuration = 0;
    /**
     * @var bool whether to enable caching translated messages
     */
    public $enableCaching = false;

    private function getConnection(): Connection
    {
        if (!$this->db instanceof Connection) {
            $this->db = Yii::ensureObject($this->db, Connection::class);
        }
        return $this->db;
    }

    private function getCache(): CacheInterface
    {
        if (!$this->cache instanceof CacheInterface) {
            $this->cache = Yii::ensureObject($this->cache, CacheInterface::class);
        }
        return $this->cache;
    }

    /**
     * Loads the message translation for the specified language and category.
     * If translation for specific locale code such as `en-US` isn't found it
     * tries more generic `en`.
     *
     * @param string $category the message category
     * @param string $language the target language
     *
     * @return array the loaded messages. The keys are original messages, and the values
     * are translated messages.
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \yii\db\Exception
     */
    protected function loadMessages($category, $language): array
    {
        if ($this->enableCaching) {
            $key = [
                __CLASS__,
                $category,
                $language,
            ];
            $messages = $this->getCache()->get($key);
            if ($messages === false) {
                $messages = $this->loadMessagesFromDb($category, $language);
                $this->getCache()->set($key, $messages, $this->cachingDuration);
            }

            return $messages;
        }

        return $this->loadMessagesFromDb($category, $language);
    }

    /**
     * Loads the messages from database.
     * You may override this method to customize the message storage in the database.
     * @param string $category the message category.
     * @param string $language the target language.
     * @return array the messages loaded from database.
     * @throws \yii\db\Exception
     */
    protected function loadMessagesFromDb($category, $language)
    {
        $mainQuery = (new Query())->select(['message' => 't1.message', 'translation' => 't2.translation'])
            ->from(['t1' => $this->sourceMessageTable, 't2' => $this->messageTable])
            ->where([
                't1.id' => new Expression('[[t2.id]]'),
                't1.category' => $category,
                't2.language' => $language,
            ]);

        $fallbackLanguage = substr($language, 0, 2);
        $fallbackSourceLanguage = substr($this->sourceLanguage, 0, 2);

        if ($fallbackLanguage !== $language) {
            $mainQuery->union($this->createFallbackQuery($category, $language, $fallbackLanguage), true);
        } elseif ($language === $fallbackSourceLanguage) {
            $mainQuery->union($this->createFallbackQuery($category, $language, $fallbackSourceLanguage), true);
        }

        $messages = $mainQuery->createCommand($this->getConnection())->queryAll();

        return ArrayHelper::map($messages, 'message', 'translation');
    }

    /**
     * The method builds the [[Query]] object for the fallback language messages search.
     * Normally is called from [[loadMessagesFromDb]].
     *
     * @param string $category the message category
     * @param string $language the originally requested language
     * @param string $fallbackLanguage the target fallback language
     * @return Query
     * @see loadMessagesFromDb
     * @since 2.0.7
     */
    protected function createFallbackQuery($category, $language, $fallbackLanguage)
    {
        return (new Query())->select(['message' => 't1.message', 'translation' => 't2.translation'])
            ->from(['t1' => $this->sourceMessageTable, 't2' => $this->messageTable])
            ->where([
                't1.id' => new Expression('[[t2.id]]'),
                't1.category' => $category,
                't2.language' => $fallbackLanguage,
            ])->andWhere([
                'NOT IN', 't2.id', (new Query())->select('[[id]]')->from($this->messageTable)->where(['language' => $language]),
            ]);
    }
}
