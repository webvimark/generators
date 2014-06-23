<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\crud\Generator $generator
 */

echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'searchModelClass');
echo $form->field($generator, 'controllerClass');
echo $form->field($generator, 'indexTitle');
echo $form->field($generator, 'createUpdateTitle');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'moduleID');
echo $form->field($generator, 'indexWidgetType')->dropDownList([
    'grid' => 'GridView',
    'list' => 'ListView',
]);
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
?>

<script type="text/javascript">
	/*<![CDATA[*/

	$(function () {
		var modelClass = $('#generator-modelclass');
		var searchModelClass = $('#generator-searchmodelclass');
		var controllerClass = $('#generator-controllerclass');

		$(modelClass).on('keyup change', function () {
			var nameAndPath = $(this).val().split('\\');

			var name = nameAndPath.pop();
			var path = nameAndPath.join('\\');

			searchModelClass.val(path + '\\search\\' + name + 'Search');
			controllerClass.val(path.replace('models', 'controllers') + '\\' + name + 'Controller');
		});
	});

	/*]]>*/
</script>