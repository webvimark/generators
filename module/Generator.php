<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace webvimark\generators\module;

use Yii;

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
	 * Create also "search" and "messages" folders. Also create "messages/config.php"
	 *
	 * @inheritdoc
	 */
	public function save($files, $answers, &$results)
	{
		// If there are not errors - create "models" and "search" folders
		if ( ! parent::save($files, $answers, $results) )
		{
			$modelsDir = $this->getModulePath() . '/models';
			$searchDir = $modelsDir . '/search';

			mkdir($searchDir, 0777, true);

			chmod($modelsDir, 0777);
			chmod($searchDir, 0777);

			$this->createMessagesConfig();
		}
	}

	/**
	 * Create "messages" directory and config for translations in it
	 */
	protected function createMessagesConfig()
	{
		$messagesDir = $this->getModulePath() . '/messages';

		mkdir($messagesDir, 0777, true);
		chmod($messagesDir, 0777);

		$configFile = $messagesDir . '/config.php';

		file_put_contents($configFile, $this->render('config.php'));
		chmod($configFile, 0766);
	}
}
