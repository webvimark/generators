<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\web\View $this
 * @var webvimark\generators\module\Generator $generator
 */
$className = $generator->moduleClass;
$pos = strrpos($className, '\\');
$ns = ltrim(substr($className, 0, $pos), '\\');
$className = substr($className, $pos + 1);

echo "<?php\n";
?>

namespace <?= $ns ?>;

class <?= $className ?> extends \yii\base\Module
{
	public $controllerNamespace = '<?= $generator->getControllerNamespace() ?>';

	public function init()
	{
		parent::init();

		// $this->registerTranslations();
	}


	public function registerTranslations()
	{
		Yii::$app->i18n->translations['modules/<?= $className ?>/*'] = [
			'class'          => 'yii\i18n\PhpMessageSource',
			'sourceLanguage' => 'ru',
			'basePath'       => '@app/modules/<?= $className ?>/messages',
			'fileMap'        => [
				'modules/<?= $className ?>/front' => 'front.php',
			],
		];
	}

	public static function t($category, $message, $params = [], $language = null)
	{
		return Yii::t('modules/<?= $className ?>/' . $category, $message, $params, $language);
	}
}
