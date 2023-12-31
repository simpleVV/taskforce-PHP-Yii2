<?php

/** 
 * @var yii\web\View $this
 * @var User $model
 */

use yii\helpers\Html;
use app\helpers\HtmlHelper;

$user = Yii::$app->user->identity;

$this->title = 'Профиль пользователя';
?>

<div class="left-column">
    <h3 class="head-main"><?= Html::encode($model->name); ?></h3>
    <div class="user-card">
        <div class="photo-rate">
            <img class="card-photo" src=<?= $model->avatar_path ?> width="191" height="190" alt="Фото пользователя" />
            <div class="card-rate">
                <?= HtmlHelper::getStarElements($model->rating, false, 'big') ?>
                <span class="current-rate">
                    <?= $model->rating ?>
                </span>
            </div>
        </div>
        <p class="user-description">
            <?= Html::encode($model->about); ?>
        </p>
    </div>
    <div class="specialization-bio">
        <div class="specialization">
            <p class="head-info">Специализации</p>
            <ul class="special-list">
                <?php foreach ($model->categories as $category) : ?>
                    <li class="special-item">
                        <a href="<?= Yii::$app->urlManager->createUrl(['tasks/', 'FilterTasks[categoryId][]' => $category->id]); ?>" class="link link--regular"><?= Html::encode($category->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="bio">
            <p class="head-info">Био</p>
            <p class="bio-info">
                <span class="country-info">Россия</span>,
                <span class="town-info">
                    <?= $model->city->name; ?>
                </span>
                <?php if ($model->bd_date) : ?>
                    , <span class="age-info">
                        <?= $model->getAge(); ?>
                    </span> лет
                <?php endif; ?>
            </p>
        </div>
    </div>
    <?php if ($model->reviews) : ?>
        <h4 class="head-regular">Отзывы заказчиков</h4>
        <?php foreach ($model->reviews as $review) : ?>
            <?= $this->render('//partials/_review-card', ['model' => $review]); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="right-column">
    <div class="right-card black">
        <h4 class="head-card">Статистика исполнителя</h4>
        <dl class="black-list">
            <dt>Всего заказов</dt>
            <dd>
                <?= $model->getCompleteTasks(); ?> выполнено,
                <?= $model->fail_tasks; ?> провалено</dd>
            <dt>Место в рейтинге</dt>
            <dd>
                <?= $model->position; ?> место
            </dd>
            <dt>Дата регистрации</dt>
            <dd>
                <?= Yii::$app->formatter->asDate($model->dt_registration); ?>
            </dd>
            <dt>Статус</dt>
            <?= $model->haveActiveTask()
                ? '<dd>Занят</dd>'
                : '<dd>Открыт для новых заказов</dd>'
            ?>
        </dl>
    </div>
    <?php if ($model->showContacts($user->id)) : ?>
        <div class="right-card white">
            <h4 class="head-card">Контакты</h4>
            <ul class="enumeration-list">
                <li class="enumeration-item">
                    <a href="#" class="link link--block link--phone">
                        <?= Html::encode($model->phone); ?>
                    </a>
                </li>
                <li class="enumeration-item">
                    <a href="#" class="link link--block link--email">
                        <?= Html::encode($model->email); ?>
                    </a>
                </li>
                <li class="enumeration-item">
                    <a href="#" class="link link--block link--tg">
                        <?= Html::encode($model->telegram); ?>
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>