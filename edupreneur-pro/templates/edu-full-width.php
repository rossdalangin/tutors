<?php
/**
 * Template Name: Edupreneur Full Width
 * Description: Full-width layout including theme header and footer.
 */

get_header(); ?>

<div class="edu-full-width-container" style="max-width: 100%; padding: 0;">
    <?php
    while ( have_posts() ) : the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php get_footer(); ?>
