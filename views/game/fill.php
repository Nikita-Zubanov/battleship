<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 01.03.19
 * Time: 19:38
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Расстановка фигур';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>
<?php

if ($gameData['player_move'] === (string)$field::FIRST_PLAYER_ID) {
    $field->player_name = $gameData['first_player_name'];
} else {
    $field->player_name = $gameData['second_player_name'];
}
?>
    <div class="game-create">

    <h2><?= $field->player_name ?>, расположите ваш флот!</h2>

    <table>
        <?php

        for ($line = 0; $line <= $field::FIELD_SIZE; $line++) {
            echo '<tr>';
            echo '<th>' . $line . '</th>';
            foreach ($field::COORDINATES as $column => $columnValue) {

                if ($line === 0) {
                    echo '<th>' . $field::COORDINATES[$column] . '</th>';
                } else {
                    echo '<td>' . $form->
                        field($cell, 'coordinate')->
                        checkbox([
                            'name'      => $columnValue . $line,
                            'uncheck'   => $cell::EMPTY_CELL,
                            'value'     => $cell::BUSY_CELL,
                    ]);
                }
            }
            echo '</tr>';
        }
        ?>
    </table>

        <?= $form->field($field, 'game_id')->hiddenInput(['value' => $gameData['id']])->label(false) ?>
        <?= $form->field($field, 'player_id')->hiddenInput(['value' => $gameData['player_move']])->label(false) ?>
        <?= $form->field($field, 'player_name')->hiddenInput(['value' => $field->player_name])->label(false) ?>

        <?= Html::submitButton('Сохранить поле', ['class' => 'btn btn-success'])?>

</div>
<?php ActiveForm::end(); ?>
