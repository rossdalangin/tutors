<?php
/**
 * Template Name: Edupreneur Canvas (No Header/Footer)
 * Description: A clean, distraction-free template for immersive learning.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body { background: var(--edu-bg, #f0f0f1); margin: 0; padding: 0; }
        .edu-canvas-content { max-width: 1200px; margin: 40px auto; padding: 20px; }
    </style>
</head>
<body <?php body_class(); ?>>
    <div class="edu-canvas-content">
        <?php
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
        ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
