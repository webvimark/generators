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
