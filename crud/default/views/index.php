<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var app\webvimark\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
<?= !empty($generator->searchModelClass) ? " * @var " . ltrim($generator->searchModelClass, '\\') . " \$searchModel\n" : '' ?>
 */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

	<h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?php if(!empty($generator->searchModelClass)): ?>
<?= "	<?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

	<p>
		<?= "<?= " ?>Html::a(<?= $generator->generateString('Create {modelClass}', ['modelClass' => Inflector::camel2words(StringHelper::basename($generator->modelClass))]) ?>, ['create'], ['class' => 'btn btn-success']) ?>
	</p>

	<?= "<?php Pjax::begin([
		'id'=>'".Inflector::camel2id(StringHelper::basename($generator->modelClass)) ."-grid-pjax',
	]) ?>" ?>


<?php if ($generator->indexWidgetType === 'grid'): ?>
	<?= "<?= " ?>GridView::widget([
		'id'=>'<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-grid',
		'dataProvider' => $dataProvider,
		<?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n		'columns' => [\n" : "'columns' => [\n"; ?>
			['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if ( ($tableSchema = $generator->getTableSchema()) === false )
{
	foreach ($generator->orderColumns($generator->getColumnNames()) as $name)
	{
		if ( $generator->notShowColumnsInIndex($name) )
			continue;

		echo "			'" . $name . "',\n";
	}
}
else
{
	foreach ($generator->orderColumns($tableSchema->columns) as $column)
	{
		if ( $generator->notShowColumnsInIndex($column) )
			continue;

		echo "			" . $generator->generateColumnDependOnName($column) . ",\n";
	}
}
?>
			['class' => 'yii\grid\CheckboxColumn'],
			['class' => 'yii\grid\ActionColumn'],
		],
	]); ?>
<?php else: ?>
	<?= "<?= " ?>ListView::widget([
		'dataProvider' => $dataProvider,
		'itemOptions' => ['class' => 'item'],
		'itemView' => function ($model, $key, $index, $widget) {
			return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
		},
	]) ?>
<?php endif; ?>

	<?= "<?php Pjax::end() ?>" ?>

</div>
