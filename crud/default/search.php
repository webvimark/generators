<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating CRUD search class of the specified model.
 *
 * @var yii\web\View $this
 * @var \webvimark\generators\crud\Generator $generator
 */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;

/**
 * <?= $searchModelClass ?> represents the model behind the search form about `<?= $generator->modelClass ?>`.
 */
class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
	public function rules()
	{
		return [
			<?= implode(",\n			", $rules) ?>,
		];
	}

	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search($params)
	{
		$query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find();

<?php if ( in_array('sorter', $generator->tableSchema->columnNames) ): ?>
		if ( ! Yii::$app->request->get('sort') )
		{
			$query->orderBy('<?= $generator->tableSchema->name ?>.sorter');
		}

<?php endif; ?>
<?php if ( $generator->tableSchema->foreignKeys ): ?>
		$query->joinWith(['<?= implode('\', \'', $generator->getRelationRefs()) ?>']);

<?php endif; ?>
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => Yii::$app->request->cookies->getValue('_grid_page_size', 20),
			],
			'sort'=>[
				'defaultOrder'=>['id'=> SORT_DESC],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		<?= implode("\n        	", $searchConditions) ?>

		return $dataProvider;
	}
}
