<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace webvimark\generators\model;

use Yii;
use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\base\NotSupportedException;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class Generator extends \yii\gii\generators\model\Generator
{
	public $db = 'db';
	public $ns = 'app\models';
	public $tableName;
	public $modelClass;
	public $baseClass = 'webvimark\components\BaseActiveRecord';
	public $generateRelations = true;
	public $generateLabelsFromComments = false;
	public $useTablePrefix = false;
	public $generateQuery = false;
	public $queryNs = 'app\models';
	public $queryClass;
	public $queryBaseClass = 'yii\db\ActiveQuery';


	public $defaultLanguage = 'en';

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return '------ Model Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			['defaultLanguage', 'string'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return array_merge(
			parent::stickyAttributes(),
			['defaultLanguage']
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator generates an ActiveRecord class for the specified database table.';
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isImage($name)
	{
		if ( strpos($name, 'image') !== false OR strpos($name, 'logo') !== false )
		{
			return true;
		}

		return false;
	}

	/**
	 * @param TableSchema $tableSchema
	 *
	 * @return bool
	 */
	public function hasImages($tableSchema)
	{
		foreach ($tableSchema->columns as $column)
		{
			if ( $this->isImage($column->name) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param TableSchema $tableSchema
	 *
	 * @return array
	 */
	public function getAllImageNames($tableSchema)
	{
		$images = [];
		foreach ($tableSchema->columns as $column)
		{
			if ( $this->isImage($column->name) )
			{
				$images[] = $column->name;
			}
		}

		return $images;
	}

	/**
	 * Generates the attribute labels for the specified table.
	 *
	 * @param \yii\db\TableSchema $table the table schema
	 *
	 * @return array the generated attribute labels (name => label)
	 */
	public function generateLabels($table)
	{
		$labels = [];
		foreach ($table->columns as $column)
		{
			if ( $this->generateLabelsFromComments && !empty($column->comment) )
			{
				$labels[$column->name] = $column->comment;
			}
			elseif ( !strcasecmp($column->name, 'id') )
			{
				$labels[$column->name] = 'ID';
			}
			elseif ( ( ! $this->enableI18N OR $this->defaultLanguage == 'ru') AND  $this->russianLabels($column->name) )
			{
				$labels[$column->name] = $this->russianLabels($column->name);
			}
			elseif ( in_array($column->name, ['created_at', 'updated_at']) )
			{
				$labels[$column->name] = Inflector::camel2words(substr($column->name, 0, -3));
			}
			else
			{
				$label = Inflector::camel2words($column->name);
				if ( strcasecmp(substr($label, -3), ' id') === 0 )
				{
					$label = substr($label, 0, -3) . ' ID';
				}
				$labels[$column->name] = $label;
			}
		}

		return $labels;
	}

	protected function russianLabels($columnName)
	{
		$columnName = rtrim($columnName, '_id');

		$t = [
			'name'              => 'Название',
			'login'             => 'Логин',
			'url'               => 'Ссылка',
			'active'            => 'Активно',
			'status'            => 'Статус',
			'price'             => 'Цена',
			'discount_price'    => 'Цена по скидке',
			'created_at'        => 'Создано',
			'updated_at'        => 'Обновлено',
			'description'       => 'Описание',
			'short_description' => 'Краткое описание',
			'type'              => 'Тип',
			'group'             => 'Группа',
			'sorter'            => 'Порядок',
			'image'             => 'Изображение',
			'logo'              => 'Лого',
			'tags'              => 'Теги',
			'is_new'            => 'Новинка',
			'is_discount'       => 'Скидка',
			'body'              => 'Текст',
			'link'              => 'Ссылка',
			'author'            => 'Автор',
			'path'              => 'Путь',
			'code'              => 'Код',
			'class'             => 'Класс',
			'position'          => 'Позиция',
			'options'           => 'Опции',
			'preview'           => 'Превью',
		];

		return isset($t[$columnName]) ? $t[$columnName] : false;
	}

	/**
	 * Generates validation rules for the specified table.
	 *
	 * @param \yii\db\TableSchema $table the table schema
	 *
	 * @return array the generated validation rules
	 */
	public function generateRules($table)
	{
		$types   = [];
		$lengths = [];
		$images = [];
		foreach ($table->columns as $column)
		{
			if ( $column->autoIncrement OR in_array($column->name, ['created_at', 'updated_at']) )
			{
				continue;
			}

			if ( !$column->allowNull && $column->defaultValue === null AND $this->requiredFields($column) )
			{
				$types['required'][] = $column->name;
			}

			if ( stripos($column->name, 'email') !== false )
			{
				$types['email'][] = $column->name;
			}

			if ( $column->name == 'url' )
			{
				$types['unique'][] = $column->name;
			}

			if ( $this->isImage($column->name) )
			{
				$images[] = $column->name;
			}

			switch ($column->type)
			{
				case Schema::TYPE_SMALLINT:
				case Schema::TYPE_INTEGER:
				case Schema::TYPE_BIGINT:
					$types['integer'][] = $column->name;
					break;
				case Schema::TYPE_BOOLEAN:
					$types['boolean'][] = $column->name;
					break;
				case Schema::TYPE_FLOAT:
				case Schema::TYPE_DECIMAL:
				case Schema::TYPE_MONEY:
					$types['number'][] = $column->name;
					break;
				case Schema::TYPE_DATE:
				case Schema::TYPE_TIME:
				case Schema::TYPE_DATETIME:
				case Schema::TYPE_TIMESTAMP:
					$types['safe'][] = $column->name;
					break;
				default: // strings
					if ( $column->size > 0 )
					{
						$lengths[$column->size][] = $column->name;
					}
					else
					{
						$types['string'][] = $column->name;
					}
			}
		}
		$rules = [];
		foreach ($types as $type => $columns)
		{
			$rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
		}

		foreach ($lengths as $length => $columns)
		{
			$columnsWithoutImage = array_diff($columns, $images);
			$rules[] = "[['" . implode("', '", $columnsWithoutImage) . "'], 'string', 'max' => $length]";
			$rules[] = "[['" . implode("', '", $columnsWithoutImage) . "'], 'trim']";
		}

		if ( $images )
		{
			$rules[] = "[['" . implode("', '", $images) . "'], 'image', 'maxSize' => 1024*1024*5, 'extensions' => ['gif', 'png', 'jpg', 'jpeg']]";
		}

		// Unique indexes rules
		try
		{
			$db            = $this->getDbConnection();
			$uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
			foreach ($uniqueIndexes as $uniqueColumns)
			{
				// Avoid validating auto incremental columns
				if ( !$this->isColumnAutoIncremental($table, $uniqueColumns) AND $uniqueColumns[0] != 'url' )
				{
					$attributesCount = count($uniqueColumns);

					if ( $attributesCount == 1 )
					{
						$rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
					}
					elseif ( $attributesCount > 1 )
					{
						$labels      = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
						$lastLabel   = array_pop($labels);
						$columnsList = implode("', '", $uniqueColumns);
						$rules[]     = "[['" . $columnsList . "'], 'unique', 'targetAttribute' => ['" . $columnsList . "'], 'message' => 'The combination of " . implode(', ', $labels) . " and " . $lastLabel . " has already been taken.']";
					}
				}
			}
		}
		catch (NotSupportedException $e)
		{
			// doesn't support unique indexes information...do nothing
		}

		return $rules;
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return array
	 */
	protected function requiredFields($column)
	{
		$exceptNames = [
			'created_at',
			'updated_at',
			'sorter',
			'url',
			'meta_name',
			'meta_keywords',
			'meta_description'
		];
		if ( in_array($column->name, $exceptNames) )
			return false;

		if ( in_array($column->dbType, ['tinyint(1)']) )
			return false;

		return true;
	}


	/**
	 * @return array the generated relation declarations
	 */
	protected function ___generateRelations()
	{
		if ( !$this->generateRelations )
		{
			return [];
		}

		$db = $this->getDbConnection();

		if ( ( $pos = strpos($this->tableName, '.') ) !== false )
		{
			$schemaName = substr($this->tableName, 0, $pos);
		}
		else
		{
			$schemaName = '';
		}

		$relations = [];
		foreach ($db->getSchema()->getTableSchemas($schemaName) as $table)
		{
			$tableName = $table->name;
			$className = $this->generateClassName($tableName);
			foreach ($table->foreignKeys as $refs)
			{
				$refTable = $refs[0];
				unset( $refs[0] );
				$fks          = array_keys($refs);
				$refClassName = $this->generateClassName($refTable);

				// Add relation for this table
				$link                                 = $this->generateRelationLink(array_flip($refs));
				$relationName                         = $this->generateRelationName($relations, $table, $fks[0], false);
				$relations[$className][$relationName] = [
					"return \$this->hasOne($refClassName::className(), $link);",
					$refClassName,
					false,
				];

				// Add relation for the referenced table
				$hasMany = false;
				if ( count($table->primaryKey) > count($fks) )
				{
					$hasMany = true;
				}
				else
				{
					foreach ($fks as $key)
					{
						if ( !in_array($key, $table->primaryKey, true) )
						{
							$hasMany = true;
							break;
						}
					}
				}
				$link                                    = $this->generateRelationLink($refs);
				$relationName                            = $this->generateRelationName($relations, $refTable, $className, $hasMany);
				$relations[$refClassName][$relationName] = [
					"return \$this->" . ( $hasMany ? 'hasMany' : 'hasOne' ) . "($className::className(), $link);",
					$className,
					$hasMany,
				];
			}

			if ( ( $fks = $this->checkPivotTable($table) ) === false )
			{
				continue;
			}
			$table0     = $fks[$table->primaryKey[0]][0];
			$table1     = $fks[$table->primaryKey[1]][0];
			$className0 = $this->generateClassName($table0);
			$className1 = $this->generateClassName($table1);

			$link                                  = $this->generateRelationLink([$fks[$table->primaryKey[1]][1] => $table->primaryKey[1]]);
			$viaLink                               = $this->generateRelationLink([$table->primaryKey[0] => $fks[$table->primaryKey[0]][1]]);
			$relationName                          = $this->generateRelationName($relations, $db->getTableSchema($table0), $table->primaryKey[1], true);
			$relations[$className0][$relationName] = [
				"return \$this->hasMany($className1::className(), $link)->viaTable('{$table->name}', $viaLink);",
				$className1,
				true,
			];

			$link                                  = $this->generateRelationLink([$fks[$table->primaryKey[0]][1] => $table->primaryKey[0]]);
			$viaLink                               = $this->generateRelationLink([$table->primaryKey[1] => $fks[$table->primaryKey[1]][1]]);
			$relationName                          = $this->generateRelationName($relations, $db->getTableSchema($table1), $table->primaryKey[0], true);
			$relations[$className1][$relationName] = [
				"return \$this->hasMany($className0::className(), $link)->viaTable('{$table->name}', $viaLink);",
				$className0,
				true,
			];
		}

		return $relations;
	}

	/**
	 * @inheritdoc
	 */
	public function successMessage()
	{
		$message ='The code has been generated successfully. <br/> <br/>';
		$message .= Html::a(
			'Generate CRUD',
			[
				'/gii/default/view',
				'id'=>'ybc-crud',
				'modelClass'=>$this->ns . '\\' . $this->modelClass,
			],
			['target'=>'_blank']
		);

		return $message;
	}
}
