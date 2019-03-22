<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 26.02.19
 * Time: 18:58
 */

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Cell extends ActiveRecord
{
    const EMPTY_CELL = 'empty';
    const BUSY_CELL = 'busy';
    const WRECKED_CELL = 'wrecked';
    const MISS_CELL = 'miss';
    const HIT_CELL = 'hit';

    public static function tableName()
    {
        return 'cell';
    }

    public function attributeLabels()
    {
        return [
            'game_id' => 'ID игры',
            'player_id' => 'ID игрока',
            'coordinate' => '',
            'state' => 'Состояние палубы',
        ];
    }

    public function rules()
    {
        return [
            [['player_id', 'game_id'], 'required'],
            [['state', 'coordinate'], 'default', 'value' => 'error'],
        ];
    }

    public function getCellsData($playerId, $gameId)
    {
        return Cell::find()->asArray()->where(['player_id' => $playerId, 'game_id' => $gameId])->all();
    }

    public function setTableDatabase($gameData)
    {
        for ($line = 1; $line <= Field::FIELD_SIZE; $line++) {
            foreach (Field::COORDINATES as $column => $columnValue) {
                if (Yii::$app->request->post($columnValue . $line) == self::BUSY_CELL) {
                    Yii::$app->db->createCommand()->
                    batchInsert(
                        $this->tableName(),
                        ['game_id', 'player_id', 'coordinate', 'state'],
                        [
                            [
                                $gameData['id'],
                                $gameData['player_move'],
                                $columnValue . $line,
                                self::BUSY_CELL
                            ],
                        ]
                    )->execute();
                }
            }
        }
    }
    public function setCellDatabase($cellData, $coordinate, $state)
    {
        foreach ($cellData as $key => $data) {
            if ($coordinate == $data['coordinate'] && $state == self::WRECKED_CELL) {
                $cell = Cell::find()->where(['coordinate' => $coordinate, 'player_id' => $cellData[$key]['player_id'], 'game_id' => $cellData[$key]['game_id']])->one();
                $cell->state = $state;
                $cell->save();
            } elseif ($state == self::MISS_CELL) {
                $this->game_id = $cellData[$key]['game_id'];
                $this->player_id = $cellData[$key]['player_id'];
                $this->coordinate = $coordinate;
                $this->state = $state;
                $this->save();
            }
        }
    }

    public function getCellsCoordinatesAndStatesByPost()
    {
        $coordinates = array();

        for ($line = 1; $line <= Field::FIELD_SIZE; $line++) {
            foreach (Field::COORDINATES as $column => $columnValue) {
                if (Yii::$app->request->post($columnValue . $line) == self::BUSY_CELL) {
                    $coordinates[$line][$column] = self::BUSY_CELL;
                } elseif (Yii::$app->request->post($columnValue . $line) == self::MISS_CELL) {
                    $coordinates[$line][$column] = self::MISS_CELL;
                } elseif (Yii::$app->request->post($columnValue . $line) == self::WRECKED_CELL) {
                    $coordinates[$line][$column] = self::WRECKED_CELL;
                } else
                    $coordinates[$line][$column] = self::EMPTY_CELL;
            }
        }

        return $coordinates;
    }

    protected $ships = [
        'singleDeck' => ['shipCount' => 0, 'location' => array()],
        'doubleDeck' => ['shipCount' => 0, 'location' => array()],
        'threeDeck' => ['shipCount' => 0, 'location' => array()],
        'fourDeck' => ['shipCount' => 0, 'location' => array()],
    ];
    public function setShipsLocationAndCount($shipsLocation, $shipsCount)	//Идут две функции, работающие с массивом кораблей
    {
        if (!empty($shipsLocation)) {
            switch (count($shipsLocation)) {
                case 1:
                    $this->ships['singleDeck']['location'] = array_merge($this->ships['singleDeck']['location'], $shipsLocation);
                    break;
                case 2:
                    $this->ships['doubleDeck']['location'] = array_merge($this->ships['doubleDeck']['location'], $shipsLocation);
                    break;
                case 3:
                    $this->ships['threeDeck']['location'] = array_merge($this->ships['threeDeck']['location'], $shipsLocation);
                    break;
                case 4:
                    $this->ships['fourDeck']['location'] = array_merge($this->ships['fourDeck']['location'], $shipsLocation);
                    break;
            }
        } elseif (!empty($shipsCount)) {
            switch ($shipsCount) {
                case '0':
                    break;
                case '1':
                    $this->ships['singleDeck']['shipCount']++;
                    break;
                case '2':
                    $this->ships['doubleDeck']['shipCount']++;
                    break;
                case '3':
                    $this->ships['threeDeck']['shipCount']++;
                    break;
                case '4':
                    $this->ships['fourDeck']['shipCount']++;
                    break;
            }
        }
    }

    public function getShipsLocationAndCount($cells)
    {
        foreach ($cells as $rowKey => $row) {
            foreach ($row as $symbolKey => $state) {
                $locationShip = array();
                $shipCount = 0;

                if (!empty($cells[$rowKey + 1][$symbolKey]) &&
                    $cells[$rowKey + 1][$symbolKey] == self::BUSY_CELL &&
                    $cells[$rowKey][$symbolKey] == self::BUSY_CELL) {
                    for ($i = $rowKey; $i <= count($cells); $i++) { //Пробегаемся по массиву поля по вертикали
                        if ($cells[$i][$symbolKey] == self::BUSY_CELL) {
                            $locationShip[$i] = Field::COORDINATES[$symbolKey] . $i;
                            $shipCount++;
                            $cells[$i][$symbolKey] = self::EMPTY_CELL;
                        } else break;
                    }

                    $this->setShipsLocationAndCount($locationShip, null);
                } elseif (!empty($cells[$rowKey][$symbolKey + 1]) &&
                    $cells[$rowKey][$symbolKey + 1] == self::BUSY_CELL &&
                    $cells[$rowKey][$symbolKey] == self::BUSY_CELL) {
                    for ($i = $symbolKey; $i <= count($row); $i++) { //Пробегаемся по массиву поля по горизонтали
                        if ($cells[$rowKey][$i] == self::BUSY_CELL) {
                            $locationShip[$i] = Field::COORDINATES[$i] . $rowKey;
                            $shipCount++;
                            $cells[$rowKey][$i] = self::EMPTY_CELL;
                        } else break;
                    }

                    $this->setShipsLocationAndCount($locationShip, null);
                } elseif ($cells[$rowKey][$symbolKey] == self::BUSY_CELL) { //Проверяем текущую ячейку поля
                    $shipCount++;
                    array_push($locationShip, Field::COORDINATES[$symbolKey] . $rowKey);
                    $cells[$rowKey][$symbolKey] = self::EMPTY_CELL;

                    $this->setShipsLocationAndCount($locationShip, null);
                }

                $this->setShipsLocationAndCount(null, $shipCount);
            }
        }

        return $this->ships;
    }
}