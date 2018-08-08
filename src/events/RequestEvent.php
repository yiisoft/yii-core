<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * RequestEvent represents the parameter needed by [[Request]] events.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 * @since 3.0
 */
class RequestEvent extends Event
{
    /**
     * @event event raised at the beginning of [[validate()]]. You may set
     * [[RequestEvent::isValid]] to be false to stop the validation.
     */
    const BEFORE = 'request.before';
    /**
     * @event event raised at the end of [[validate()]]
     */
    const AFTER = 'request.after';
}
