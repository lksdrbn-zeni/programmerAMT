<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<nav class="navbar navbar-expand-lg fixed-top app-navbar">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo esc_url(home_url('/')); ?>#top">
            <span class="logo-mark"><i class="bi bi-chat-heart-fill"></i></span>
            <span class="fw-bold">Психология общения</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Открыть меню">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="#course">Программа</a></li>
                <li class="nav-item"><a class="nav-link" href="#plan">План</a></li>
                <li class="nav-item"><a class="nav-link" href="#theory">Материалы</a></li>
                <li class="nav-item"><a class="nav-link" href="#results">Результаты</a></li>
                <li class="nav-item"><a class="nav-link" href="#tests">Тест</a></li>
                <li class="nav-item"><a class="btn btn-success rounded-pill px-4" href="<?php echo esc_url(wp_login_url(admin_url())); ?>"><i class="bi bi-shield-lock me-1"></i> Вход преподавателя</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileNavLabel">Меню сайта</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
    </div>
    <div class="offcanvas-body d-grid gap-2">
        <a class="btn btn-outline-success" href="#course" data-bs-dismiss="offcanvas">Программа</a>
        <a class="btn btn-outline-success" href="#plan" data-bs-dismiss="offcanvas">Тематический план</a>
        <a class="btn btn-outline-success" href="#theory" data-bs-dismiss="offcanvas">Материалы</a>
        <a class="btn btn-outline-success" href="#results" data-bs-dismiss="offcanvas">Результаты</a>
        <a class="btn btn-outline-success" href="#tests" data-bs-dismiss="offcanvas">Тест</a>
        <a class="btn btn-success" href="<?php echo esc_url(wp_login_url(admin_url())); ?>" data-bs-dismiss="offcanvas">Вход преподавателя</a>
    </div>
</div>
