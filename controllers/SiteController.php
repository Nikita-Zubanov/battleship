<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Game;


class SiteController extends Controller
{
    public function actionIndex()
    {
        $game = new Game();

        if ($game->load(Yii::$app->request->post())) {
            if ($game->save()) {
                return $this->redirect('fill');
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }

        return $this->render('index', compact('game'));
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}