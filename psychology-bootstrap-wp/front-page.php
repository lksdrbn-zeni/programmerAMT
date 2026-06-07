<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
$materials = psych_get_materials_data();
$questions = psych_get_questions_data();
$terms = psych_default_terms();
$overview = psych_course_overview();
$plan = psych_course_plan();
$learning = psych_learning_results();
$sources = psych_reference_sources();
?>
<header id="top" class="hero overflow-hidden">
    <div class="hero-shape hero-shape-one"></div>
    <div class="hero-shape hero-shape-two"></div>
    <div class="container position-relative py-5">
        <div class="row align-items-center g-5 pt-5">
            <div class="col-lg-7">
                <span class="badge rounded-pill text-bg-light border border-success-subtle text-success fw-semibold px-3 py-2"><i class="bi bi-mortarboard me-1"></i> <?php echo esc_html($overview['specialty']); ?></span>
                <h1 class="display-4 fw-black mt-3 mb-3">ОГСЭ.03 «Психология общения»</h1>
                <p class="lead text-muted mb-4">Полный учебный сайт по рабочей программе: тематический план на 50 часов, материалы по всем разделам, практические занятия, контрольные вопросы и тест для самопроверки.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#plan" class="btn btn-success btn-lg rounded-pill px-4"><i class="bi bi-table me-1"></i> Смотреть план</a>
                    <a href="#theory" class="btn btn-outline-success btn-lg rounded-pill px-4"><i class="bi bi-book me-1"></i> Изучать материалы</a>
                </div>
                <div class="row g-3 mt-4 hero-stats">
                    <div class="col-4"><div class="mini-stat"><strong><?php echo esc_html($overview['total_hours']); ?></strong><span>часов</span></div></div>
                    <div class="col-4"><div class="mini-stat"><strong><?php echo esc_html(count($materials)); ?></strong><span>карточек</span></div></div>
                    <div class="col-4"><div class="mini-stat"><strong><?php echo esc_html(count($questions)); ?></strong><span>вопросов</span></div></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="app-preview shadow-lg">
                    <div class="preview-topbar"><span></span><span></span><span></span><strong class="ms-2">Учебный курс</strong></div>
                    <div class="p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="preview-icon"><i class="bi bi-window-sidebar"></i></div>
                            <div><p class="text-muted small mb-1">Содержание сайта</p><h3 class="h5 mb-0">Рабочая программа + тест</h3></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6"><div class="preview-card"><i class="bi bi-journal-text"></i><span>Теория</span></div></div>
                            <div class="col-6"><div class="preview-card"><i class="bi bi-card-checklist"></i><span>Практика</span></div></div>
                            <div class="col-6"><div class="preview-card"><i class="bi bi-ui-checks"></i><span>Тест</span></div></div>
                            <div class="col-6"><div class="preview-card"><i class="bi bi-shield-lock"></i><span>Админка</span></div></div>
                        </div>
                        <div class="alert alert-success border-0 mt-4 mb-0 rounded-4"><i class="bi bi-check-circle me-1"></i> Наполнение перенесено по рабочей программе ОГСЭ.03.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<main>
    <section id="course" class="section-pad bg-white">
        <div class="container">
            <div class="row g-4 align-items-end mb-4">
                <div class="col-lg-7">
                    <span class="section-label">Рабочая программа</span>
                    <h2 class="section-heading">Объём дисциплины и виды учебной работы</h2>
                    <p class="text-muted mb-0">Сайт построен как электронное сопровождение дисциплины <?php echo esc_html($overview['title']); ?>. Студент видит материалы и тест, преподаватель управляет наполнением через WordPress.</p>
                </div>
                <div class="col-lg-5"><div class="d-flex flex-wrap gap-2 justify-content-lg-end"><span class="chip"><i class="bi bi-clock"></i> <?php echo esc_html($overview['total_hours']); ?> часов</span><span class="chip"><i class="bi bi-people"></i> <?php echo esc_html($overview['competencies']); ?></span><span class="chip"><i class="bi bi-check2-square"></i> <?php echo esc_html($overview['attestation']); ?></span></div></div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-3"><div class="feature-card h-100"><span class="feature-icon"><i class="bi bi-book-half"></i></span><h3>Теория</h3><p><?php echo esc_html($overview['theory_hours']); ?> часов теоретического обучения по основам общения, деловой этике, конфликтам и невербальному поведению.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="feature-card h-100"><span class="feature-icon"><i class="bi bi-lightbulb"></i></span><h3>Практика</h3><p><?php echo esc_html($overview['practice_hours']); ?> часов практической подготовки: тренинги, деловые игры, письма, публичное выступление.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="feature-card h-100"><span class="feature-icon"><i class="bi bi-person-check"></i></span><h3>Самостоятельная работа</h3><p><?php echo esc_html($overview['self_hours']); ?> часа на подготовку к дифференцированному зачёту и повторение ключевых тем.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="feature-card h-100"><span class="feature-icon"><i class="bi bi-sliders"></i></span><h3>Администрирование</h3><p>Материалы и вопросы редактируются через настоящую админ-панель WordPress без статичного редактора на сайте.</p></div></div>
            </div>
        </div>
    </section>

    <section id="plan" class="section-pad bg-soft">
        <div class="container">
            <div class="section-center mb-4"><span class="section-label">Тематический план</span><h2 class="section-heading">Содержание учебной дисциплины</h2><p class="text-muted">Таблица повторяет логику рабочей программы: разделы, темы, номера уроков, содержание, часы и компетенции.</p></div>
            <div class="content-card p-3 p-lg-4">
                <div class="table-responsive">
                    <table class="table course-plan-table align-middle mb-0">
                        <thead><tr><th>Раздел / тема</th><th>Уроки</th><th>Содержание</th><th>Часы</th><th>Компетенции</th></tr></thead>
                        <tbody>
                        <?php foreach ($plan as $row) : ?>
                            <?php if ($row['type'] === 'section') : ?>
                                <tr class="plan-section-row"><td colspan="3"><strong><?php echo esc_html($row['section']); ?></strong></td><td><strong><?php echo esc_html($row['hours']); ?></strong></td><td><?php echo esc_html($row['competencies']); ?></td></tr>
                            <?php else : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($row['title']); ?></strong></td>
                                    <td><?php echo esc_html($row['lessons'] ?? ''); ?></td>
                                    <td><?php echo esc_html($row['content']); ?></td>
                                    <td><?php echo esc_html($row['hours']); ?></td>
                                    <td><?php echo esc_html($row['competencies']); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section id="theory" class="section-pad bg-white">
        <div class="container">
            <div class="section-center mb-4"><span class="section-label">Материалы сайта</span><h2 class="section-heading">Все темы и практические занятия</h2><p class="text-muted">Карточки можно фильтровать по разделам. Полный текст открывается в модальном окне.</p></div>
            <ul class="nav nav-pills justify-content-center gap-2 mb-4" id="categoryTabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-filter="all" type="button">Все</button></li>
                <?php foreach ($terms as $slug => $name) : ?>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-filter="<?php echo esc_attr($slug); ?>" type="button"><?php echo esc_html($name); ?></button></li>
                <?php endforeach; ?>
            </ul>
            <div id="materialsGrid" class="row g-4">
                <?php foreach ($materials as $item) : ?>
                    <div class="col-md-6 col-xl-4 material-item" data-category="<?php echo esc_attr($item['category']); ?>">
                        <article class="theory-card h-100" data-title="<?php echo esc_attr($item['title']); ?>" data-text="<?php echo esc_attr($item['text']); ?>" data-example="<?php echo esc_attr($item['example']); ?>">
                            <div class="d-flex align-items-start gap-3 mb-3"><span class="feature-icon mb-0"><i class="bi <?php echo esc_attr($item['icon']); ?>"></i></span><div><span class="badge rounded-pill text-bg-success-subtle text-success mb-2"><?php echo esc_html($item['accent']); ?></span><h3><?php echo esc_html($item['title']); ?></h3></div></div>
                            <p><?php echo esc_html(wp_trim_words($item['text'], 32)); ?></p>
                            <button class="btn btn-outline-success rounded-pill mt-3 open-material" type="button">Открыть материал</button>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="results" class="section-pad bg-soft">
        <div class="container">
            <div class="section-center mb-4"><span class="section-label">Контроль и результаты</span><h2 class="section-heading">Что должен знать и уметь студент</h2><p class="text-muted">Раздел нужен для полного соответствия сайта рабочей программе: знания, умения и формы контроля.</p></div>
            <div class="row g-4">
                <?php foreach ($learning as $block) : ?>
                    <div class="col-lg-4"><div class="summary-card h-100 p-4"><h3 class="h5 fw-bold mb-3"><?php echo esc_html($block['title']); ?></h3><ul class="clean-list mb-0"><?php foreach ($block['items'] as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul></div></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="tests" class="section-pad bg-white">
        <div class="container">
            <div class="section-center mb-4"><span class="section-label">Проверка знаний</span><h2 class="section-heading">Интерактивный тест по всей программе</h2><p class="text-muted">Тест охватывает основные темы: общение, этика, речевой этикет, конфликты, невербальное поведение и верификация ложной информации.</p></div>
            <div class="quiz-shell">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h3 class="h4 mb-1">Самопроверка по дисциплине</h3><p class="text-muted mb-0">Вопросы можно редактировать в разделе «Психология: тесты».</p></div><span class="badge rounded-pill text-bg-success px-3 py-2" id="knowledgeCountBadge"><?php echo esc_html(count($questions)); ?> вопросов</span></div>
                <div id="knowledgeQuiz" class="quiz-list"></div>
                <div class="d-flex flex-wrap gap-2 mt-4"><button class="btn btn-success rounded-pill px-4" id="checkKnowledge" type="button"><i class="bi bi-check2-circle me-1"></i> Проверить</button><button class="btn btn-outline-success rounded-pill px-4" id="resetKnowledge" type="button"><i class="bi bi-arrow-repeat me-1"></i> Сбросить</button></div>
                <div id="knowledgeResult" class="mt-4"></div>
            </div>
        </div>
    </section>

    <section id="sources" class="section-pad bg-soft">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4"><span class="section-label">Источники</span><h2 class="section-heading">Учебная литература</h2><p class="text-muted">Список источников вынесен на сайт, чтобы ресурс выглядел как полноценное сопровождение дисциплины.</p></div>
                <div class="col-lg-8"><div class="content-card p-4"><ol class="mb-0 source-list"><?php foreach ($sources as $source) : ?><li><?php echo esc_html($source); ?></li><?php endforeach; ?></ol></div></div>
            </div>
        </div>
    </section>
</main>
<div class="modal fade" id="materialModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-success-subtle border-0"><h5 class="modal-title" id="materialModalLabel">Материал</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button></div>
            <div class="modal-body p-4"><p id="materialModalText" class="lead"></p><div class="alert alert-success rounded-4 border-0 mb-0"><strong>Пример:</strong> <span id="materialModalExample"></span></div></div>
        </div>
    </div>
</div>
<?php
get_footer();
