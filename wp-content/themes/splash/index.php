<?php get_header();

//Get sidebar settings
$sidebar_settings = splash_get_sidebar_settings();
$sidebar_id = $sidebar_settings['id'];

$stm_sidebar_layout_mode = splash_sidebar_layout_mode($sidebar_settings['position'], $sidebar_id);

if (empty($stm_sidebar_layout_mode['sidebar_before'])) {
    $sidebar_settings['position'] = 'none';
}

$class = '';

if (splash_is_layout("baseball")) $class = "news_style_baseball_" . $sidebar_settings["view_type"];
if (splash_is_layout("magazine_one")) $class = "news_sidebar_" . $sidebar_settings["position"];

?>

    <div class="stm-default-page stm-default-page-<?php echo esc_attr($sidebar_settings['view_type']); ?> stm-default-page-<?php echo esc_attr($sidebar_settings['position']); ?>">
        <div class="container">
            <?php if(splash_is_layout('esport')): ?>
                <div class="row">
                    <?php get_template_part('partials/global/title-box'); ?>
                </div>
            <?php endif; ?>
            <div class="row sidebar-wrapper">
                <?php echo wp_kses_post($stm_sidebar_layout_mode['content_before']); ?>
                <?php if (!splash_is_layout('magazine_one') && !splash_is_layout('esport')): ?>
                    <div class="stm-small-title-box">
                        <?php if (!splash_is_layout('soccer_two') && !splash_is_layout('hockey') ) get_template_part('partials/global/title-box'); ?>
                    </div>
                <?php endif; ?>
                <?php if (have_posts()): ?>
                    <div class="row row-3 row-sm-2 <?php echo esc_attr($class); ?> <?php if(splash_is_layout('hockey')) echo 'hockey'; ?>">

                        <?php
                        $template = 'partials/loop/content-' . $sidebar_settings["view_type"];
                        $layoutName = splash_get_layout_name();
                        switch ($layoutName) {
                            case 'baseball':
                                $template = 'partials/loop/content-' . $sidebar_settings["view_type"] . '-baseball';
                                break;
                            case 'magazine_one':
                                $template = 'partials/loop/content-' . $sidebar_settings["view_type"] . '-magazine-one';
                                break;
                        }

                        while (have_posts()): the_post();
                            get_template_part($template);
                        endwhile; ?>

                    </div>
                    <?php splash_pagination(); ?>
                <?php else: ?>
                    <h5 style="font-weight: 600;" class="page-description"><?php echo esc_html('Search results for '.'"'.get_search_query().'"'); ?></h5>
                    <div class="text-transform nothing found"><?php esc_html_e('No Results', 'splash'); ?></div>
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