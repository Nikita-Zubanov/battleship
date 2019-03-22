<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 26.02.19
 * Time: 19:12
 */

namespace app\models;

use yii\db\ActiveRecord;

class Game extends ActiveRecord
{
    public static function tableName()
    {
        return 'game';
    }

    public function attributeLabels()
    {
        return [
            'first_player_name' => 'Имя первого игрока',
            'second_player_name' => 'Имя второго игрока',
            'player_move' => 'Ход игрока',
        ];
    }

    public function rules()
    {
        return [
            [['first_player_name', 'second_player_name'], 'required', 'message' => 'Поле обязательно для заполнения'],
            [['player_move'], 'default', 'value' => '1'],
        ];
    }

    public function getGameData()
    {
        return Game::find()->asArray()->orderBy(['id' => SORT_DESC])->one();
    }

    public function setGameDatabase($gameId, $playerMove, $winner)
    {
        $game = Game::findOne(['id' => $gameId]);
        $game->player_move = $playerMove;
        $game->winner = $winner;
        $game->save();
    }

    public function getWinner($playerData, $enemyShips)
    {
        if ($enemyShips['singleDeck']['shipCount'] === 0 &&
            $enemyShips['doubleDeck']['shipCount'] === 0 &&
            $enemyShips['threeDeck']['shipCount'] === 0 &&
            $enemyShips['fourDeck']['shipCount'] === 0) {
            return $playerData['player_name'];
        }
    }
}