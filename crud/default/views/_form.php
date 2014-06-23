<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var webvimark\generators\crud\Generator $generator
 */

/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
<?php if ( $generator->tableSchema->foreignKeys ): ?>
use yii\helpers\ArrayHelper;
<?php endif; ?>
<?php if ( $generator->hasCheckBoxes() ): ?>
use webvimark\extensions\BootstrapSwitch\BootstrapSwitch;
<?php endif; ?>

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

	<?= "<?php " ?>$form = ActiveForm::begin([
		'id'=>'<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>',
		'layout'=>'horizontal',
	]); ?>

<?php foreach ($generator->orderAttributesForForm($safeAttributes) as $attribute) {
	if ( $generator->checkNotShowColumnNameInForm($attribute) )
		continue;

	echo "	<?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
} ?>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<?= "<?php if ( \$model->isNewRecord ): ?>
				<?= Html::submitButton(
					'<span class=\"glyphicon glyphicon-plus-sign\"></span> ".trim($generator->generateString('Создать'),"'")."',
					['class' => 'btn btn-success']
				) ?>
			<?php else: ?>
				<?= Html::submitButton(
					'<span class=\"glyphicon glyphicon-ok\"></span> ".trim($generator->generateString('Сохранить'), "'")."',
					['class' => 'btn btn-primary']
				) ?>
			<?php endif; ?>" ?>
		</div>
	</div>

	<?= "<?php " ?>ActiveForm::end(); ?>

</div>

<?php if ( $generator->hasCheckBoxes() ): ?>
<?= "<?php BootstrapSwitch::widget() ?>" ?>
<?php endif; ?>