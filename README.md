Migrations module for Yii 2
=====
Provide:
* usage of relations in views and search
* image, sorter, status columns
* better views
* integrations with other my extensions
* autocompletion

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist webvimark/generators "*"
```

or add

```
"webvimark/generators": "*"
```

to the require section of your `composer.json` file.

Configuration
-------------

In your config/web.php

```php
	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class'=>'yii\gii\Module',
		'generators' => [
			'ybc-model'     => 'webvimark\generators\model\Generator',
			'ybc-crud'      => 'webvimark\generators\crud\Generator',
			'ybc-module'    => 'webvimark\generators\module\Generator',
			'ybc-extension' => 'webvimark\generators\extension\Generator',
		]
	];
```

Usage
-----

Go to gii