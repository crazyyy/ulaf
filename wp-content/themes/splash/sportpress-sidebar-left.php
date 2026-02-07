<?php
/*
Template Name: SportsPress Sidebar Left
*/
get_header();
    $content = 'col-md-9';
    $has_sidebar = true;
    if(!empty($_GET['sidebar-full'])) {
        $has_sidebar = false;
        $content = 'col-md-12';
    }
if(get_post_type() != "sp_tournament" && get_post_type() != "page") :
    $post_types_content = splash_sportspress_side_posts();
    $eventResultAsHeader = get_post_meta(get_the_ID(), 'event_as_header', false);
    ?>
    <?php if($eventResultAsHeader != null && $eventResultAsHeader[0] == "on") :
        $eventBg = get_the_post_thumbnail_url(get_the_ID(), 'full');
    ?>
        <div class="stm-event-results-as-header">
            <div class="stm-event-header-bg" style="background: url('<?php echo esc_url($eventBg) ?>');"></div>
            <?php echo get_template_part("sportspress/event-results-as-header"); ?>
        </div>
    <?php endif; ?>
    <div class="container stm-sportspress stm-sportspress-sidebar-left">
        <div class="row">
            <?php if($has_sidebar): ?>
                <div class="col-md-3">
                    <?php get_sidebar('sportspress'); ?>
                </div>
            <?php endif; ?>
            <div class="<?php echo esc_attr($content); ?>">
                <?php foreach($post_types_content as $post_type => $post_type_content): ?>
                    <?php if ( get_post_type() == $post_type ): ?>
                        <!--CALENDAR-->
                        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div class="<?php echo sanitize_text_field($post_type_content['class']); ?>">
                                <div class="container">
                                    <?php if ( have_posts() ) :
                                        while ( have_posts() ) : the_post();
                                            get_template_part('partials/global/sportspress/' . $post_type_content['template'] );
                                        endwhile;
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php elseif(get_post_type() == "page"):?>
    <div class="container stm-sportspress-sidebar-left stm-page-sportspress-sidebar-left">
        <div class="row">
            <div class="col-md-3">
                <?php get_sidebar('sportspress'); ?>
            </div>
            <div class="col-md-9">
                <div <?php post_class(); ?>>
                    <div>
                        <div class="container">
                            <?php if ( have_posts() ) :
                                while ( have_posts() ) : the_post();
                                    echo "<h1>" . get_the_title() . "</h1>";
                                    the_content();
                                endwhile;
                            endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="container stm-sportspress-sidebar-right">
        <div class="row">
            <div class="col-md-3">
                <?php get_sidebar('sportspress'); ?>
            </div>
            <div class="<?php echo esc_attr($content); ?>">
                <?php
                /**
                 * sportspress_before_single_tournament hook
                 */
                do_action( 'sportspress_before_single_tournament' );

                if ( post_password_required() ) {
                    echo get_the_password_form();
                    return;
                }

                do_action( 'sportspress_single_tournament_content' );

                do_action( 'sportspress_after_single_tournament' );
                ?>
            </div>
        </div>
    </div>
    <?php
endif; get_footer(); ?>