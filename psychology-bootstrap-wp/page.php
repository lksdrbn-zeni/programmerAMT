<?php
get_header();
?>
<main class="section-pad bg-soft">
    <div class="container narrow-content">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article <?php post_class('content-card p-4 p-lg-5'); ?>>
                <h1 class="section-heading"><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>
<?php
get_footer();
