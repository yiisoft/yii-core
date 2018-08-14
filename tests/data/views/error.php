<?php

/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

?>
Name: <?= $name ?>

Code: <?= $this->app->response->statusCode ?>

Message: <?= $message ?>

Exception: <?= get_class($exception) ?>
