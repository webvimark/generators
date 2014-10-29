<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var webvimark\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($generator->indexTitle) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">


	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>
				<span class="glyphicon glyphicon-th"></span> <?= "<?= Html::encode(\$this->title) ?>" ?>

			</strong>
		</div>
		<div class="panel-body">

			<p>
				<?= "<?= " ?>Html::a(Yii::t('app', 'Edit'), ['update', <?= $urlParams ?>], ['class' => 'btn btn-sm btn-primary']) ?>
				<?= "<?= " ?>Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-sm btn-success']) ?>
				<?= "<?= " ?>Html::a(Yii::t('app', 'Delete'), ['delete', <?= $urlParams ?>], [
					'class' => 'btn btn-sm btn-danger pull-right',
					'data' => [
						'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
						'method' => 'post',
					],
				]) ?>
			</p>

			<?= "<?= " ?>DetailView::widget([
				'model' => $model,
				'attributes' => [
				<?php
				if (($tableSchema = $generator->getTableSchema()) === false)
				{
					foreach ($generator->orderColumnsForView($generator->getColumnNames()) as $name)
					{
						if ( $generator->checkNotShowColumnNameInView($name) )
						{
							continue;
						}
						echo "					'" . $name . "',\n";
					}
				}
				else
				{
					foreach ($generator->orderColumnsForView($generator->getTableSchema()->columns) as $column)
					{
						if ( $generator->checkNotShowColumnNameInView($column->name) )
						{
							continue;
						}
						echo "					" . $generator->generateColumnDependOnNameInView($column). ",\n";
					}
				}
				?>
				],
			]) ?>

		</div>
	</div>
</div>
