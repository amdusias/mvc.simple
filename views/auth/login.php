<?php

use app\core\form\Form;

$this->title = $title;
?>

<div class="register h-100">
    <div class="content">
        <div class="content__block h-100">
            <img src="/assets/img/home.png" alt="#">
            <h4>Авторизуйтесь</h4>
            <div class="register__form">
                <?php $form = Form::begin('/login', 'post') ?>
                <?php echo $form->field($model, 'email')->emailField() ?>
                <?php echo $form->field($model, 'password')->passwordField() ?>
                <?php echo $form->field($model, 'captcha')->captchaField() ?>
                <button type="submit" class="btn btn-primary btn-block">Авторизоваться</button>
                <?php Form::end() ?>
            </div>
            <p>Вы не зарегистрированы? <a href="/register">Создайте аккаунт</a></p>
        </div>
    </div>
</div>