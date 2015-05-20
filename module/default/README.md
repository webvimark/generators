<?= $generator->moduleID ?>

<?= str_repeat('=', mb_strlen($generator->moduleID, \Yii::$app->charset)) ?>


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist <?= $generator->vendorName ?>/<?= $generator->packageName ?> "*"
```

or add

```
"<?= $generator->vendorName ?>/<?= $generator->packageName ?>": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the module is installed, simply use it in your code by  :

```php
```