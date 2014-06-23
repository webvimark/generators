<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\webvimark\generators\extension;

use Yii;
use yii\gii\CodeFile;

/**
 * This generator will generate the skeleton files needed by an extension.
 *
 * @property string $keywordsArrayJson A json encoded array with the given keywords. This property is
 * read-only.
 * @property boolean $outputPath The directory that contains the module class. This property is read-only.
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since 2.0
 */
class Generator extends \yii\gii\generators\extension\Generator
{
	public $vendorName = 'webvimark';
	public $packageName = "yii2-";
	public $namespace = 'webvimark\extensions';
	public $type = "yii2-extension";
	public $keywords = "yii2,extension";
	public $title;
	public $description;
	public $outputPath = "@app/runtime/tmp-extensions";
	public $license = 'MIT';
	public $authorName = 'webvimark';
	public $authorEmail = 'webvimark@gmail.com';

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return '------ Extension Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator helps you to generate the files needed by a Yii extension.';
	}
}
