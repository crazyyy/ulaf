<?php
/*
Template Name: SportsPress Sidebar Right
*/
?>

<?php get_header(); ?>

<?php

if (get_post_type() != "sp_tournament" && get_post_type() != 'page') :
    $post_types_content = splash_sportspress_side_posts();
    $eventResultAsHeader = get_post_meta(get_the_ID(), 'event_as_header', false);

    if ($eventResultAsHeader != null && $eventResultAsHeader[0] == "on") :
        $eventBg = get_the_post_thumbnail_url(get_the_ID(), 'full');
        ?>
        <div class="stm-event-results-as-header">
            <div class="stm-event-header-bg" style="background: url('<?php echo esc_url($eventBg) ?>');"></div>
            <?php echo get_template_part("sportspress/event-results-as-header"); ?>
        </div>
    <?php endif; ?>
    <?php
    $col_1 = "col-md-9 has_sidebar";
    $col_2 = "col-md-3";
    if (splash_is_layout('hockey') || splash_is_layout('esport')) {
        $col_1 = "col-md-8 has_sidebar";
        $col_2 = "col-md-4";
    }
    $has_sidebar = true;
    if(!empty($_GET['sidebar-full'])){
        $has_sidebar = false;
        $col_1 = "col-md-12";
    }
    ?>
    <div class="container stm-sportspress stm-sportspress-sidebar-right">
        <div class="row">
            <div class="<?php echo esc_html($col_1); ?>">
                <?php foreach ($post_types_content as $post_type => $post_type_content): ?>
                    <?php if (get_post_type() == $post_type): ?>
                        <!--CALENDAR-->
                        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div class="<?php echo sanitize_text_field($post_type_content['class']); ?>">
                                <div class="container">
                                    <?php if (have_posts()) :
                                        while (have_posts()) : the_post();
                                            get_template_part('partials/global/sportspress/' . $post_type_content['template']);
                                        endwhile;
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if($has_sidebar): ?>
                <div class="<?php echo esc_html($col_2); ?>">
                    <?php get_sidebar('sportspress'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif (get_post_type() == "page"): ?>
    <div class="container stm-sportspress-sidebar-right stm-page-sportspress-sidebar-right">
        <div class="row">
            <div class="col-md-9">
                <div <?php post_class(); ?>>
                    <div>
                        <div class="container">
                            <?php if (have_posts()) :
                                while (have_posts()) : the_post();
                                    echo "<h1>" . get_the_title() . "</h1>";
                                    the_content();
                                endwhile;
                            endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <?php get_sidebar('sportspress'); ?>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="container stm-sportspress-sidebar-right">
        <div class="row">
            <div class="col-md-9">
                <?php
                /**
                 * sportspress_before_single_tournament hook
                 */
                do_action('sportspress_before_single_tournament');

                if (post_password_required()) {
                    echo get_the_password_form();
                    return;
                }

                do_action('sportspress_single_tournament_content');

                do_action('sportspress_after_single_tournament');
                ?>
            </div>
            <div class="col-md-3">
                <?php get_sidebar('sportspress'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php get_footer(); ?>