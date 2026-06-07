<?php
get_header();
?>
<main class="section-pad bg-soft">
    <div class="container">
        <h1 class="section-heading"><?php single_post_title(); ?></h1>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article <?php post_class('content-card p-4'); ?>>
                <h2><?php the_title(); ?></h2>
                <?php the_content(); ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>
<?php
get_footer();
