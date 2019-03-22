<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 21.03.19
 * Time: 14:08
 */
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

$this->title = 'Победитель';
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

<h1><?= $gameData['winner'] ?>, поздравляем! Вы победили!</h1>