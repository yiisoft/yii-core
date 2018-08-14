<?php

/* @var $exception Exception */

?>
Code: <?= $this->app->response->statusCode ?>

Message: <?= $exception->getMessage() ?>

Exception: <?= get_class($exception) ?>
