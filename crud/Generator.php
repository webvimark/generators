<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace webvimark\generators\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

/**
 * Generates CRUD
 * @property array                       $columnNames      Model column names. This property is read-only.
 * @property string                      $controllerID     The controller ID (without the module ID prefix). This property is
 * read-only.
 * @property array                       $searchAttributes Searchable attributes. This property is read-only.
 * @property boolean|\yii\db\TableSchema $tableSchema      This property is read-only.
 * @property string                      $viewPath         The action view file path. This property is read-only.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class Generator extends \yii\gii\generators\crud\Generator
{
	public $modelClass = "app\\";
	public $moduleID;
	public $controllerClass;
	public $baseControllerClass = 'webvimark\components\BaseController';
	public $indexWidgetType = 'grid';
	public $searchModelClass;

	public function getName()
	{
		return '------ CRUD';
	}

	/**
	 * Return array of relations
	 * @return array
	 */
	public function getRelationRefs()
	{
		$refs = [];
		foreach ($this->tableSchema->foreignKeys as $fk)
		{
			$refs[] = lcfirst(Inflector::id2camel($fk[0], '_'));
		}

		return $refs;
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return string
	 */
	public function generateColumnDependOnName($column)
	{
		if ( $column->dbType == 'tinyint(1)' )
		{
			return $this->_generateStatusColumn($column);
		}
		elseif ( $column->name == 'name' )
		{
			return $this->_generateLinkColumn($column);
		}
		elseif ( $column->name == 'sorter' )
		{
			return $this->_generateSorterColumn($column);
		}
		elseif ( $this->_isFk($column) )
		{
			return $this->_generateFkColumn($column);
		}

		$format = $this->generateColumnFormat($column);

		$result = $column->name . ($format === 'text' ? "" : ":" . $format);

		return "'" . $result . "'";
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return string
	 */
	protected function _generateFkColumn($column)
	{
		foreach ($this->tableSchema->foreignKeys as $fk)
		{
			$columnName = array_keys($fk);
			if ( end($columnName) == $column->name )
			{
				$refTable = Inflector::id2camel($fk[0], '_');
				break;
			}
		}

		$relation = lcfirst($refTable);

		return "[
				'attribute'=>'{$column->name}',
				'filter'=>ArrayHelper::map({$refTable}::find()->all(), 'id', 'name'),
				'value'=>'{$relation}.name',
			]";
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return bool
	 */
	protected function _isFk($column)
	{
		foreach ($this->tableSchema->foreignKeys as $fk)
		{
			$columnName = array_keys($fk);
			if ( end($columnName) == $column->name )
			{
				return true;
			}
		}

		return false;

	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return string
	 */
	protected function _generateLinkColumn($column)
	{
		return "[
				'attribute'=>'{$column->name}',
				'value'=>function(\$model){
						return Html::a(\$model->name, ['update', 'id'=>\$model->id]);
					},
				'format'=>'raw',
			]";
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return string
	 */
	protected function _generateSorterColumn($column)
	{

		return "['class' => 'webvimark\components\SorterColumn']";
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return string
	 */
	protected function _generateStatusColumn($column)
	{

		return "[
				'class'=>'webvimark\components\StatusColumn',
				'attribute'=>'{$column->name}',
				'toggleUrl'=>Url::to(['toggle-attribute', 'attribute'=>'{$column->name}', 'id'=>'_id_']),
			]";

	}

	/**
	 * Reorder columns (like "name" goes first and "sorter" last)
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function orderColumns($columns)
	{
		$startItems = [
			'image',
			'logo',
			'name'
		];
		$endItems   = [
			'active',
			'status',
			'sorter'
		];

		foreach (array_reverse($startItems) as $startItem)
		{
			if ( isset($columns[$startItem]) )
			{
				$item = $columns[$startItem];
				unset($columns[$startItem]);

				array_unshift($columns, $item);
			}
		}

		foreach ($endItems as $endItem)
		{
			if ( isset($columns[$endItem]) )
			{
				$item = $columns[$endItem];
				unset($columns[$endItem]);

				array_push($columns, $item);
			}
		}


		return $columns;
	}

	/**
	 * Reorder columns (like "name" goes first and "sorter" last)
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function orderAttributesForForm($attributes)
	{
		$attributes = array_combine($attributes, $attributes);
		$startItems = [
			'active',
			'status',
			'name',
			'url',
			'image',
			'logo',
		];
		$endItems   = [
			'meta_title',
			'meta_keywords',
			'meta_description',
		];


		foreach (array_reverse($startItems) as $startItem)
		{
			if ( isset($attributes[$startItem]) )
			{
				$item = $attributes[$startItem];
				unset($attributes[$startItem]);

				array_unshift($attributes, $item);
			}
		}

		foreach ($endItems as $endItem)
		{
			if ( isset($attributes[$endItem]) )
			{
				$item = $attributes[$endItem];
				unset($attributes[$endItem]);

				array_push($attributes, $item);
			}
		}


		return $attributes;
	}

	/**
	 * @return array model column names
	 */
	public function getColumnNames()
	{
		/** @var ActiveRecord $class */
		$class = $this->modelClass;
		if ( is_subclass_of($class, 'yii\db\ActiveRecord') )
		{
			return $class::getTableSchema()->getColumnNames();
		}
		else
		{
			/** @var \yii\base\Model $model */
			$model = new $class();

			return $model->attributes();
		}
	}

	/**
	 * @param string|ColumnSchema $column
	 *
	 * @return array
	 */
	public function notShowColumnsInIndex($column)
	{
		if ( $column instanceof ColumnSchema )
		{
			if ( $this->checkNotShowColumnNameForIndex($column->name) OR $column->type == Schema::TYPE_TEXT )
			{
				return true;
			}
		}
		else
		{
			return $this->checkNotShowColumnNameForIndex($column);
		}

		return false;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	protected function checkNotShowColumnNameForIndex($name)
	{
		$notShow = [
			'id',
			'created_at',
			'updated_at',
			'url',
			'meta_title',
			'meta_keywords',
			'meta_description',
		];

		return in_array($name, $notShow);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function checkNotShowColumnNameInForm($name)
	{
		$notShow = [
			'created_at',
			'updated_at',
			'sorter',
		];

		return in_array($name, $notShow);
	}


	/**
	 * Generates search conditions
	 * @return array
	 */
	public function generateSearchConditions()
	{
		$columns = [];
		if ( ($table = $this->getTableSchema()) === false )
		{
			$class = $this->modelClass;
			/** @var \yii\base\Model $model */
			$model = new $class();
			foreach ($model->attributes() as $attribute)
			{
				$columns[$attribute] = 'unknown';
			}
		}
		else
		{
			foreach ($table->columns as $column)
			{
				$columns[$column->name] = $column->type;
			}
		}

		$likeConditions = [];
		$hashConditions = [];
		foreach ($columns as $column => $type)
		{
			if ( strpos($column, 'image') !== false )
			{
				continue;
			}

			switch ($type)
			{
				case Schema::TYPE_SMALLINT:
				case Schema::TYPE_INTEGER:
				case Schema::TYPE_BIGINT:
				case Schema::TYPE_BOOLEAN:
				case Schema::TYPE_FLOAT:
				case Schema::TYPE_DECIMAL:
				case Schema::TYPE_MONEY:
				case Schema::TYPE_DATE:
				case Schema::TYPE_TIME:
				case Schema::TYPE_DATETIME:
				case Schema::TYPE_TIMESTAMP:
					$hashConditions[] = "'{$column}' => \$this->{$column},";
					break;
				case Schema::TYPE_TEXT:
					break;
				default:
					$likeConditions[] = "->andFilterWhere(['like', '{$column}', \$this->{$column}])";
					break;
			}
		}

		$conditions = [];
		if ( !empty($hashConditions) )
		{
			$conditions[] = "\$query->andFilterWhere([\n" . str_repeat('	', 3) . implode("\n" . str_repeat('	', 3), $hashConditions) . "\n" . str_repeat('	', 2) . "]);\n";
		}
		if ( !empty($likeConditions) )
		{
			$conditions[] = "\$query" . implode("\n" . str_repeat('	', 3), $likeConditions) . ";\n";
		}

		return $conditions;
	}

	/**
	 * @return bool
	 */
	public function hasCheckBoxes()
	{
		foreach ($this->tableSchema->columns as $column)
		{
			if ( $column->dbType === 'tinyint(1)' )
				return true;
		}

		return false;
	}

	/**
	 * Generates code for active field
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	public function generateActiveField($attribute)
	{
		$tableSchema = $this->getTableSchema();
		if ( $tableSchema === false || !isset($tableSchema->columns[$attribute]) )
		{
			if ( preg_match('/^(password|pass|passwd|passcode)$/i', $attribute) )
			{
				return "\$form->field(\$model, '$attribute')->passwordInput()";
			}
			else
			{
				return "\$form->field(\$model, '$attribute')";
			}
		}
		$column = $tableSchema->columns[$attribute];

		if ( $column->dbType === 'tinyint(1)' )
		{
			return "\$form->field(\$model->loadDefaultValues(), '$attribute')->checkbox(['class'=>'b-switch'], false)";
		}
		elseif ( $column->type === 'text' )
		{
			return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
		}
		elseif ( $column->name === 'name' )
		{
			return "\$form->field(\$model, '$attribute')->textInput(['maxlength' => 255, 'autofocus'=>\$model->isNewRecord ? true:false])";
		}
		elseif ( $this->_isFk($column) )
		{
			return "\$form->field(\$model, '$attribute')
		->dropDownList(
			ArrayHelper::map(".Inflector::id2camel(rtrim($attribute, '_id'), '_')."::find()->all(), 'id', 'name'),
			['prompt'=>'']
		)";
		}
		elseif ( stripos($column->name, 'price') !== false )
		{
			return "\$form->field(\$model, '$attribute',
		['inputTemplate' => '<div class=\"row\"><div class=\"col-sm-4\"><div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div></div></div>',]
	)->textInput()";
		}
		else
		{
			if ( preg_match('/^(password|pass|passwd|passcode)$/i', $column->name) )
			{
				$input = 'passwordInput';
			}
			else
			{
				$input = 'textInput';
			}
			if ( is_array($column->enumValues) && count($column->enumValues) > 0 )
			{
				$dropDownOptions = [];
				foreach ($column->enumValues as $enumValue)
				{
					$dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
				}

				return "\$form->field(\$model, '$attribute')->dropDownList(" . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)) . ", ['prompt' => ''])";
			}
			elseif ( $column->phpType !== 'string' || $column->size === null )
			{
				return "\$form->field(\$model, '$attribute')->$input()";
			}
			else
			{
				return "\$form->field(\$model, '$attribute')->$input(['maxlength' => $column->size])";
			}
		}
	}
}
