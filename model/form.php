<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\form\Generator $generator
 */
$attributes = ['tableName', 'modelClass', 'ns'];

foreach ($attributes as $attribute)
{
	if ( Yii::$app->request->get($attribute) )
		$generator->$attribute = Yii::$app->request->get($attribute);
}

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'db');
echo $form->field($generator, 'useTablePrefix')->checkbox();
echo $form->field($generator, 'generateRelations')->checkbox();
echo $form->field($generator, 'generateLabelsFromComments')->checkbox();
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
?>

<?php
$js = <<<JS
	$('#generator-tablename').on('keyup change', function(){
		var str = $(this).val();
		var f = str.charAt(0).toUpperCase();

		$('#generator-modelclass').val(f + str.substr(1));
	});
JS;
$this->registerJs($js);
?>
