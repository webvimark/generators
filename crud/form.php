<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\crud\Generator $generator
 */

$attributes = ['modelClass', 'moduleID'];

foreach ($attributes as $attribute)
{
	if ( Yii::$app->request->get($attribute) )
		$generator->$attribute = Yii::$app->request->get($attribute);
}

echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'searchModelClass');
echo $form->field($generator, 'controllerClass');
echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'layout');
echo $form->field($generator, 'indexTitle');
echo $form->field($generator, 'createUpdateTitle');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'indexWidgetType')->dropDownList([
    'grid' => 'GridView',
    'list' => 'ListView',
]);
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
echo $form->field($generator, 'tPrefix');
?>

<?php
$js = <<<JS

	var tPrefix = $('form .field-generator-tprefix');
	var I18NCheckbox = $('form #generator-enablei18n');
	var viewPath = $('form #generator-viewpath');

	tPrefix.toggle(I18NCheckbox.is(':checked'));

	I18NCheckbox.on('change', function () {
                tPrefix.toggle($(this).is(':checked'));
            });

	var modelClass = $('#generator-modelclass');
	var searchModelClass = $('#generator-searchmodelclass');
	var controllerClass = $('#generator-controllerclass');
	var moduleId = $('#generator-moduleid');

	$(modelClass).on('keyup change blur', function () {
		var nameAndPath = $(this).val().split('\\\');

		nameAndPath.forEach(function(part, i){
			if ( part == 'modules' )
			{
				moduleId.val(nameAndPath[i+1]);
			}
		});

		var name = nameAndPath.pop();
		var path = nameAndPath.join('\\\');
		var vp = nameAndPath.join('/');

		searchModelClass.val(path + '\\\search\\\' + name + 'Search');
		controllerClass.val(path.replace('models', 'controllers') + '\\\' + name + 'Controller');

		viewPath.val('@' + vp.replace('models', 'views') + '/' + name);
	});
JS;
$this->registerJs($js);
?>
