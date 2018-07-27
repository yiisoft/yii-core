<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewEvent represents events triggered by the [[View]] component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewEvent extends Event
{
    /**
     * @event triggered by [[yii\base\View::beginPage()]].
     */
    const BEGIN_PAGE = 'beginPage';
    /**
     * @event triggered by [[yii\base\View::endPage()]].
     */
    const END_PAGE = 'endPage';
    /**
     * @event triggered by [[yii\web\View::beginBody()]].
     */
    const BEGIN_BODY = 'beginBody';
    /**
     * @event triggered by [[yii\web\View::endBody()]].
     */
    const END_BODY = 'endBody';
    /**
     * @event triggered by [[View::renderFile()]] right before it renders a view file.
     */
    const BEFORE_RENDER = 'beforeRender';
    /**
     * @event triggered by [[View::renderFile()]] right after it renders a view file.
     */
    const AFTER_RENDER = 'afterRender';

    /**
     * @var string the view file being rendered.
     */
    public $viewFile;
    /**
     * @var array the parameter array passed to the [[View::render()]] method.
     */
    public $params;

    /**
     * @param string $name event name
     */
    public function __construct(string $name, string $viewFile, array $params = [])
    {
        parent::__construct($name);
        $this->viewFile = $viewFile;
        $this->params = $params;
    }

    /**
     * Creates BEFORE_RENDER event.
     * @param string $viewFile the view file being rendered.
     * @param array $params array passed to the [[View::render()]] method.
     * @return self created event
     */
    public static function beforeRender(string $viewFile, array $params): self
    {
        return new static(static::BEFORE_RENDER, $viewFile, $params);
    }

    /**
     * Creates AFTER_RENDER event with result. 
     * @param string $viewFile the view file being rendered.
     * @param array $params array passed to the [[View::render()]] method.
     * @param mixed $result view rendering result.
     * @return self created event
     */
    public static function afterRender(string $viewFile, array $params, $result): self
    {
        return (new static(static::AFTER_RENDER, $viewFile, $params))->setResult($result);
    }
}
