<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\web\View $this
 * @var app\webvimark\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name => label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name => relation declaration)
 */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
use yii\helpers\Inflector;
use yii\behaviors\TimestampBehavior;


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
<?php if ( in_array('created_at', $tableSchema->columnNames) ): ?>

	/**
	* @inheritdoc
	*/
	public function behaviors()
	{
		return [
			TimestampBehavior::className(),
		];
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
<?php if ( isset($tableSchema->columns['url']) ): ?>

	/**
	* Generate url from the name
	*
	* @return bool
	*/
	public function beforeValidate()
	{
		$this->url = $this->url ? $this->url : Inflector::slug($this->name);

		return parent::beforeValidate();
	}
<?php endif; ?>
}
