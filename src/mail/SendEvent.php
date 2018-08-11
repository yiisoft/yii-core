<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use yii\base\Event;

/**
 * SendEvent represents the event triggered by [[BaseMailer]].
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class SendEvent extends Event
{
    /**
     * @event raised right before send.
     * You may set [[isValid]] to be false to cancel the send.
     */
    const BEFORE = 'mail.send.before';
    /**
     * @event raised right after send.
     */
    const AFTER = 'mail.send.after';

    /**
     * @var bool if message was sent successfully.
     */
    public $isSuccessful;

    /**
     * @param string $name event name
     * @param MessageInterface $message the message associated with this event.
     */
    public function __construct(string $name, MessageInterface $message, bool $isSuccessful = null)
    {
        parent::__construct($name, $message);
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * Creates BEFORE event with mesage.
     * @param MessageInterface $message the message this event is fired on.
     * @return self created event.
     */
    public static function before(MessageInterface $message): self
    {
        return new static(static::BEFORE, $message);
    }

    /**
     * Creates AFTER event with isSuccessful flag.
     * @param MessageInterface $message the message this event is fired on.
     * @return self created event.
     */
    public static function after(MessageInterface $message, bool $isSuccessful): self
    {
        return (new static(static::AFTER, $message, $isSuccessful));
    }

    /**
     * @return MessageInterface the message being send.
     */
    public function getMessage(): MessageInterface
    {
        return $this->getTarget();
    }
}
