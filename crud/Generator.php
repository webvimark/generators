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
	public $layout = "//back";
	public $moduleID;
	public $controllerClass;
	public $baseControllerClass = 'webvimark\components\AdminDefaultController';
	public $indexWidgetType = 'grid';
	public $searchModelClass;
	public $indexTitle;
	public $createUpdateTitle;

	public $messageCategory = 'app';
	public $defaultLanguage = 'ru';
	public $tPrefix = 'Yii';

	public function getName()
	{
		return '------ CRUD';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['indexTitle', 'createUpdateTitle', 'tPrefix', 'layout'], 'filter', 'filter' => 'trim'],
			['defaultLanguage', 'string'],
			[['indexTitle', 'createUpdateTitle', 'layout'], 'required'],
		]);
	}


	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array_merge(parent::hints(), [
			'tPrefix' => 'For example <code>PageModule</code>',
			'indexTitle' => 'Title for the index page',
			'createUpdateTitle' => 'Title for the create and update pages <code>Создание бла-бла-бла</code> или <code>Редактирование бла-бла-бла</code>',
		]);
	}
	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return array_merge(
			parent::stickyAttributes(),
			['baseControllerClass', 'moduleID', 'indexWidgetType', 'tPrefix', 'defaultLanguage', 'layout']
		);
	}

	/**
	 * Check if there are images or logos in table
	 *
	 * @return bool
	 */
	public function hasImages()
	{
		foreach ($this->tableSchema->columns as $name => $uselessStuff)
		{
			if ( $this->isImage($name) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function hasText()
	{
		foreach ($this->tableSchema->columns as $column)
		{
			if ( $column->type == 'text' )
			{
				return true;
			}
		}

		return false;
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
	 * Return array of relations if FK ids as keys and ref names as values
	 *
	 * ['excursion_type_id'=>'excursionType']
	 *
	 * @return array
	 */
	public function getRelationRefs()
	{
		$refs = [];
		foreach ($this->tableSchema->foreignKeys as $fk)
		{
			$tmp = array_keys($fk);
			$fkId = end($tmp);

			$refs[$fkId] = lcfirst(Inflector::id2camel($fk[0], '_'));
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
		elseif ( $this->isImage($column->name) )
		{
			return $this->_generateImageColumn($column);
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
				'filter'=>ArrayHelper::map({$refTable}::find()->asArray()->all(), 'id', 'name'),
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
	protected function _generateImageColumn($column)
	{
		return "[
				'value'=>function(\$model){
						return Html::img(\$model->getImageUrl('small', '{$column->name}'));
					},
				'contentOptions'=>['width'=>'10px'],
				'format'=>'raw',
			]";
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
						return Html::a(\$model->name, ['update', 'id'=>\$model->id], ['data-pjax'=>0]);
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
	 * Reorder columns for view (like "name" goes first and "sorter" last)
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function orderColumnsForView($columns)
	{
		$startItems = [
			'id',
			'active',
			'status',
			'name',
			'username',
			'login',
			'url',
			'email',
		];
		$endItems   = [
			'image',
			'logo',
			'created_at',
			'updated_at',
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
	 * @param string $name
	 *
	 * @return bool
	 */
	public function checkNotShowColumnNameInView($name)
	{
		$notShow = [
			'sorter',
		];

		return in_array($name, $notShow);
	}


	/**
	 * @param ColumnSchema $column
	 *
	 * @return string
	 */
	public function generateColumnDependOnNameInView($column)
	{
		if ( $column->dbType == 'tinyint(1)' )
		{
			return $this->_generateStatusColumnInView($column);
		}
		elseif ( $this->isImage($column->name) )
		{
			return $this->_generateImageColumnInView($column);
		}
		elseif ( $column->type == 'text' )
		{
			return "'" . $column->name . ':raw' . "'";
		}
		elseif ( $column->name == 'url' )
		{
			return "'" . $column->name . "'";
		}
		elseif ( in_array($column->name, ['created_at', 'updated_at']) )
		{
			return "'" . $column->name . ':datetime' . "'";
		}
		elseif ( $this->_isFk($column) )
		{
			return $this->_generateFkColumnInView($column);
		}

		$format = $this->generateColumnFormat($column);

		$result = $column->name . ($format === 'text' ? "" : ":" . $format);

		return "'" . $result . "'";
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return mixed
	 */
	protected function _generateStatusColumnInView($column)
	{
		return "[
						'attribute'=>'{$column->name}',
						'value'=>(\$model->{$column->name} == 1) ?
								'<span class=\"label label-success\">Да</span>' :
								'<span class=\"label label-warning\">Нет</span>',
						'format'=>'raw',
					]";
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return mixed
	 */
	protected function _generateImageColumnInView($column)
	{
		return "[
						'attribute'=>'{$column->name}',
						'value'=>Html::img(\$model->getImageUrl('medium', '{$column->name}')),
						'visible'=>is_file(\$model->getImagePath('medium', '{$column->name}')),
						'format'=>'raw',
					]";
	}

	/**
	 * @param ColumnSchema $column
	 *
	 * @return mixed
	 */
	protected function _generateFkColumnInView($column)
	{
		$refTables = $this->getRelationRefs();

		return "[
						'attribute'=>'{$column->name}',
						'value'=>@\$model->".$refTables[$column->name]."->name,
					]";
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
					$hashConditions[] = "'{$this->tableSchema->name}.{$column}' => \$this->{$column},";
					break;
				case Schema::TYPE_TEXT:
					break;
				default:
					$likeConditions[] = "->andFilterWhere(['like', '{$this->tableSchema->name}.{$column}', \$this->{$column}])";
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
			return "\$form->field(\$model, '$attribute', ['enableClientValidation'=>false, 'enableAjaxValidation'=>false])->textarea(['rows' => 6])";
		}
		elseif ( $this->isImage($column->name) )
		{
			return $this->_generateImageField($column);
		}
		elseif ( $column->name === 'name' )
		{
			return "\$form->field(\$model, '$attribute')->textInput(['maxlength' => 255, 'autofocus'=>\$model->isNewRecord ? true:false])";
		}
		elseif ( $this->_isFk($column) )
		{
			return "\$form->field(\$model, '$attribute')
		->dropDownList(
			ArrayHelper::map(".Inflector::id2camel(rtrim($attribute, '_id'), '_')."::find()->asArray()->all(), 'id', 'name'),
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

	/**
	 * @param ColumnSchema $column
	 *
	 * @return string
	 */
	protected function _generateImageField($column)
	{

//		return "\$form->field(\$model, '$attribute')->fileInput()";
	}

	/**
	 * Generates a string depending on enableI18N property
	 *
	 * @param string $string the text be generated
	 * @param array $placeholders the placeholders to use by `Yii::t()`
	 * @return string
	 */
	public function generateString($string = '', $placeholders = [])
	{
		$string = addslashes($string);
		if ($this->enableI18N) {
			// If there are placeholders, use them
			if (!empty($placeholders)) {
				$ph = ', ' . VarDumper::export($placeholders);
			} else {
				$ph = '';
			}
			$str = $this->tPrefix . "::t('" . $this->messageCategory . "', '" . $string . "'" . $ph . ")";
		} else {
			// No I18N, replace placeholders by real words, if any
			if (!empty($placeholders)) {
				$phKeys = array_map(function($word) {
					return '{' . $word . '}';
				}, array_keys($placeholders));
				$phValues = array_values($placeholders);
				$str = "'" . str_replace($phKeys, $phValues, $string) . "'";
			} else {
				// No placeholders, just the given string
				$str = "'" . $string . "'";
			}
		}
		return $str;
	}

	/**
	 * Created_at and updated_at now safe attributes (for date range picker)
	 *
	 * Generates validation rules for the search model.
	 * @return array the generated validation rules
	 */
	public function generateSearchRules()
	{
		if (($table = $this->getTableSchema()) === false) {
			return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
		}
		$types = [];
		foreach ($table->columns as $column) {
			if ( in_array($column->name, ['created_at', 'updated_at']) )
			{
				$types['safe'][] = $column->name;
				continue;
			}

			switch ($column->type) {
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
				default:
					$types['safe'][] = $column->name;
					break;
			}
		}

		$rules = [];
		foreach ($types as $type => $columns) {
			$rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
		}

		return $rules;
	}
}
