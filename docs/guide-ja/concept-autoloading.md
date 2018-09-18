クラスのオートローディング
==========================

Yiiは、必要となるすべてのクラス・ファイルを特定してインクルードするために、
[クラスのオートローディング・メカニズム](http://www.php.net/manual/ja/language.oop5.autoload.php) を使用します。
オートローダ自体も、[PSR-4 標準](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) に準拠したものが Composer によって生成されます。

> Note: 説明を簡単にするため、このセクションではクラスのオートローディングについてのみ話します。しかし、
  ここに記述されている内容は、インタフェイスとトレイトのオートローディングにも同様に適用されることに注意してください。


## オートローダを構成する <span id="configuring-autoloader"></span>

Yii は Composer が生成するオートローダを使用します。それを構成するためには `composer.json` を使わなければなりません。


```json
{
    "name": "myorg/myapp",
    "autoload": {
        "psr-4": {
          "app\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "tests\\": "tests"
        }
    }
}
```

テスト固有の要求は、実運用環境のオートローダを汚染しないように `autoload-dev` に置いていることに注意して下さい。
実運用環境では Composer を `--no-dev` フラグで使います。

詳細は [Composer のドキュメント](https://getcomposer.org/doc/01-basic-usage.md#autoloading) を参照して下さい。

構成が済んだら、`composer dump-autoload` を実行して、オートローダを生成します。
これは構成を変更するたびに実行しなければならないことに注意して下さい。

アプリケーションにオートローダの存在を知らせるために、エントリ・スクリプトで `vendor/autoload.php` を `require` します。
ウェブ・アプリケーションでは通常は `index.php`、コンソール・アプリケーションでは `yii` です。

```php
<?php

// 実運用環境に配備するときは次の2行をコメント・アウトする
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();

```

## オートローダを使用する <span id="using-autoloader"></span>

クラス・オートローダを使用するためには、クラスの作成と命名について、二つの単純な規則に従わなければなりません。

* 全てのクラスは [namespace](http://php.net/manual/ja/language.namespaces.php) (e.g. `foo\bar\MyClass`) に属さなければならない
* 全てのクラスは、名前空間と合致するディレクトリの下に、クラス名の後に `.php` を付けた名前の
独立したファイルに保存されなければならない。
  
> Note: 実際には、名前空間に合致しないディレクトリにクラスを保管する必要がある場合に、第2の規則を破ることは可能です。
> [Composer のドキュメント](https://getcomposer.org/doc/01-basic-usage.md#autoloading) を参照して下さい。

例えば、クラス名と名前空間が `foo\bar\MyClass` である場合、ファイルは `bar/MyClass.php` で、
`foo` は `composer.json` のオートローダの定義に従ってマップされます。

[プロジェクト・テンプレート](start-installation.md) を使おうとする場合は、あなたのクラス群を事前定義されたトップ・レベルの名前空間である
`app` の下に置くことが出来ます。
`app\components\MyClass` というクラス名は `AppBasePath/components/MyClass.php` というクラス・ファイルとして解決できます。
