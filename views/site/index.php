<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 01.03.19
 * Time: 9:41
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Регистрация';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($game, 'first_player_name')->label('Имя первого игрока') ?>
<?= $form->field($game, 'second_player_name')->label('Имя второго игрока') ?>

<?= Html::submitButton('Зарегестрировать игроков', ['class' => 'btn btn-success'])?>

<?php ActiveForm::end(); ?>

