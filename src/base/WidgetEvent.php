<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * WidgetEvent represents the event parameter used for a widget event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the widget.
 *
 * @author Petra Barus <petra.barus@gmail.com>
 * @since 2.0.11
 */
class WidgetEvent extends Event
{
    /**
     * @event Event an event that is triggered when the widget is initialized via [[init()]].
     * @since 2.0.11
     */
    const BEFORE_INIT = 'init';
    /**
     * @event WidgetEvent an event raised right before executing a widget.
     * You may set [[WidgetEvent::isValid]] to be false to cancel the widget execution.
     * @since 2.0.11
     */
    const BEFORE_RUN = 'beforeRun';
    /**
     * @event WidgetEvent an event raised right after executing a widget.
     * @since 2.0.11
     */
    const AFTER_RUN = 'afterRun';

    /**
     * Creates BEFORE_RUN event.
     * @return self created event
     */
    public static function beforeRun(): self
    {
        return new static(static::BEFORE_RUN);
    }

    /**
     * Creates AFTER_RUN event with result.
     * @param mixed $result widget return result.
     * @return self created event
     */
    public static function afterRun($result): self
    {
        return (new static(static::AFTER_RUN))->setResult($result);
    }
}
