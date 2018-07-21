<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\helpers\Yii;

/**
 * InlineAction represents an action that is defined as a controller method.
 *
 * The name of the controller method is available via [[actionMethod]] which
 * is set by the [[controller]] who creates this action.
 *
 * For more details and usage information on InlineAction, see the [guide article on actions](guide:structure-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
    /**
     * @var string the controller method that this inline action is associated with
     */
    public $actionMethod;


    /**
     * @param string $id the ID of this action
     * @param Controller $controller the controller that owns this action
     * @param string $actionMethod the controller method that this inline action is associated with
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $actionMethod)
    {
        $this->actionMethod = $actionMethod;
        parent::__construct($id, $controller);
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     * @param array $params action parameters
     * @return mixed the result of the action
     */
    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);
        Yii::debug('Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()', __METHOD__);
        if ($this->app->requestedParams === null) {
            $this->app->requestedParams = $args;
        }

        return $this->controller->{$this->actionMethod}(...$args);
    }
}
