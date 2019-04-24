<?php

/** @var string $title */
/** @var string $sourcePath */
/** @var string $translationPath */
/** @var array $results */

use yii\helpers\Html;

?><!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Translation report</title>

        <style>
            .diff ins {
                background: #cfc;
                text-decoration: none;
            }

            .diff del {
                background: #ffe6cc;
                text-decoration: none;
            }

            .ok {
                color: #99cc32;
            }

            .errors {
                color: #cc5129;
            }
        </style>
    </head>
    <body>
        <h1><?= Html::encode($title) ?></h1>

        <ul>
            <li><strong>Source:</strong> <?= Html::encode($sourcePath) ?></li>
            <li><strong>Translation:</strong> <?= Html::encode($translationPath) ?></li>
        </ul>

        <?php foreach ($results as $name => $result): ?>
            <h2 class="<?= empty($result['errors']) ? 'ok' : 'errors' ?>"><?= $name ?></h2>
            <?php foreach ($result['errors'] as $error): ?>
                <p><?= Html::encode($error) ?></p>
            <?php endforeach ?>
            <?php if (!empty($result['diff'])): ?>
                <pre><code class="diff"><?= $this->context->highlightDiff($result['diff']) ?></code></pre>
            <?php endif ?>
        <?php endforeach ?>
    </body>
</html>