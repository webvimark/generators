<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var webvimark\generators\crud\Generator $generator
 */
$createTitleStart = ( ! $generator->enableI18N OR $generator->defaultLanguage == 'ru') ? 'Создание' : 'Creating';

echo "<?php\n";
?>

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = <?= $generator->generateString($createTitleStart . ' ' . $generator->createUpdateTitle) ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($generator->indexTitle) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-create">

	<div class="panel panel-default">
		<div class="panel-body">

			<?= "<?= \$this->render('_form', compact('model')) ?>" ?>

		</div>
	</div>

</div>
