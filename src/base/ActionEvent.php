<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionEvent represents the event parameter used for an action event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionEvent extends Event
{
    /**
     * @event raised before executing a controller action.
     * You may set [[ActionEvent::isValid]] to `false` to cancel the action execution.
     */
    const BEFORE = 'beforeAction';
    /**
     * @event raised after executing a controller action.
     */
    const AFTER = 'afterAction';

    /**
     * @param string $name event name
     * @param Action $action the action associated with this event.
     */
    public function __construct(string $name, Action $action)
    {
        parent::__construct($name, $action);
    }

    /**
     * Creates AFTER_RUN event with result.
     * @param Action $action the action this event is fired on.
     * @param mixed $result action result.
     * @return self created event
     */
    public static function before(Action $action): self
    {
        return new static(static::BEFORE, $action);
    }

    /**
     * Creates AFTER_RUN event with result.
     * @param Action $action the action this event is fired on.
     * @param mixed $result action result.
     * @return self created event
     */
    public static function after(Action $action, $result): self
    {
        return (new static(static::AFTER, $action))->setResult($result);
    }

    /**
     * @return Action the action associated with this event.
     */
    public function getAction(): Action
    {
        return $this->getTarget();
    }
}
