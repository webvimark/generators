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
	{
		$generator->$attribute = Yii::$app->request->get($attribute);

		if ( $attribute == 'ns' )
		{
			$generator->queryNs = Yii::$app->request->get($attribute);
		}
	}
}

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'db');
echo $form->field($generator, 'useTablePrefix')->checkbox();
echo $form->field($generator, 'generateLabelsFromComments')->checkbox();
echo $form->field($generator, 'generateRelations')->checkbox();

echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
echo $form->field($generator, 'tPrefix');

echo $form->field($generator, 'generateQuery')->checkbox();
echo $form->field($generator, 'queryNs');
echo $form->field($generator, 'queryClass');
echo $form->field($generator, 'queryBaseClass');
?>

<?php
$js = <<<JS
	var tPrefix = $('form .field-generator-tprefix');

	var I18NCheckbox = $('form #generator-enablei18n');
	tPrefix.toggle(I18NCheckbox.is(':checked'));

	I18NCheckbox.on('change', function () {
                tPrefix.toggle($(this).is(':checked'));
            });

	$('#generator-tablename').on('keyup change', function(){

		var parts = $(this).val().split('_');
		var result = '';

		$.each(parts, function(index, part) {
			result += part.charAt(0).toUpperCase() + part.substr(1);
		});

		$('#generator-modelclass').val(result);
		$('#generator-queryclass').val(result + 'Query');
	});

	$('#generator-ns').on('keyup change', function(){

		$('#generator-queryns').val($(this).val());
	});
JS;
$this->registerJs($js);
?>
