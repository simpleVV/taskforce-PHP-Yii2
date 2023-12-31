<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;

class LoginForm extends Model
{
    public $email;
    public $password;

    private $user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email'], 'email'],
            ['password', 'validatePassword']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
            'password' => 'Пароль'
        ];
    }

    /**
     * Checking the user's entered password for a 
     * match with the password from the database
     * 
     * @param string $attribute model attribute
     * @param $params
     * @return void
     */
    public function validatePassword(string $attribute, $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный email или пароль.');
            }
        }
    }

    /**
     * Login user if the validate is passed 
     * 
     * @return bool whether the user is logged in
     */
    public function login(): bool
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser());
        }
    }

    /**
     * Get user record in the database by email
     * 
     * @return ?User user records if there is one in the database
     * or null
     */
    private function getUser(): ?User
    {
        if ($this->user === null) {
            $this->user = User::findByEmail($this->email);
        }

        return $this->user;
    }
}
