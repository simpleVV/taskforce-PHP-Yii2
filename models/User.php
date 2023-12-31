<?php

namespace app\models;

use Yii;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $dt_registration
 * @property string $email
 * @property string $name
 * @property string $password
 * @property int $city_id
 * @property string|null $bd_date
 * @property string|null $avatar_path
 * @property string|null $about
 * @property int|null $is_performer
 * @property int|null $hide_contacts
 * @property int|null $hide_profile
 * @property string|null $phone
 * @property string|null $telegram
 *
 * @property City $city
 * @property Response[] $responses
 * @property Review[] $reviews
 * @property Task[] $tasks
 * @property Task[] $clientTasks
 * @property UserCategory[] $userCategories
 */
class User extends BaseUser implements IdentityInterface
{
    private const MIN_PASSWORD_LENGTH = 8;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'dt_registration' => 'Дата регистрации',
            'email' => 'Email',
            'name' => 'Имя',
            'password' => 'Пароль',
            'old_password' => 'Старый пароль',
            'new_password' => 'Новый пароль',
            'city_id' => 'Город',
            'bd_date' => 'Дата рождения',
            'avatar_path' => 'Avatar Path',
            'categories' => 'Выбранные категории',
            'about' => 'Информация о себе',
            'hide_contacts' => 'Показывать контакты только заказчику',
            'hide_profile' => 'Hide Profile',
            'phone' => 'Номер телефона',
            'telegram' => 'Telegram',
        ];
    }

    /**
     * Checks the user for active tasks
     * 
     * @return bool true - if user has active tasks
     */
    public function haveActiveTask(): bool
    {
        return $this->getTasks()
            ->joinWith('status', true, 'INNER JOIN')
            ->where(['statuses.id' => Status::STATUS_IN_PROGRESS])->exists();
    }

    /**
     * Finde user by email in DB
     * 
     * @param string $email by which the database is searched
     * @return ?User user data if the user is found
     * and null if not
     */
    public static function findByEmail(string $email): ?User
    {
        return User::find()
            ->where(['email' => $email])
            ->one();
    }

    /**
     * increase user fail tasks count by 1
     * 
     * @return void
     */
    public function increaseFailTasks(): void
    {
        $this->updateCounters(['fail_tasks' => 1]);
    }

    /**
     * Get the user age in years
     * 
     * @return ?int user age in years
     */
    public function getAge(): ?int
    {
        $result = null;

        if ($this->bd_date) {
            $bd = new \DateTime($this->bd_date);
            $now = new \DateTime();
            $diff = $now->diff($bd);
            $result = $diff->y;
        }

        return $result;
    }

    /**
     * Сalculation of user rating
     * sum of all ratings from reviews / (number of reviews + counter of failed
     * tasks)
     * 
     * @return ?float user rating or null if user has no rating
     */
    public function getRating(): ?float
    {
        $rating = null;
        $reviewsCount = $this->getReviews()
            ->count();

        if ($reviewsCount) {
            $rate = $this->getReviews()->sum('rate');
            $rating = round($rate / ($reviewsCount + $this->fail_tasks), 2);
        }
        return $rating;
    }

    /**
     * Find number of completed user tasks
     * 
     * @return ?int number of completed user tasks or null if user has no
     * complete tasks
     */
    public function getCompleteTasks(): ?int
    {
        return $this->getTasks()
            ->where(['status_id' => Status::STATUS_COMPLETE])
            ->count();
    }

    /**
     * Find the user's position in the rating 
     * 
     * @return ?int user's position in the rating
     */
    public function getPosition(): int|float
    {
        $query = $this->getReviews();
        $totalCount = $query->count();
        $position = 0;

        if ($query->exists()) {
            $highestRating = $query
                ->where(['rate' => Review::HIGHEST_RATING])
                ->count() * Review::HIGHEST_RATING;
            $goodRating = $query
                ->where(['rate' => Review::GOOD_RATING])
                ->count() * Review::GOOD_RATING;
            $averageRating = $query
                ->where(['rate' => Review::AVERAGE_RATING])
                ->count() * Review::AVERAGE_RATING;
            $poorRating = $query
                ->where(['rate' => Review::POOR_RATING])
                ->count() * Review::POOR_RATING;
            $lowestRating = $query
                ->where(['rate' => Review::LOWEST_RATING])
                ->count() * Review::LOWEST_RATING;

            $position = ($highestRating + $goodRating + $averageRating + $poorRating + $lowestRating) / $totalCount;
        }

        return round($position);
    }

    /**
     * Save user in DB
     * 
     * @param string $name user name from VK
     * @param string $email user email from VK
     * @param int $city user city title from VK
     * @return bool true - if the user's data is successfully saved
     */
    public function saveUserFromVk(string $name, string $email, int $cityId): bool
    {
        $password = Yii::$app->security->generateRandomString(self::MIN_PASSWORD_LENGTH);

        $this->name = $name;
        $this->email = $email;
        $this->city_id = $cityId;
        $this->password = Yii::$app->security->generatePasswordHash($password);;

        return $this->save();
    }

    /**
     * show user contacts
     * 
     * @param int $clientId client id
     * @return bool show contacts if the user does not hide contacts or shows
     * contacts only to his clients
     */
    public function showContacts(int $clientId): bool
    {
        $task = Task::findOne(['client_id' => $clientId]);

        return $this->hide_contacts
            ? !empty($task)
            : true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])->viaTable('user_categories', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[City]].
     *
     * @return \yii\db\ActiveQuery|CityQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Gets query for [[Responses]].
     *
     * @return \yii\db\ActiveQuery|ResponseQuery
     */
    public function getResponses()
    {
        return $this->hasMany(Response::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Reviews]].
     *
     * @return \yii\db\ActiveQuery|ReviewQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Review::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery|TaskQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['performer_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery|TaskQuery
     */
    public function getClientTasks()
    {
        return $this->hasMany(Task::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserCategories]].
     *
     * @return \yii\db\ActiveQuery|UserCategoryQuery
     */
    public function getUserCategories()
    {
        return $this->hasMany(UserCategory::class, ['user_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }
}
