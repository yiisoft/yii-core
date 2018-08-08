<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelEvent represents the parameter needed by [[Model]] events.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelEvent extends Event
{
    /**
     * @event event raised at the beginning of [[validate()]]. You may set
     * [[ModelEvent::isValid]] to be false to stop the validation.
     */
    const BEFORE_VALIDATE = 'model.validate.before';
    /**
     * @event event raised at the end of [[validate()]]
     */
    const AFTER_VALIDATE = 'model.validate.after';
}
