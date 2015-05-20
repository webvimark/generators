<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var webvimark\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$viewTitleStart = $generator->enableI18N ? "Yii::t('app', 'Details of the')" : 'Details of the';
$editBtn = $generator->enableI18N ? "Yii::t('app', 'Edit')" : 'Edit';
$createBtn = $generator->enableI18N ? "Yii::t('app', 'Create')" : 'Create';

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = <?= $viewTitleStart . ' . " " . ' . $generator->generateString($generator->createUpdateTitle) ?> . ': ' . $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($generator->indexTitle) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">


	<div class="panel panel-default">
		<div class="panel-body">

			<p>
				<?= "<?= " ?>Html::a(<?= $editBtn ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-sm btn-primary']) ?>
				<?= "<?= " ?>Html::a(<?= $createBtn ?>, ['create'], ['class' => 'btn btn-sm btn-success']) ?>
				<?= "<?= " ?>Html::a(Yii::t('yii', 'Delete'), ['delete', <?= $urlParams ?>], [
					'class' => 'btn btn-sm btn-danger pull-right',
					'data' => [
						'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
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
