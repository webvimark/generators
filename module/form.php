<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\module\Generator $generator
 */
?>
<div class="module-form">
<?php
echo $form->field($generator, 'moduleClass');
echo $form->field($generator, 'moduleID');
echo $form->field($generator, 'prepareForComposer')->checkbox();
echo $form->field($generator, 'vendorName');
echo $form->field($generator, 'packageName');
echo $form->field($generator, 'namespace');
?>
</div>


<?php
$js = <<<JS
var pfc = $('#generator-prepareforcomposer');

var vendorName = $('.field-generator-vendorname');
var packageName = $('.field-generator-packagename');
var ns = $('.field-generator-namespace');

if ( !pfc.is(':checked') )
{
	vendorName.hide();
	packageName.hide();
	ns.hide();
}

pfc.on('change', function(){
	if ( $(this).is(':checked') )
	{
		vendorName.show();
		packageName.show();
		ns.show();
	}
	else
	{
		vendorName.hide();
		packageName.hide();
		ns.hide();
	}

});


$('#generator-moduleclass').on('keyup change', function(){

	var res = $(this).val().split('\\\');
	res.pop();

	$('#generator-namespace').val(res.join('\\\'));
});
JS;
$this->registerJs($js);
?>
