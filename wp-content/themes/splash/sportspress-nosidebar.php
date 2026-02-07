<?php
/**
 * Template Name: SportsPress No Sidebar
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19.05.2017
 * Time: 17:07
 */

get_header();

    $post_types_content = splash_sportspress_side_posts();
    $eventResultAsHeader = get_post_meta(get_the_ID(), 'event_as_header', false);
    if($eventResultAsHeader != null && $eventResultAsHeader[0] == "on") :
        $eventBg = get_the_post_thumbnail_url(get_the_ID(), 'full');
        ?>
        <div class="stm-event-results-as-header">
            <div class="stm-event-header-bg" style="background: url('<?php echo esc_url($eventBg) ?>');"></div>
            <?php echo get_template_part("sportspress/event-results-as-header"); ?>
        </div>
    <?php endif; ?>
    <div class="container stm-sportspress stm-sportspress-no-sidebar">
        <div class="row">
            <div class="col-md-12">
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
<?php
get_footer();
?>