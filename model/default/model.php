<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator webvimark\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
<?php if ( isset($tableSchema->columns['url']) ): ?>
use webvimark\helpers\LittleBigHelper;
<?php endif; ?>
<?php if ( in_array('created_at', $tableSchema->columnNames) ): ?>
use yii\behaviors\TimestampBehavior;
<?php endif; ?>

/**
 * This is the model class for table "<?= $tableName ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
<?php if ( in_array('created_at', $tableSchema->columnNames) ): ?>
	protected $_timestamp_enabled = true;
<?php endif; ?>

	/**
	* @inheritdoc
	*/
	public static function tableName()
	{
		return '<?= $generator->generateTableName($tableName) ?>';
	}
<?php if ($generator->db !== 'db'): ?>

	/**
	* @return \yii\db\Connection the database connection used by this AR class.
	*/
	public static function getDb()
	{
		return Yii::$app->get('<?= $generator->db ?>');
	}
<?php endif; ?>

	/**
	* @inheritdoc
	*/
	public function rules()
	{
		return [<?= "\n			" . implode(",\n			", $rules) . "\n		" ?>];
	}

	/**
	* @inheritdoc
	*/
	public function attributeLabels()
	{
		return [
<?php foreach ($labels as $name => $label): ?>
			<?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
		];
	}
<?php foreach ($relations as $name => $relation): ?>

	/**
	* @return \yii\db\ActiveQuery
	*/
	public function get<?= $name ?>()
	{
		<?= $relation[0] . "\n" ?>
	}
<?php endforeach; ?>
<?php if ($queryClassName): ?>
	<?php
	$queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
	echo "\n";
	?>
	/**
	* @inheritdoc
	* @return <?= $queryClassFullName ?> the active query used by this AR class.
	*/
	public static function find()
	{
		return new <?= $queryClassFullName ?>(get_called_class());
	}
<?php endif; ?>
<?php if ( isset($tableSchema->columns['slug']) ): ?>

	/**
	* Generate url from the name
	*
	* @return bool
	*/
	public function beforeValidate()
	{
		$this->slug = $this->slug ? $this->slug : LittleBigHelper::slug($this->name);

		return parent::beforeValidate();
	}
<?php endif; ?>
<?php if ( $generator->hasImages($tableSchema) ): ?>

	public function afterDelete()
	{
<?php if ( count($generator->getAllImageNames($tableSchema)) > 1 ): ?>
		$this->bulkDeleteImages(['<?= implode("', '", $generator->getAllImageNames($tableSchema)) ?>']);
<?php else: ?>
		$this->deleteImage($this-><?=  $generator->getAllImageNames($tableSchema)[0] ?>);
<?php endif; ?>

		parent::afterDelete();
	}
<?php endif; ?>
}
