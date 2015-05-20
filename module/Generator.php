<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace webvimark\generators\module;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\StringHelper;

/**
 * This generator will generate the skeleton code needed by a module.
 *
 * @property string $controllerNamespace The controller namespace of the module. This property is read-only.
 * @property boolean $modulePath The directory that contains the module class. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\generators\module\Generator
{
	public $moduleClass = 'app\modules\\';
	public $prepareForComposer = false;

	public $vendorName;
	public $packageName;
	public $namespace;

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return '------ Module Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator helps you to generate the skeleton code needed by a Yii module.';
	}


	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array_merge(parent::hints(), [
			'prepareForComposer' => 'Create composer.json and add post-install, post-update, post-uninstall scripts',
			'vendorName'  => 'This refers to the name of the publisher, your GitHub user name is usually a good choice, eg. <code>myself</code>.',
			'packageName' => 'This is the name of the extension on packagist, eg. <code>yii2-foobar</code>.',
			'namespace'   => 'PSR-4, eg. <code>myself\foobar\</code> This will be added to your autoloading by composer. Do not use yii, yii2 or yiisoft in the namespace.',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['vendorName', 'packageName', 'namespace', 'prepareForComposer'], 'filter', 'filter' => 'trim'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function requiredTemplates()
	{
		return ['module.php', 'controller.php', 'view.php', 'config.php'];
	}

	/**
	 * Generate also message config and message folder
	 *
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = [];
		$modulePath = $this->getModulePath();
		$files[] = new CodeFile(
			$modulePath . '/' . StringHelper::basename($this->moduleClass) . '.php',
			$this->render("module.php")
		);
		$files[] = new CodeFile(
			$modulePath . '/controllers/DefaultController.php',
			$this->render("controller.php")
		);
		$files[] = new CodeFile(
			$modulePath . '/views/default/index.php',
			$this->render("view.php")
		);
		$files[] = new CodeFile(
			$modulePath . '/messages/config.php',
			$this->render("config.php")
		);

		if ( $this->prepareForComposer )
		{
			$files[] = new CodeFile(
				$modulePath . '/composer.json',
				$this->render("composer.json")
			);
			$files[] = new CodeFile(
				$modulePath . '/YBC_'.$this->moduleID.'_Installer.php',
				$this->render("ybc_composer_installer.php")
			);
			$files[] = new CodeFile(
				$modulePath . '/README.md',
				$this->render("README.md")
			);
		}

		return $files;
	}

	/**
	 * Create also "search" and "messages" folders. Also create "messages/config.php"
	 *
	 * @inheritdoc
	 */
	public function save($files, $answers, &$results)
	{
		// If there are not errors - create "models" and "search" folders
		if ( parent::save($files, $answers, $results) )
		{
			$modelsDir = $this->getModulePath() . '/models';
			$searchDir = $modelsDir . '/search';

			mkdir($searchDir, 0777, true);

			chmod($modelsDir, 0777);
			chmod($searchDir, 0777);

			return true;
		}

		return false;
	}

}
