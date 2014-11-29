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

$tmp = explode('\\', $generator->moduleClass);
$moduleFolder = $tmp[count($tmp) -2];

echo "<?php\n";
?>

namespace <?= $ns ?>;

use Yii;

class <?= $className ?> extends \yii\base\Module
{
	/**
	* Helps to check if translations have been registered already
	*
	* @var bool
	*/
	protected static $_translationsRegistered = false;

	public $controllerNamespace = '<?= $generator->getControllerNamespace() ?>';

	/**
	* I18N helper
	*
	* @param string      $category
	* @param string      $message
	* @param array       $params
	* @param null|string $language
	*
	* @return string
	*/
	public static function t($category, $message, $params = [], $language = null)
	{
		if ( !static::$_translationsRegistered )
		{
			Yii::$app->i18n->translations['modules/<?= $moduleFolder ?>/*'] = [
				'class'          => 'yii\i18n\PhpMessageSource',
				//'sourceLanguage' => 'en',
				'basePath'       => '@app/modules/<?= $moduleFolder ?>/messages',
				'fileMap'        => [
					'modules/<?= $moduleFolder ?>/app' => 'app.php',
				],
			];

			static::$_translationsRegistered = true;
		}

		return Yii::t('modules/<?= $moduleFolder ?>/' . $category, $message, $params, $language);
	}
}
