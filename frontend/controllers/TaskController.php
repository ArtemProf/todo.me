<?php

namespace app\controllers;

use Yii;
use common\models\user\User;
use common\models\Task;
use yii\rest\ActiveController;
use yii\web\Response;


class TaskController extends ActiveController
{
    public $modelClass = Task::class;
    public $user = null;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        if (Yii::$app->user->isGuest) {
            return $this->redirect('/login');
        }

        $this->user = User::current();

        return $this;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['index']);
        unset($actions['view']);

        return $actions;
    }

    public function actionIndex()
    {
        return $this->user->getTasks()->all();
    }

    public function actionCreate()
    {
        $task = new Task();
        $task->uid = $this->user->id;
        $task->description = Yii::$app->getRequest()->post('description');
        $task->dueDate = date('Y-m-d');
        $task->state = 0;
        $task->itemGroup = 0;

        $task->save();

        return $task;
    }

    public function actionDelete($id = null)
    {
        Task::findOne(['id' => $id])->delete();

        return $this->user->getTasks()->all();
    }

    public function actionView()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect('/login');
        }

        Yii::$app->response->format = Response::FORMAT_HTML;

        $users = [];
        if( $this->user->isAdmin() ){
            $users = User::getUsersList();
        }

        return $this->render('index',[
            'user' => $this->user,
            'users' => $users
        ]);
    }

    protected function verbs()
    {
        return [
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
            'index'  => ['GET'],
            'view'   => ['GET'],
        ];
    }


}