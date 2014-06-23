<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace webvimark\generators\model;

use Yii;
use yii\db\ActiveRecord;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\db\Schema;
use yii\gii\CodeFile;
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
	public function getDescription()
	{
		return 'This generator generates an ActiveRecord class for the specified database table.';
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
			elseif ( $this->russianLabels($column->name) )
			{
				$labels[$column->name] = $this->russianLabels($column->name);
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
		$t = [
			'name'              => 'Название',
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
			'type_id'           => 'Тип',
			'group'             => 'Группа',
			'group_id'          => 'Группа',
			'sorter'            => 'Порядок',
			'image'             => 'Изображение',
			'logo'              => 'Лого',
			'tags'              => 'Теги',
			'is_new'            => 'Новинка',
			'is_discount'       => 'Скидка',
			'body'              => 'Текст',
			'author'            => 'Автор',
			'author_id'         => 'Автор',
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
		foreach ($table->columns as $column)
		{
			if ( $column->autoIncrement )
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
			$rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
		}

		// Unique indexes rules
		try
		{
			$db            = $this->getDbConnection();
			$uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
			foreach ($uniqueIndexes as $uniqueColumns)
			{
				// Avoid validating auto incremental columns
				if ( !$this->isColumnAutoIncremental($table, $uniqueColumns) )
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
}
