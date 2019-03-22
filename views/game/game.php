<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 26.02.19
 * Time: 8:09
 */
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\Field;
use app\models\Cell;

$this->title = 'Игра';
?>

<?php
NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar-inverse navbar-fixed-top',
    ],
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
        ['label' => 'Закончить игру', 'url' => ['/site/index']],
    ],
]);
NavBar::end();
?>

<h2><?= $playerData['player_name'] ?>, ваш ход.</h2>

<?php $form = ActiveForm::begin(); ?>

<div class="enemy-game-table">

    <table>
        <?php

        for ($line = 0; $line <= Field::FIELD_SIZE; $line++) {
            echo '<tr>';
            echo '<th>' . $line . '</th>';
            foreach (Field::COORDINATES as $column => $columnValue) {

                if ($line === 0) {
                    echo '<th>' . Field::COORDINATES[$column] . '</th>';
                } else {
                    $stateCell = Cell::find()->asArray()->where(['game_id' => $gameData['id'], 'player_id' => $enemyData['player_id'], 'coordinate' => $columnValue . $line])->one()['state'];

                    if ($stateCell == Cell::WRECKED_CELL) {
                        echo '<td class="wrecked">';
                    } elseif ($stateCell == Cell::MISS_CELL) {
                        echo '<td class="miss">';
                    } elseif ($stateCell == Cell::BUSY_CELL) {
//                        echo '<td class="busy">'; //Для отладки(показывает корабли противника)
                        echo '<td>';
                    } else {
                        echo '<td>';
                        $stateCell = Cell::EMPTY_CELL;
                    }

                    echo $form->
                    field($cell, 'coordinate')->
                    checkbox([
                        'name' => $columnValue . $line,
                        'uncheck' => $stateCell,
                        'value' => Cell::HIT_CELL,
                    ]);
                }
            }
            echo '</tr>';
        }
        ?>
    </table>
</div>

<?= Html::submitButton('Сделать ход', ['class' => 'btn btn-success'])?>

<div class="player-game-table">

    <table>
        <?php

        for ($line = 0; $line <= Field::FIELD_SIZE; $line++) {
            echo '<tr>';
            echo '<th>' . $line . '</th>';
            foreach (Field::COORDINATES as $column => $columnValue) {

                if ($line === 0) {
                    echo '<th>' . Field::COORDINATES[$column] . '</th>';
                } else {
                    $stateCell = Cell::find()->asArray()->where(['game_id' => $gameData['id'], 'player_id' => $playerData['player_id'], 'coordinate' => $columnValue . $line])->one()['state'];

                    if ($stateCell == Cell::WRECKED_CELL) {
                        echo '<td class="wrecked">';
                    } elseif ($stateCell == Cell::MISS_CELL) {
                        echo '<td class="miss">';
                    } elseif ($stateCell == Cell::BUSY_CELL) {
                        echo '<td class="busy">';
                    } else
                        echo '<td>';

                }
            }
            echo '</tr>';
        }
        ?>
    </table>
</div>

<?php ActiveForm::end(); ?>
