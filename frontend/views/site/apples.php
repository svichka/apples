<?php

/* @var $this yii\web\View */
/* @var $apples \common\models\Fruit\Apple[] */

use yii\helpers\Html;

$this->title = 'Apples';
$this->params['breadcrumbs'][] = $this->title;
?>
<div>
	<?=Html::a('Generate', [\yii\helpers\Url::to(['/site/generate'])])?>
</div>
<hr>
<div>
	<?php foreach ($apples as $apple): ?>
	<div class="row">
		<div class="col-lg-3">
		<?=$apple->color ?>
		</div>
		<div class="col-lg-3">
		<?=$apple->size ?>
		</div>
		<div class="col-lg-3">
		<?=0 === $apple->fallen_at ? Html::a('Drop', [\yii\helpers\Url::to(['/site/drop', 'id' => $apple->id])]) : 'Fallen' ?>
		<?=$apple->isDecayed() ? 'Decayed' : ''?>
		</div>
		<div class="col-lg-3">
		<?=0 !== $apple->fallen_at && !$apple->isDecayed() ? $this->render('forms/eatApple', ['apple' => $apple]): '' ?>
		</div>
	</div>
	<hr>
	<?php endforeach; ?>
</div>