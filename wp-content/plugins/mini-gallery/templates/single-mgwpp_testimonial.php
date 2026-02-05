<?php
if (! defined('ABSPATH')) {
    exit;
}
get_header();
?>

<main class="mgwpp-testimonial-single">
    <?php while (have_posts()) :
        the_post(); ?>
        <article <?php post_class('testimonial-content'); ?>>

            <!-- Featured Image -->
            <?php if (has_post_thumbnail()) : ?>
                <div class="testimonial-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <!-- Testimonial Content -->
            <div class="testimonial-body">
                <div class="testimonial-meta">
                    <?php
                    $mgwpp_author = get_post_meta(get_the_ID(), '_mgwpp_author', true);
                    $mgwpp_position = get_post_meta(get_the_ID(), '_mgwpp_position', true);
                    ?>

                    <h1 class="testimonial-title"><?php the_title(); ?></h1>

                    <?php if ($mgwpp_author) : ?>
                        <div class="testimonial-author">
                            <?php echo esc_html($mgwpp_author); ?>
                            <?php if ($mgwpp_position) : ?>
                                <span class="author-position"><?php echo esc_html($mgwpp_position); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="testimonial-text">
                    <?php the_content(); ?>
                </div>
            </div>

        </article>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>