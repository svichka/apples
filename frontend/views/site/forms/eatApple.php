<?php

/* @var $this yii\web\View */
/* @var $apple \common\models\Fruit\Apple */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

?>
<?php $form = ActiveForm::begin(['id' => 'eat-apple-form', 'action' => \yii\helpers\Url::to(['/site/eat', 'id' => $apple->id])]); ?>
<div class="form-group">
<?= $form->field($apple, 'percentToEat')->textInput() ?>
<?= $form->field($apple, 'id')->hiddenInput()->label(false) ?>
	<?= Html::submitButton('Eat', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

