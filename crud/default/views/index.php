<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var webvimark\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$createBtn = $generator->enableI18N ? "Yii::t('app', 'Create')" : 'Create';

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use webvimark\extensions\GridBulkActions\GridBulkActions;
use webvimark\extensions\GridPageSize\GridPageSize;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
<?= !empty($generator->searchModelClass) ? " * @var " . ltrim($generator->searchModelClass, '\\') . " \$searchModel\n" : '' ?>
 */

$this->title = <?= $generator->generateString($generator->indexTitle) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

<?php if(!empty($generator->searchModelClass)): ?>
<?= "	<?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

	<div class="panel panel-default">
		<div class="panel-body">

			<div class="row">
				<div class="col-xs-6">
					<p>
						<?= "<?= " ?>Html::a('<span class="glyphicon glyphicon-plus-sign"></span> ' . <?= $createBtn ?>, ['create'], ['class' => 'btn btn-success']) ?>
					</p>
				</div>

				<div class="col-xs-6 text-right">
					<?= "<?= GridPageSize::widget(['pjaxId'=>'".Inflector::camel2id(StringHelper::basename($generator->modelClass))."-grid-pjax']) ?>" ?>

				</div>
			</div>


			<?= "<?php Pjax::begin([
				'id'=>'".Inflector::camel2id(StringHelper::basename($generator->modelClass)) ."-grid-pjax',
			]) ?>" ?>


		<?php if ($generator->indexWidgetType === 'grid'): ?>
	<?= "<?= " ?>GridView::widget([
				'id'=>'<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-grid',
				'dataProvider' => $dataProvider,
				'pager'=>[
					'options'=>['class'=>'pagination pagination-sm'],
					'hideOnSinglePage'=>true,
					'lastPageLabel'=>'>>',
					'firstPageLabel'=>'<<',
				],

				'layout'=>'{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}'.GridBulkActions::widget(['gridId'=>'<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>grid']).'</div></div>',

			<?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n				'columns' => [\n" : "'columns' => [\n"; ?>
					['class' => 'yii\grid\SerialColumn', 'options'=>['style'=>'width:10px'] ],

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

					['class' => 'yii\grid\CheckboxColumn', 'options'=>['style'=>'width:10px'] ],
					[
						'class' => 'yii\grid\ActionColumn',
						'contentOptions'=>['style'=>'width:70px; text-align:center;'],
					],
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
	</div>
</div>
