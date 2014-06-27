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
				<?= "<?= " ?>Html::a(<?= $generator->generateString('Редактировать') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-sm btn-primary']) ?>
				<?= "<?= " ?>Html::a(<?= $generator->generateString('Создать') ?>, ['create'], ['class' => 'btn btn-sm btn-success']) ?>
				<?= "<?= " ?>Html::a(<?= $generator->generateString('Удалить') ?>, ['delete', <?= $urlParams ?>], [
					'class' => 'btn btn-sm btn-danger pull-right',
					'data' => [
						'confirm' => <?= $generator->generateString('Вы уверены, что хотите удалить этот элемент?') ?>,
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
					foreach ($generator->getColumnNames() as $name)
					{
						echo "					'" . $name . "',\n";
					}
				}
				else
				{
					foreach ($generator->getTableSchema()->columns as $column)
					{
						$format = $generator->generateColumnFormat($column);
						echo "					'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
					}
				}
				?>
				],
			]) ?>

		</div>
	</div>
</div>
