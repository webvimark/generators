<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var webvimark\generators\crud\Generator $generator
 */
$createTitleStart = $generator->enableI18N ? "Yii::t('app', 'Creating')" : "'Creating'";

echo "<?php\n";
?>

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = <?= $createTitleStart . ' . " " . ' . $generator->generateString($generator->createUpdateTitle) ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($generator->indexTitle) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-create">

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
