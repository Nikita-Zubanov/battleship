<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 25.02.19
 * Time: 14:00
 */

namespace app\models;

use yii\db\ActiveRecord;

class Field extends ActiveRecord
{
    const FIELD_SIZE = 10;
    const COORDINATES = [
        1 => 'А',
        2 => 'Б',
        3 => 'В',
        4 => 'Г',
        5 => 'Д',
        6 => 'Е',
        7 => 'Ж',
        8 => 'З',
        9 => 'И',
        10 => 'К',
        ];
    const FIRST_PLAYER_ID = 1;
    const SECOND_PLAYER_ID = 2;

    public static function tableName()
    {
        return 'field';
    }

    public function attributeLabels()
    {
        return [
            'game_id' => 'ID игры',
            'player_id' => 'ID игрока',
            'player_name' => 'Имя игрока',
        ];
    }

    public function rules()
    {
        return [
            [['game_id'], 'required'],
            [['player_id', 'player_name'], 'default', 'value' => 'error'],
        ];
    }

    public function getFieldData($playerId, $gameId)
    {
        return Field::find()->asArray()->where(['player_id' => $playerId, 'game_id' => $gameId])->one();
    }
    
    protected $hitCoordinate;
    protected $hitState;
    public function step($cells, $enemyCellData)
    {
        $hit = false;
        $this->hitCoordinate = null;

        foreach ($cells as $rowKey => $state) {     //Проверяем, что игрок кликнул лишь по одному checkbox'у
            if ($rowKey !== '_csrf') {
                if ($state == Cell::HIT_CELL && $hit) {
                    $hit = false;
                    break;
                } elseif ($state == Cell::HIT_CELL) {
                    $hit = true;
                    $this->hitCoordinate = $rowKey;
                }
            }
        }

        if($hit) {
            $hit = false;
            for ($i = 0; $i < count($enemyCellData); $i++){     //Проверяем, попал ли
                if ($this->hitCoordinate == $enemyCellData[$i]['coordinate'] &&
                    $enemyCellData[$i]['state'] == Cell::BUSY_CELL)
                {
                    $this->hitState = Cell::HIT_CELL;
                    $hit = true;

                    return $hit;
                } elseif ($this->hitCoordinate == $enemyCellData[$i]['coordinate'] &&
                    ($enemyCellData[$i]['state'] == Cell::WRECKED_CELL ||
                        $enemyCellData[$i]['state'] == Cell::MISS_CELL)) {
                    $hit = false;

                    return $hit;
                }
            }
            if (!$hit) {
                $hit = true;
                $this->hitState = Cell::MISS_CELL;

                return $hit;
            }
        }

        return $hit;
    }
    
    public function getHitCoordinate()
    {
        return $this->hitCoordinate;
    }

    public function getHitState()
    {
        return $this->hitState;
    }

    protected $buildError;
    public function setErrorPositioningShips($cells)
    {
        foreach ($cells as $rowKey => $row) {
            foreach ($row as $symbolKey => $state) {
                if (!empty($cells[$rowKey + 1][$symbolKey + 1]) &&
                    $cells[$rowKey][$symbolKey] == Cell::BUSY_CELL &&
                    $cells[$rowKey + 1][$symbolKey + 1] == Cell::BUSY_CELL) {
                    $this->buildError .= "Неправильное расположение кораблей! ";
                    return;
                } elseif (!empty($cells[$rowKey + 1][$symbolKey - 1]) &&
                    $cells[$rowKey][$symbolKey] == Cell::BUSY_CELL &&
                    $cells[$rowKey + 1][$symbolKey - 1] == Cell::BUSY_CELL) {
                    $this->buildError .= "Неправильное расположение кораблей! ";
                    return;
                }
            }
        }
    }

    public function setErrorCountingShips($ships)
    {
        if ($ships['singleDeck']['shipCount'] !== 4 ||
            $ships['doubleDeck']['shipCount'] !== 3 ||
            $ships['threeDeck']['shipCount'] !== 2 ||
            $ships['fourDeck']['shipCount'] !== 1) {
            $this->buildError .= "Проверьте количество кораблей и их расстановку! ";
        }
    }

    public function getBuildError($cells, $ships)
    {
        $this->setErrorPositioningShips($cells);
        $this->setErrorCountingShips($ships);

        return $this->buildError;
    }
}