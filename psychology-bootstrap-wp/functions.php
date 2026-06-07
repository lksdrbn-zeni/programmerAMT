<?php
if (!defined('ABSPATH')) {
    exit;
}

define('PSYCH_THEME_VERSION', '2.0.0');

function psych_theme_setup(): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => 'Главное меню']);
}
add_action('after_setup_theme', 'psych_theme_setup');

function psych_enqueue_assets(): void {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3');
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css', [], '1.11.3');
    wp_enqueue_style('psych-app', get_template_directory_uri() . '/assets/css/app.css', ['bootstrap'], PSYCH_THEME_VERSION);

    wp_enqueue_script('bootstrap-bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], '5.3.3', true);
    wp_enqueue_script('psych-app', get_template_directory_uri() . '/assets/js/app.js', ['bootstrap-bundle'], PSYCH_THEME_VERSION, true);
    wp_localize_script('psych-app', 'psychologySiteData', [
        'materials' => psych_get_materials_data(),
        'questions' => psych_get_questions_data(),
    ]);
}
add_action('wp_enqueue_scripts', 'psych_enqueue_assets');

function psych_register_post_types(): void {
    register_post_type('psych_material', [
        'labels' => [
            'name' => 'Материалы курса',
            'singular_name' => 'Материал курса',
            'add_new_item' => 'Добавить материал',
            'edit_item' => 'Редактировать материал',
            'menu_name' => 'Психология: материалы',
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'materials'],
    ]);

    register_post_type('psych_question', [
        'labels' => [
            'name' => 'Вопросы теста',
            'singular_name' => 'Вопрос теста',
            'add_new_item' => 'Добавить вопрос',
            'edit_item' => 'Редактировать вопрос',
            'menu_name' => 'Психология: тесты',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-editor-help',
        'supports' => ['title', 'page-attributes'],
    ]);

    register_taxonomy('psych_material_category', ['psych_material'], [
        'labels' => [
            'name' => 'Разделы курса',
            'singular_name' => 'Раздел курса',
            'menu_name' => 'Разделы курса',
        ],
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => 'course-section'],
    ]);
}
add_action('init', 'psych_register_post_types');

function psych_add_meta_boxes(): void {
    add_meta_box('psych_material_details', 'Параметры карточки материала', 'psych_material_meta_box', 'psych_material', 'normal', 'default');
    add_meta_box('psych_question_details', 'Варианты ответа', 'psych_question_meta_box', 'psych_question', 'normal', 'default');
}
add_action('add_meta_boxes', 'psych_add_meta_boxes');

function psych_material_meta_box(WP_Post $post): void {
    wp_nonce_field('psych_save_material_meta', 'psych_material_nonce');
    $accent = get_post_meta($post->ID, '_psych_accent', true);
    $icon = get_post_meta($post->ID, '_psych_icon', true) ?: 'bi-chat-dots';
    $example = get_post_meta($post->ID, '_psych_example', true);
    ?>
    <p><label for="psych_accent"><strong>Короткая метка</strong></label></p>
    <input type="text" class="widefat" id="psych_accent" name="psych_accent" value="<?php echo esc_attr($accent); ?>" placeholder="Например: Уроки 1–2 / 2 часа">
    <p><label for="psych_icon"><strong>Иконка Bootstrap Icons</strong></label></p>
    <input type="text" class="widefat" id="psych_icon" name="psych_icon" value="<?php echo esc_attr($icon); ?>" placeholder="bi-ear">
    <p><label for="psych_example"><strong>Практический пример</strong></label></p>
    <textarea class="widefat" id="psych_example" name="psych_example" rows="4" placeholder="Краткий пример применения темы"><?php echo esc_textarea($example); ?></textarea>
    <?php
}

function psych_question_meta_box(WP_Post $post): void {
    wp_nonce_field('psych_save_question_meta', 'psych_question_nonce');
    $options = get_post_meta($post->ID, '_psych_options', true);
    $options = is_array($options) ? $options : ['', '', '', ''];
    $correct = (int) get_post_meta($post->ID, '_psych_correct', true);
    for ($i = 0; $i < 4; $i++) {
        ?>
        <p><label for="psych_option_<?php echo esc_attr($i); ?>"><strong>Вариант <?php echo esc_html($i + 1); ?></strong></label></p>
        <input type="text" class="widefat" id="psych_option_<?php echo esc_attr($i); ?>" name="psych_options[]" value="<?php echo esc_attr($options[$i] ?? ''); ?>">
        <?php
    }
    ?>
    <p><label for="psych_correct"><strong>Номер правильного ответа</strong></label></p>
    <select id="psych_correct" name="psych_correct">
        <?php for ($i = 0; $i < 4; $i++) : ?>
            <option value="<?php echo esc_attr($i); ?>" <?php selected($correct, $i); ?>><?php echo esc_html($i + 1); ?></option>
        <?php endfor; ?>
    </select>
    <?php
}

function psych_save_meta(int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['psych_material_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['psych_material_nonce'])), 'psych_save_material_meta')) {
        update_post_meta($post_id, '_psych_accent', sanitize_text_field(wp_unslash($_POST['psych_accent'] ?? '')));
        $icon = sanitize_text_field(wp_unslash($_POST['psych_icon'] ?? 'bi-chat-dots'));
        update_post_meta($post_id, '_psych_icon', $icon ?: 'bi-chat-dots');
        update_post_meta($post_id, '_psych_example', sanitize_textarea_field(wp_unslash($_POST['psych_example'] ?? '')));
    }

    if (isset($_POST['psych_question_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['psych_question_nonce'])), 'psych_save_question_meta')) {
        $options = array_map('sanitize_text_field', wp_unslash($_POST['psych_options'] ?? []));
        $options = array_values(array_pad(array_slice($options, 0, 4), 4, ''));
        update_post_meta($post_id, '_psych_options', $options);
        update_post_meta($post_id, '_psych_correct', max(0, min(3, (int) ($_POST['psych_correct'] ?? 0))));
    }
}
add_action('save_post', 'psych_save_meta');

function psych_get_materials_data(): array {
    $query = new WP_Query([
        'post_type' => 'psych_material',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'ASC'],
        'post_status' => 'publish',
    ]);
    $items = [];
    foreach ($query->posts as $post) {
        $terms = get_the_terms($post, 'psych_material_category');
        $category = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->slug : 'section-1';
        $items[] = [
            'id' => $post->ID,
            'title' => get_the_title($post),
            'category' => $category,
            'icon' => get_post_meta($post->ID, '_psych_icon', true) ?: 'bi-chat-dots',
            'accent' => get_post_meta($post->ID, '_psych_accent', true) ?: 'Тема курса',
            'text' => wp_strip_all_tags($post->post_content),
            'example' => get_post_meta($post->ID, '_psych_example', true),
        ];
    }
    return $items;
}

function psych_get_questions_data(): array {
    $query = new WP_Query([
        'post_type' => 'psych_question',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'ASC'],
        'post_status' => ['publish', 'private'],
    ]);
    $items = [];
    foreach ($query->posts as $post) {
        $options = get_post_meta($post->ID, '_psych_options', true);
        if (!is_array($options) || count(array_filter($options)) < 2) {
            continue;
        }
        $items[] = [
            'id' => $post->ID,
            'question' => get_the_title($post),
            'options' => array_values($options),
            'correct' => (int) get_post_meta($post->ID, '_psych_correct', true),
        ];
    }
    return $items;
}

function psych_default_terms(): array {
    return json_decode(<<<'JSON'
{
  "section-1": "Раздел 1",
  "section-2": "Раздел 2",
  "section-3": "Раздел 3",
  "section-4": "Раздел 4",
  "practice": "Практика",
  "control": "Аттестация"
}
JSON, true);
}

function psych_course_overview(): array {
    return json_decode(<<<'JSON'
{
  "title": "ОГСЭ.03 Психология общения",
  "specialty": "09.02.07 Информационные системы и программирование",
  "total_hours": 50,
  "teacher_hours": 48,
  "theory_hours": 28,
  "practice_hours": 20,
  "self_hours": 2,
  "attestation": "Дифференцированный зачёт",
  "competencies": "ОК 01, ОК 03, ОК 04"
}
JSON, true);
}

function psych_course_plan(): array {
    return json_decode(<<<'JSON'
[
  {
    "type": "section",
    "section": "Раздел 1. Теоретические основы изучения общения в психологии",
    "hours": 12,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 1",
    "title": "Тема 1.1. Методологические и логические основы психологии общения",
    "lessons": "1, 2",
    "content": "Степень научной разработанности проблемы. Предмет и задачи психологии общения как отрасли психологической науки. Социология коммуникации и психология общения. Общение как ведущая деятельность специалиста. Речь как важнейшее средство общения. Виды речи. Психофизиологические основы речи.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 1",
    "title": "Практическое занятие №1. Составление древа понятия «общение»",
    "lessons": "3, 4",
    "content": "Составление схемы понятия «общение»: признаки, функции, средства, виды речи и основные связи между элементами общения.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 1",
    "title": "Тема 1.2. Психологическая структура и функции общения",
    "lessons": "5, 6, 7, 8",
    "content": "Этика общечеловеческая и профессиональная. Формирование профессиональной этики. Принципы этики деловых отношений. Психологическая структура общения. Реализация функций общения. Социально-психологическая характеристика деловых и личных взаимоотношений. Социальная перцепция, взаимопонимание, идентификация, эмпатия, эффекты ореола, первичности и новизны, стереотипы и способы их нейтрализации.",
    "hours": 4,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 1",
    "title": "Практическое занятие №2. Семинар «Общение как инструмент современного специалиста»",
    "lessons": "9, 10",
    "content": "Обсуждение роли общения в профессиональной деятельности. Анализ ситуаций взаимодействия специалиста с коллегами, руководством и клиентами.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 1",
    "title": "Практическое занятие №3. Нейтрализация стереотипов общения",
    "lessons": "11, 12",
    "content": "Разбор типичных стереотипов общения и способов их нейтрализации через уточнение, эмпатию, проверку фактов и уважительную обратную связь.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "section",
    "section": "Раздел 2. Психологические особенности делового общения",
    "hours": 16,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 2",
    "title": "Тема 2.1. Культура поведения и этика делового общения",
    "lessons": "13, 14, 15, 16",
    "content": "Культура поведения как форма общения людей. Поступки, основанные на нравственности, этическом вкусе и соблюдении норм и правил. Единство внутренней и внешней культуры человека. Умение найти нравственную линию поведения в нестандартной и экстремальной ситуации. Современные взгляды на место этики в деловом общении. Общеэтические принципы и характер делового общения.",
    "hours": 4,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 2",
    "title": "Тема 2.2. Речевой этикет или этика делового красноречия",
    "lessons": "17, 18, 19, 20",
    "content": "Речевой этикет как правило речевого поведения в обществе. Деловая риторика и её значимость для эффективности деловых отношений. Национальные, исторические и другие корни делового красноречия. Виды речевого воздействия и этические требования к выступлению, совещанию, деловой беседе. Стиль делового речевого воздействия, комплименты, эпидейктическая речь.",
    "hours": 4,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 2",
    "title": "Практическое занятие №4. Составление плана публичного выступления",
    "lessons": "21, 22",
    "content": "Подготовка структуры публичного выступления: цель, вступление, тезисы, аргументы, примеры, заключение, этикет выступающего.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 2",
    "title": "Тема 2.3. Психологические особенности делового телефонного разговора и письменного делового общения",
    "lessons": "23, 24",
    "content": "Практические рекомендации и нормы делового этикета телефонного разговора. Рациональная композиция делового разговора. Что можно, нужно и нельзя говорить по телефону. Методы достижения результативности телефонного делового разговора в рамках этикета.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 2",
    "title": "Практическое занятие №5. Деловая игра «Этикет телефонного разговора»",
    "lessons": "25, 26",
    "content": "Ролевая отработка телефонного разговора: приветствие, представление, формулировка цели, уточнение информации, завершение разговора.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 2",
    "title": "Практическое занятие №6. Составление текста делового письма",
    "lessons": "27, 28, 29, 30",
    "content": "Составление делового письма с соблюдением структуры, речевого этикета, грамотности, точности формулировок и корректного тона.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "section",
    "section": "Раздел 3. Коммуникации в процессе организации совместных действий",
    "hours": 14,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 3",
    "title": "Тема 3.1. Социально-психологическая характеристика конфликтов",
    "lessons": "31, 32, 33, 34",
    "content": "Типология конфликтов. Управление конфликтной ситуацией. Стратегии и алгоритм разрешения конфликтов. Психологическая коррекция конфликтного общения.",
    "hours": 4,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 3",
    "title": "Практическое занятие №7. Психотренинг «Конструктивный конфликт»",
    "lessons": "35, 36",
    "content": "Отработка конструктивных способов поведения в конфликте: активное слушание, я-высказывания, поиск интересов сторон, выбор решения.",
    "hours": 4,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "practice",
    "section": "Раздел 3",
    "title": "Практическое занятие №8. Психотренинг «Развитие уверенности в себе»",
    "lessons": "37, 38",
    "content": "Упражнения на уверенное поведение, спокойную самопрезентацию, аргументацию позиции и снижение тревожности в общении.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 3",
    "title": "Тема 3.2. Психологическая характеристика невербального общения",
    "lessons": "39, 40, 41, 42",
    "content": "Разделы психологии, изучающие невербальные средства общения: кинесика, экстралингвистика, паралингвистика, такесика, проксемика. Значение взгляда в общении. Мимика как средство общения. Пантомимика. Виды жестов и поз.",
    "hours": 4,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "section",
    "section": "Раздел 4. Верификация ложной информации в процессе общения",
    "hours": 4,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 4",
    "title": "Тема 4.1. Определение и психологическая структура лжи",
    "lessons": "43, 44",
    "content": "Определение и основные формы лжи: умолчание и искажение. Причины негативного искажения информации. Признаки обмана в общении.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "theme",
    "section": "Раздел 4",
    "title": "Тема 4.2. Верификация ложной информации",
    "lessons": "45, 46",
    "content": "Верификация ложной информации по словам, голосу, пластике и мимике. Подготовка к дифференцированному зачёту.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "control",
    "section": "Самостоятельная работа",
    "title": "Самостоятельная работа. Подготовка к дифференцированному зачёту",
    "lessons": "47, 48",
    "content": "Повторение основных тем дисциплины, подготовка к практической работе и итоговой проверке знаний.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  },
  {
    "type": "control",
    "section": "Промежуточная аттестация",
    "title": "Дифференцированный зачёт — практическая работа №9",
    "lessons": "49, 50",
    "content": "Итоговая практическая работа по содержанию дисциплины «Психология общения». Проверяется знание тем, умение применять приёмы эффективного общения и анализировать коммуникативные ситуации.",
    "hours": 2,
    "competencies": "ОК 01, ОК 03, ОК 04"
  }
]
JSON, true);
}

function psych_learning_results(): array {
    return json_decode(<<<'JSON'
[
  {
    "title": "Знания",
    "items": [
      "взаимосвязь общения и деятельности",
      "цели, функции, виды и уровни общения",
      "роли и ролевые ожидания в общении",
      "виды социальных взаимодействий",
      "механизмы взаимопонимания в общении",
      "техники и приёмы общения, правила слушания, ведения беседы и убеждения",
      "этические принципы общения",
      "источники, причины, виды и способы разрешения конфликтов",
      "приёмы саморегуляции в процессе общения"
    ]
  },
  {
    "title": "Умения",
    "items": [
      "применять техники и приёмы эффективного общения в профессиональной деятельности",
      "использовать приёмы саморегуляции поведения в процессе межличностного общения",
      "разрешать смоделированные конфликтные ситуации",
      "самостоятельно оценивать ситуацию и выбирать стратегию поведения",
      "работать с психологической информацией и применять её на практике"
    ]
  },
  {
    "title": "Контроль",
    "items": [
      "тестирование",
      "анализ ролевых ситуаций",
      "оценка выполнения практических работ",
      "оценка решений творческих задач",
      "дифференцированный зачёт в форме практической работы №9"
    ]
  }
]
JSON, true);
}

function psych_reference_sources(): array {
    return json_decode(<<<'JSON'
[
  "Аминов И. И. Психология общения: учебник. Москва: КноРус, 2024.",
  "Бороздина Г. В. Психология и этика деловых отношений: учебное пособие. М., 2021.",
  "Дорошенко В. Ю. Психология и этика делового общения: учебник для вузов. М., 2021.",
  "Леонов Н. И. Психология общения: учебное пособие для СПО. Москва: Юрайт, 2025.",
  "Лавриненко В. Н., Чернышова Л. И. Психология общения: учебник и практикум для СПО. Москва: Юрайт, 2025.",
  "Рогов Е. И. Психология общения + еПриложение: Тесты. Москва: КноРус, 2024."
]
JSON, true);
}

function psych_seed_materials(): array {
    return json_decode(<<<'JSON'
[
  {
    "slug": "methodological-basics",
    "title": "Тема 1.1. Методологические и логические основы психологии общения",
    "category": "section-1",
    "icon": "bi-chat-dots",
    "accent": "Уроки 1–2 / 2 часа",
    "text": "Психология общения рассматривает закономерности взаимодействия людей, способы передачи информации, роль речи и особенности восприятия собеседника. В теме раскрываются предмет и задачи психологии общения, связь социологии коммуникации и психологии общения, виды речи и психофизиологические основы речевой деятельности.",
    "example": "Студент объясняет одногруппнику новую тему: он подбирает слова, следит за реакцией, использует речь как средство передачи информации и поддержания контакта."
  },
  {
    "slug": "communication-tree",
    "title": "Практическое занятие №1. Составление древа понятия «общение»",
    "category": "practice",
    "icon": "bi-diagram-3",
    "accent": "Уроки 3–4 / 2 часа",
    "text": "Практическая работа направлена на закрепление понятия «общение». Обучающийся составляет схему, где показывает основные элементы общения: субъектов, цель, средства, виды речи, функции, барьеры и результат взаимодействия.",
    "example": "В центре схемы размещается слово «общение», от него идут ветви: речь, мимика, жесты, функции, виды, барьеры, обратная связь."
  },
  {
    "slug": "structure-functions",
    "title": "Тема 1.2. Психологическая структура и функции общения",
    "category": "section-1",
    "icon": "bi-people",
    "accent": "Уроки 5–8 / 4 часа",
    "text": "Тема раскрывает психологическую структуру общения, функции общения, профессиональную этику, социальную перцепцию и взаимопонимание. Рассматриваются деловые и личные взаимоотношения, идентификация, эмпатия, эффекты ореола, первичности и новизны, а также стереотипы и способы их нейтрализации.",
    "example": "Если человек заранее воспринимает собеседника через стереотип, он может неправильно понять его слова. Для нейтрализации стереотипа важно задавать уточняющие вопросы и опираться на факты."
  },
  {
    "slug": "seminar-modern-specialist",
    "title": "Практическое занятие №2. Общение как инструмент современного специалиста",
    "category": "practice",
    "icon": "bi-person-workspace",
    "accent": "Уроки 9–10 / 2 часа",
    "text": "Семинарское занятие показывает значение общения в профессиональной деятельности. Обучающиеся обсуждают, как специалист взаимодействует с коллегами, руководством и клиентами, какие ошибки возникают в общении и как их предупредить.",
    "example": "На семинаре разбирается ситуация: сотрудник получил неполное задание. Правильное действие — уточнить требования, сроки и ожидаемый результат."
  },
  {
    "slug": "stereotypes-neutralization",
    "title": "Практическое занятие №3. Нейтрализация стереотипов общения",
    "category": "practice",
    "icon": "bi-shield-check",
    "accent": "Уроки 11–12 / 2 часа",
    "text": "Практическое занятие посвящено распознаванию стереотипов общения и способам их снижения. Используются приёмы активного слушания, уточнения, проверки фактов, уважительной обратной связи и отказа от поспешных выводов.",
    "example": "Вместо фразы «ты всегда так делаешь» используется конкретизация: «в этой ситуации срок был нарушен, давай разберём причину»."
  },
  {
    "slug": "business-ethics",
    "title": "Тема 2.1. Культура поведения и этика делового общения",
    "category": "section-2",
    "icon": "bi-award",
    "accent": "Уроки 13–16 / 4 часа",
    "text": "Культура поведения рассматривается как форма общения людей, основанная на нравственности, этическом вкусе и соблюдении норм. Изучается единство внутренней и внешней культуры человека, этика в деловом общении, общеэтические принципы и поведение в нестандартной ситуации.",
    "example": "В деловой ситуации важно не только правильно говорить, но и сохранять уважительный тон, не перебивать собеседника и соблюдать договорённости."
  },
  {
    "slug": "speech-etiquette",
    "title": "Тема 2.2. Речевой этикет и этика делового красноречия",
    "category": "section-2",
    "icon": "bi-megaphone",
    "accent": "Уроки 17–20 / 4 часа",
    "text": "Речевой этикет — это правила речевого поведения в обществе. Тема раскрывает деловую риторику, значение делового красноречия, виды речевого воздействия, требования к выступлению, совещанию и деловой беседе, а также стиль делового речевого воздействия.",
    "example": "Публичное выступление должно иметь вступление, основную часть и вывод. Речь должна быть ясной, уважительной и соответствовать ситуации."
  },
  {
    "slug": "public-speech-plan",
    "title": "Практическое занятие №4. Составление плана публичного выступления",
    "category": "practice",
    "icon": "bi-card-checklist",
    "accent": "Уроки 21–22 / 2 часа",
    "text": "Обучающиеся составляют план публичного выступления: определяют тему, цель, аудиторию, тезисы, аргументы, примеры и итоговое обращение к слушателям. Отрабатывается логика и культура деловой речи.",
    "example": "План выступления: приветствие, актуальность темы, три основных тезиса, пример из практики, вывод и благодарность слушателям."
  },
  {
    "slug": "phone-and-written-business",
    "title": "Тема 2.3. Деловой телефонный разговор и письменное деловое общение",
    "category": "section-2",
    "icon": "bi-telephone",
    "accent": "Уроки 23–24 / 2 часа",
    "text": "Тема посвящена психологическим особенностям телефонного разговора и письменного делового общения. Рассматриваются нормы телефонного этикета, рациональная композиция делового разговора, допустимые и недопустимые фразы, а также способы достижения результата в рамках делового этикета.",
    "example": "Телефонный разговор начинается с приветствия и представления: «Здравствуйте, меня зовут Алексей Дерябин, я звоню по вопросу…»."
  },
  {
    "slug": "phone-etiquette-game",
    "title": "Практическое занятие №5. Деловая игра «Этикет телефонного разговора»",
    "category": "practice",
    "icon": "bi-headset",
    "accent": "Уроки 25–26 / 2 часа",
    "text": "На занятии обучающиеся в парах разыгрывают деловые телефонные разговоры, отрабатывают приветствие, уточнение цели, активное слушание, фиксацию результата и корректное завершение разговора.",
    "example": "Один студент играет клиента, другой — специалиста. Цель — договориться о встрече и не нарушить нормы телефонного этикета."
  },
  {
    "slug": "business-letter",
    "title": "Практическое занятие №6. Составление текста делового письма",
    "category": "practice",
    "icon": "bi-envelope-paper",
    "accent": "Уроки 27–30 / 2 часа",
    "text": "Практическая работа формирует навык письменного делового общения. Обучающиеся составляют письмо с правильной структурой: обращение, причина обращения, основная информация, просьба или предложение, завершающая фраза и подпись.",
    "example": "Деловое письмо должно быть конкретным: «Просим направить информацию до 15 мая» звучит лучше, чем расплывчатое «пришлите когда-нибудь»."
  },
  {
    "slug": "conflicts",
    "title": "Тема 3.1. Социально-психологическая характеристика конфликтов",
    "category": "section-3",
    "icon": "bi-signpost-split",
    "accent": "Уроки 31–34 / 4 часа",
    "text": "Конфликт рассматривается как столкновение интересов, мнений или потребностей. Изучаются типология конфликтов, управление конфликтной ситуацией, стратегии и алгоритм разрешения конфликтов, психологическая коррекция конфликтного общения.",
    "example": "При конфликте эффективнее не искать виноватого, а выяснить интересы сторон: что каждому нужно и какое решение будет приемлемым."
  },
  {
    "slug": "constructive-conflict-training",
    "title": "Практическое занятие №7. Психотренинг «Конструктивный конфликт»",
    "category": "practice",
    "icon": "bi-chat-square-heart",
    "accent": "Уроки 35–36 / 4 часа",
    "text": "Тренинг направлен на отработку конструктивного поведения в конфликте. Используются я-высказывания, активное слушание, уточнение позиции, поиск общих интересов и спокойная аргументация.",
    "example": "Вместо «ты меня не слушаешь» лучше сказать: «мне важно закончить мысль, потому что я хочу объяснить свою позицию»."
  },
  {
    "slug": "confidence-training",
    "title": "Практическое занятие №8. Психотренинг «Развитие уверенности в себе»",
    "category": "practice",
    "icon": "bi-person-check",
    "accent": "Уроки 37–38 / 2 часа",
    "text": "Занятие помогает развивать уверенное поведение, самопрезентацию, умение спокойно выражать позицию, защищать личные границы и снижать тревожность в общении.",
    "example": "Уверенный ответ строится спокойно: «я понимаю вашу просьбу, но сейчас не могу выполнить её в этот срок»."
  },
  {
    "slug": "nonverbal-communication",
    "title": "Тема 3.2. Психологическая характеристика невербального общения",
    "category": "section-3",
    "icon": "bi-person-arms-up",
    "accent": "Уроки 39–42 / 4 часа",
    "text": "Невербальное общение включает мимику, жесты, позу, взгляд, дистанцию, интонацию и темп речи. Изучаются кинесика, экстралингвистика, паралингвистика, такесика, проксемика, пантомимика, виды жестов и поз.",
    "example": "Человек может говорить «я согласен», но скрещённые руки, напряжённый взгляд и резкая интонация показывают внутреннее несогласие."
  },
  {
    "slug": "lie-structure",
    "title": "Тема 4.1. Определение и психологическая структура лжи",
    "category": "section-4",
    "icon": "bi-exclamation-triangle",
    "accent": "Уроки 43–44 / 2 часа",
    "text": "Тема раскрывает понятие лжи и основные формы: умолчание и искажение. Рассматриваются причины негативного искажения информации и признаки обмана в процессе общения.",
    "example": "Умолчание — человек скрывает часть важной информации. Искажение — человек сообщает сведения в изменённом виде."
  },
  {
    "slug": "lie-verification",
    "title": "Тема 4.2. Верификация ложной информации",
    "category": "section-4",
    "icon": "bi-search",
    "accent": "Уроки 45–46 / 2 часа",
    "text": "Верификация ложной информации проводится по словам, голосу, пластике и мимике. Важно анализировать признаки комплексно, не делая вывод по одному жесту или одной фразе.",
    "example": "Если слова, мимика и интонация противоречат друг другу, это повод задать уточняющий вопрос, а не сразу обвинять человека во лжи."
  },
  {
    "slug": "self-work-credit",
    "title": "Самостоятельная работа и дифференцированный зачёт",
    "category": "control",
    "icon": "bi-mortarboard",
    "accent": "Уроки 47–50 / 4 часа",
    "text": "Самостоятельная работа включает подготовку к дифференцированному зачёту. Промежуточная аттестация проводится в форме дифференцированного зачёта — практической работы №9, где проверяются знания и умение применять приёмы общения.",
    "example": "Перед зачётом студент повторяет функции общения, этикет, деловую речь, конфликты, невербальные признаки и верификацию ложной информации."
  }
]
JSON, true);
}

function psych_seed_questions(): array {
    return json_decode(<<<'JSON'
[
  {
    "slug": "q-subject",
    "question": "Что является предметом психологии общения?",
    "options": [
      "Только устройство компьютера",
      "Закономерности общения, взаимодействия и восприятия людей",
      "Только правила орфографии",
      "Только финансовые расчёты"
    ],
    "correct": 1
  },
  {
    "slug": "q-speech",
    "question": "Почему речь считается важнейшим средством общения?",
    "options": [
      "Она позволяет передавать мысли, чувства и информацию",
      "Она нужна только для чтения книг",
      "Она заменяет все невербальные сигналы",
      "Она не влияет на общение"
    ],
    "correct": 0
  },
  {
    "slug": "q-ethics",
    "question": "Что относится к принципам деловой этики?",
    "options": [
      "Уважение, корректность и ответственность",
      "Грубость и давление",
      "Игнорирование собеседника",
      "Случайность поведения"
    ],
    "correct": 0
  },
  {
    "slug": "q-empathy",
    "question": "Что такое эмпатия?",
    "options": [
      "Способность понять переживания другого человека",
      "Полное согласие со всеми действиями",
      "Отказ от собственного мнения",
      "Стремление победить в споре"
    ],
    "correct": 0
  },
  {
    "slug": "q-stereotype",
    "question": "Как лучше нейтрализовать стереотип в общении?",
    "options": [
      "Опираясь на факты и уточняющие вопросы",
      "Повторяя ярлыки",
      "Перебивая собеседника",
      "Делая вывод по первому впечатлению"
    ],
    "correct": 0
  },
  {
    "slug": "q-public-speech",
    "question": "Что должно быть в плане публичного выступления?",
    "options": [
      "Цель, тезисы, аргументы, примеры и вывод",
      "Только заголовок",
      "Только список фамилий",
      "Только случайные фразы"
    ],
    "correct": 0
  },
  {
    "slug": "q-phone",
    "question": "С чего начинается корректный деловой телефонный разговор?",
    "options": [
      "С приветствия и представления",
      "С требования без объяснений",
      "С молчания",
      "С резкой критики"
    ],
    "correct": 0
  },
  {
    "slug": "q-letter",
    "question": "Что важно для делового письма?",
    "options": [
      "Конкретность, вежливость и логичная структура",
      "Сленг и угрозы",
      "Отсутствие темы",
      "Случайный порядок мыслей"
    ],
    "correct": 0
  },
  {
    "slug": "q-conflict",
    "question": "Что такое конфликт?",
    "options": [
      "Столкновение интересов, мнений или потребностей",
      "Любая спокойная беседа",
      "Только письменная ошибка",
      "Только молчание"
    ],
    "correct": 0
  },
  {
    "slug": "q-constructive-conflict",
    "question": "Какой способ поведения в конфликте является конструктивным?",
    "options": [
      "Выяснить интересы сторон и искать решение",
      "Повысить голос",
      "Оскорбить собеседника",
      "Отказаться слушать"
    ],
    "correct": 0
  },
  {
    "slug": "q-i-message",
    "question": "Что такое я-высказывание?",
    "options": [
      "Фраза о своём чувстве и потребности без обвинения",
      "Команда выполнить действие",
      "Обвинение собеседника",
      "Запрет на эмоции"
    ],
    "correct": 0
  },
  {
    "slug": "q-nonverbal",
    "question": "Что относится к невербальному общению?",
    "options": [
      "Мимика, жесты, поза, взгляд и интонация",
      "Только письменный текст",
      "Только формулы",
      "Только список литературы"
    ],
    "correct": 0
  },
  {
    "slug": "q-kinesics",
    "question": "Что изучает кинесика?",
    "options": [
      "Жесты, движения тела и мимику",
      "Только правила печати",
      "Только бухгалтерию",
      "Только настройку WordPress"
    ],
    "correct": 0
  },
  {
    "slug": "q-proxemics",
    "question": "Что связано с проксемикой?",
    "options": [
      "Дистанция между людьми в общении",
      "Скорость интернета",
      "Название документа",
      "Тип базы данных"
    ],
    "correct": 0
  },
  {
    "slug": "q-lie",
    "question": "Какие основные формы лжи указаны в теме 4.1?",
    "options": [
      "Умолчание и искажение",
      "Только комплимент",
      "Только вопрос",
      "Только аргумент"
    ],
    "correct": 0
  },
  {
    "slug": "q-verification",
    "question": "По каким признакам можно проводить верификацию ложной информации?",
    "options": [
      "По словам, голосу, пластике и мимике",
      "Только по цвету одежды",
      "Только по номеру кабинета",
      "Только по длине текста"
    ],
    "correct": 0
  },
  {
    "slug": "q-hours",
    "question": "Какой общий объём образовательной программы по дисциплине указан в рабочей программе?",
    "options": [
      "50 часов",
      "12 часов",
      "100 часов",
      "4 часа"
    ],
    "correct": 0
  },
  {
    "slug": "q-competencies",
    "question": "Какие общие компетенции формируются по дисциплине?",
    "options": [
      "ОК 01, ОК 03, ОК 04",
      "Только ОК 10",
      "Только ПК 1.1",
      "Только ОК 11"
    ],
    "correct": 0
  },
  {
    "slug": "q-attestation",
    "question": "В какой форме проводится промежуточная аттестация?",
    "options": [
      "Дифференцированный зачёт",
      "Курсовая работа",
      "Экзамен по билетам без практики",
      "Защита диплома"
    ],
    "correct": 0
  },
  {
    "slug": "q-practice",
    "question": "Сколько часов практических занятий предусмотрено программой?",
    "options": [
      "20 часов",
      "2 часа",
      "28 часов",
      "50 часов"
    ],
    "correct": 0
  }
]
JSON, true);
}


function psych_seed_theme_content(): void {
    foreach (psych_default_terms() as $slug => $name) {
        if (!term_exists($slug, 'psych_material_category')) {
            wp_insert_term($name, 'psych_material_category', ['slug' => $slug]);
        }
    }

    foreach (psych_seed_materials() as $index => $item) {
        $existing = get_page_by_path($item['slug'], OBJECT, 'psych_material');
        $post_data = [
            'post_type' => 'psych_material',
            'post_status' => 'publish',
            'post_title' => $item['title'],
            'post_content' => $item['text'],
            'post_name' => $item['slug'],
            'menu_order' => $index,
        ];
        if ($existing instanceof WP_Post) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }
        if ($post_id && !is_wp_error($post_id)) {
            wp_set_object_terms($post_id, $item['category'], 'psych_material_category');
            update_post_meta($post_id, '_psych_icon', $item['icon']);
            update_post_meta($post_id, '_psych_accent', $item['accent']);
            update_post_meta($post_id, '_psych_example', $item['example']);
        }
    }

    foreach (psych_seed_questions() as $index => $item) {
        $existing = get_page_by_path($item['slug'], OBJECT, 'psych_question');
        $post_data = [
            'post_type' => 'psych_question',
            'post_status' => 'publish',
            'post_title' => $item['question'],
            'post_name' => $item['slug'],
            'menu_order' => $index,
        ];
        if ($existing instanceof WP_Post) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_psych_options', $item['options']);
            update_post_meta($post_id, '_psych_correct', $item['correct']);
        }
    }
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'psych_seed_theme_content');
