<?php
/**
 * This is the template for generating a YBC composer installer class.
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

use Composer\Installer\LibraryInstaller;

class <?= $className ?> extends LibraryInstaller
{

}
