<?php
/** @var \yii\web\View $this */
/** @var string $static */
$this->beginPage();
$this->head();
$this->beginBody();
?>
{
    "static": "<?= $static ?>",
    "dynamic": "<?= $this->renderDynamic('return $this->app->params[\'dynamic\'];') ?>"
}
<?php
$this->endBody();
$this->endPage();
?>
