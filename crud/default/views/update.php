<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var webvimark\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$updateTitleStart = $generator->enableI18N ? "Yii::t('app', 'Editing')" : "'Editing'";

echo "<?php\n";
?>

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = <?= $updateTitleStart . ' . " " . ' . $generator->generateString($generator->createUpdateTitle) ?> . ': ' . $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($generator->indexTitle) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model-><?= $generator->getNameAttribute() ?>, 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Editing')
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-update">

    <?php if ($generator->addBootstrapPanel): ?>
    <div class="panel panel-default">
        <div class="panel-body">
    <?php endif; ?>

			<?= "<?= \$this->render('_form', compact('model')) ?>" ?>

    <?php if ($generator->addBootstrapPanel): ?>
        </div>
    </div>
    <?php endif; ?>

</div>
