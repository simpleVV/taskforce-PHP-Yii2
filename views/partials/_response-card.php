<?php

/** 
 * @var yii\web\View $this 
 * @var Response $model
 * */

use yii\helpers\Html;
use yii\helpers\Url;

use app\helpers\HtmlHelper;

$user = Yii::$app->user->identity;
?>

<div class="response-card">
    <img class="customer-photo" src=<?= $model->user->avatar_path ?> width="146" height="156" alt="Фото заказчиков" />
    <div class="feedback-wrapper">
        <a href="<?= Url::to(["user/view", "id" => $model->user_id]); ?>" class="link link--block link--big">
            <?= Html::encode($model->user->name); ?>
        </a>
        <div class="response-wrapper">
            <?= HtmlHelper::getStarElements($model->user->rating, false) ?>
            <p class="reviews">
                <?= Html::encode(count($model->user->reviews)); ?>
                отзыва
            </p>
        </div>
        <p class="response-message">
            <?= Html::encode($model->comment); ?>
        </p>
    </div>
    <div class="feedback-wrapper">
        <p class="info-text">
            <span class="current-time">
                <?= Yii::$app->formatter->asRelativeTime($model->dt_creation); ?>
            </span>
        </p>
        <p class="price price--small">
            <?= Html::encode($model->price); ?> ₽
        </p>
    </div>

    <?php

    $is_visible = !$model->task->performer_id && !$model->is_deny
    ?>

    <?php if ($model->task->client_id === $user->id && $is_visible) : ?>
        <div class="button-popup">
            <a href="<?= Url::to(["responses/accept", "id" => $model->id]); ?>" class="button button--blue button--small">Принять</a>
            <a href="<?= Url::to(["responses/deny", "id" => $model->id]); ?>" class="button button--orange button--small">Отказать</a>
        </div>
    <?php endif; ?>

</div>