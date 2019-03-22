<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 26.02.19
 * Time: 8:10
 */

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Game;
use app\models\Field;
use app\models\Cell;

class GameController extends Controller
{
    public function actionFill()
    {
        $field = new Field();
        $cell = new Cell();
        $game = new Game();

        $gameData = $game->getGameData();

        if ($field->load(Yii::$app->request->post()) ) {
            $cell->setTableDatabase($gameData);

            $cells = $cell->getCellsCoordinatesAndStatesByPost();
            $ships = $cell->getShipsLocationAndCount($cells);

            $buildError = $field->getBuildError($cells, $ships);

            if (empty($buildError)) {
                $field->save();

                $firstFieldData = $field->getFieldData(Field::FIRST_PLAYER_ID, $gameData['id']);
                $secondFieldData = $field->getFieldData(Field::SECOND_PLAYER_ID, $gameData['id']);

                if (isset($firstFieldData) && empty($secondFieldData)) {
                    $game->setGameDatabase($field->game_id, Field::SECOND_PLAYER_ID, null);

                    $this->refresh();
                } elseif (isset($firstFieldData) && isset($secondFieldData)) {
                    $game->setGameDatabase($field->game_id, Field::FIRST_PLAYER_ID, null);

                    return $this->redirect('game');
                }
            } else {
                $cell->deleteAll(['game_id' => $gameData['id'], 'player_id' => $gameData['player_move']]);

                Yii::$app->session->setFlash('error', $buildError);
            }
        }

        return $this->render('fill', compact('cell', 'field', 'gameData'));
    }

    public function actionGame()
    {
        $cell = new Cell();
        $field = new Field();
        $game = new Game();

        $gameData = $game->getGameData();

        $firstFieldData = $field->getFieldData(Field::FIRST_PLAYER_ID, $gameData['id']);
        $secondFieldData = $field->getFieldData(Field::SECOND_PLAYER_ID, $gameData['id']);

        if ($firstFieldData['player_id'] == $gameData['player_move']) {
            $playerData = $firstFieldData;
            $enemyData = $secondFieldData;
        } else {
            $playerData = $secondFieldData;
            $enemyData = $firstFieldData;
        }
        $enemyCellData = $cell->getCellsData($enemyData['player_id'], $gameData['id']);

        if ($field->step(Yii::$app->request->post(), $enemyCellData) ) {
            $enemyCells = $cell->getCellsCoordinatesAndStatesByPost();
            $enemyShips = $cell->getShipsLocationAndCount($enemyCells);

            if ($game->getWinner($playerData, $enemyShips) ) {
                $game->setGameDatabase($gameData['id'], null, $playerData['player_name']);

                return $this->redirect('winner');
            } else {
                $hitCoordinate = $field->getHitCoordinate();
                $hitState = $field->getHitState();

                if ($hitState == Cell::HIT_CELL) {
                    $cell->setCellDatabase($enemyCellData, $hitCoordinate, Cell::WRECKED_CELL);
                } elseif ($hitState == Cell::MISS_CELL) {
                    $cell->setCellDatabase($enemyCellData, $hitCoordinate, Cell::MISS_CELL);
                    $game->setGameDatabase($gameData['id'], $enemyData['player_id'], null);

                    $this->refresh();
                }
            }
        } elseif (Yii::$app->request->post()) {
            Yii::$app->session->setFlash('error', 'Нельзя стрелять в несколько ячеек или выбранная ячейка уже подбита.');
        }

        return $this->render('game', compact('cell', 'playerData', 'enemyData', 'gameData'));
    }

    public function actionWinner()
    {
        $game = new Game();

        $gameData = $game->getGameData();

        return $this->render('winner', compact('gameData'));
    }
}