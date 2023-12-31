<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\ResponseQuery;

/**
 * This is the model class for table "responses".
 *
 * @property int $id
 * @property string $dt_creation
 * @property string $comment
 * @property int $price
 * @property int|null $is_approved
 * @property int $task_id
 * @property int $user_id
 *
 * @property Task $task
 * @property User $user
 */
class Response extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'responses';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'dt_creation' => 'Дата создания',
            'comment' => 'Комментарий',
            'price' => 'Цена',
            'is_approved' => 'Одобрено',
            'is_deny' => 'Отказано ',
            'task_id' => 'Задание',
            'user_id' => 'Пользователь',
        ];
    }

    /**
     * Assigns the response approved property to true.
     * 
     * @return void
     */
    public function approvedResponse()
    {
        $this->is_approved = true;
    }

    /**
     * Assigns the response deny property to true.
     * 
     * @return void
     */
    public function denyResponse()
    {
        $this->is_deny = true;
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery|TaskQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return ResponseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ResponseQuery(get_called_class());
    }
}
