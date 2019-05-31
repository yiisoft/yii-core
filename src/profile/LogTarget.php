<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\profile;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use yii\helpers\Yii;
use yii\log\Logger;

/**
 * LogTarget saves profiling messages as a log messages.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'profiler' => [
 *         'targets' => [
 *             [
 *                 '__class' => yii\profile\LogTarget::class,
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 */
class LogTarget extends Target
{
    /**
     * @var string log level to be used for messages export.
     */
    public $logLevel = LogLevel::DEBUG;

    /**
     * @var LoggerInterface logger to be used for message export.
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function export(array $messages)
    {
        $logger = $this->getLogger();
        foreach ($messages as $message) {
            $message['time'] = $message['beginTime'];
            $logger->log($this->logLevel, $message['token'], $message);
        }
    }
}
