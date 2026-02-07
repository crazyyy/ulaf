<?php get_header();
//Get sidebar settings
$sidebar_settings = splash_get_sidebar_settings('donation_sidebar', 'donation_sidebar_position', 'primary_sidebar', get_theme_mod("sidebar_position", "left"));
$sidebar_id = $sidebar_settings['id'];

$stm_sidebar_layout_mode = splash_sidebar_layout_mode($sidebar_settings['position'], $sidebar_id);
?>

    <div class="stm-default-page stm-default-page-<?php echo esc_attr($sidebar_settings['view_type']); ?>">
        <div class="container">
            <div class="row">
                <?php echo wp_kses_post($stm_sidebar_layout_mode['content_before']); ?>
                <div class="stm-small-title-box">
                    <?php if (!splash_is_layout('soccer_two') && !splash_is_layout('hockey')) get_template_part('partials/global/title-box'); ?>
                </div>
                <?php if (have_posts()): ?>
                    <div class="row row-3 row-sm-2 <?php if(!$sidebar_id) echo 'no-sidebar'; ?>">
                        <?php while (have_posts()): the_post(); ?>
                            <?php
                            if (splash_is_layout("af")) get_template_part('partials/loop/donation-content-af');
                            else if (splash_is_layout("baseball")) get_template_part('partials/loop/donation-content-af');
                            else if (splash_is_layout("esport")) get_template_part('partials/loop/donation-content-esport');
                            else get_template_part('partials/loop/donation-content');
                            ?>
                        <?php endwhile; ?>
                    </div>
                    <?php splash_pagination(); ?>
                <?php else: ?>

                    <h5 class="text-transform nothing found "><?php esc_html_e('No Results', 'splash'); ?></h5>
                <?php endif; ?>
                <?php echo wp_kses_post($stm_sidebar_layout_mode['content_after']); ?>
                <!--Sidebar-->
                <?php splash_display_sidebar(
                    $sidebar_id,
                    $stm_sidebar_layout_mode['sidebar_before'],
                    $stm_sidebar_layout_mode['sidebar_after'],
                    $sidebar_settings['blog_sidebar']
                ); ?>
            </div>
        </div>
    </div>


<?php get_footer(); ?>