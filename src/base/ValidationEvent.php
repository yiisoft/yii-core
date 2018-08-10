<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ValidationEvent represents [[Model]] validation events.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 3.0
 */
class ValidationEvent extends Event
{
    /**
     * @event event raised at the beginning of [[validate()]]. You may set
     * [[Event::isValid]] to be false to stop the validation.
     */
    const BEFORE = 'model.validation.before';
    /**
     * @event event raised at the end of [[validate()]]
     */
    const AFTER = 'model.validation.after';
}
