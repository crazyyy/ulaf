<?php
$team = $player_list = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

$id = 'stm-players-' . rand(0, 9999);

?>
    <div class="stm-player-ids <?php echo esc_attr($view_style) =='style_2' ? 'style_2 ':''; echo esc_attr($id); echo ' carousel_' . esc_attr($enable_carousel); ?>">
        <div class="clearfix">
            <?php if (!empty($title)): ?>
            <div class="<?php echo esc_attr($view_style) =='style_2' ? 'stm-title-center' : 'stm-title-left'; ?>">
                <<?php echo esc_html(getHTag()); ?>
                class="stm-main-title-unit"><?php echo esc_html($title); ?></<?php echo esc_html(getHTag()); ?>>
        </div>
        <?php endif; ?>
        <?php if($enable_carousel === 'yes'): ?>
        <div class="<?php echo esc_attr($view_style) == 'style_2' ? 'stm-carousel-controls-center' : 'stm-carousel-controls-right'; ?>">
            <div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
            <div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="stm-player-list-wrapper">
        <div class="stm-players clearfix">
            <?php
            $pList = new SP_Player_List($player_list);
            //$data = get_post_meta($player_list, 'sp_player', false);
            if ($pList) {
                foreach ($pList->data() as $player_id => $val):
                    if (!empty($player_id)):
                        $player_number = get_post_meta($player_id, 'sp_number', true);
                        $positions = wp_get_post_terms($player_id, 'sp_position');
                        $position = false;
                        if ($positions) {
                            $position = $positions[0]->name;
                        }
                        if (!empty($player_image_size)) {
                            $image = wpb_getImageBySize(array(
                                'post_id' => $player_id,
                                'thumb_size' => $player_image_size
                            ));
                            if (!empty($image) and !empty($image['thumbnail'])) {
                                $image = $image['thumbnail'];
                            } else {
                                $image = '';
                            }
                        } else {
                            $image = '<img src="' . splash_get_thumbnail_url($player_id, 0, 'stm-270-370') . '" alt="' . get_the_title($player_id) . '" />';
                        }
                        if (!has_post_thumbnail($player_id)) {
                            $image = '';
                        }
                        if (!empty($image)): ?>
                            <?php if (!splash_is_layout("sccr")) : ?>
                                <div class="stm-list-single-player">
                                    <a href="<?php echo esc_url(get_the_permalink($player_id)); ?>"
                                       title="<?php echo esc_attr(get_the_title($player_id)); ?>">
                                        <?php echo splash_sanitize_text_field($image); ?>
                                        <div class="stm-list-single-player-info">
                                            <div class="inner heading-font">
                                                <div class="player-number"><?php echo esc_attr($player_number); ?></div>
                                                <div
                                                        class="player-title"><?php echo esc_attr(get_the_title($player_id)); ?></div>
                                                <div class="player-position"><?php echo esc_attr($position); ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="stm-player-wrapp">
                                    <div class="player_image">
                                        <a href="<?php echo esc_url(get_the_permalink($player_id)); ?>">
                                            <?php echo splash_sanitize_text_field($image); ?>
                                        </a>
                                    </div>
                                    <div class="stm-player-title-wrap">
                                        <h4>
                                            <a href="<?php echo esc_url(get_the_permalink($player_id)); ?>"><?php echo esc_attr(get_the_title($player_id)); ?></a>
                                        </h4>
                                        <div class="player_like">
                                            <?php $like = (get_post_meta($player_id, 'stm_like', true) == '') ? 0 : get_post_meta($player_id, 'stm_like', true); ?>
                                            <a href="#" class="like_button" data-id="<?php echo esc_attr($player_id); ?>"
                                               onclick="stm_like(jQuery(this)); return false;">
                                                <i class="fa fa-heart-o"></i>
                                                <span><?php echo esc_html($like); ?></span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="player_info clearfix <?php if ($player_number == "") echo "position-full-width"; ?>">
                                        <div class="number"><i
                                                    class="icon-soccer_ico_tshirt"></i> <?php echo esc_attr($player_number); ?>
                                        </div>
                                        <div class="position heading-font"><?php echo esc_attr($position); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach;
            } ?>
        </div>
    </div>
    </div>
<?php if (!empty($enable_carousel) and $enable_carousel == 'yes'):
    if (empty($per_row)) {
        $per_row = 4;
    }
    ?>
    <script type="text/javascript">
        (function ($) {
            "use strict";
            var unique_class = "<?php echo esc_js($id); ?>";
            var owl = $('.' + unique_class + ' .stm-players');
            $(document).ready(function () {
                initOwl(owl, unique_class);
                var tm = null;
                $('.vc_tta-tab').on('click', function() {
                    var tabId = $(this).find('a').attr('href');
                    var owlTab = $(tabId + ' .stm-players');
                    if (tm) clearTimeout(tm);
                    tm = setTimeout(function () {
                        initOwl(owlTab, tabId);
                        owlTab.trigger('destroy.owl.carousel');
                        owlTab.html(owlTab.find('.owl-stage-outer').html()).removeClass('owl-loaded');
                        initOwl(owlTab, tabId);
                    }, 200);
                });

                function initOwl(owl, uniqId) {
                    owl.owlCarousel({
                        items: 4,
                        dots: false,
                        autoplay: false,
                        slideBy: 4,
                        loop: true,
                        responsive: {
                            0: {
                                items: 1,
                                slideBy: 1
                            },
                            440: {
                                items: 2,
                                slideBy: 2
                            },
                            768: {
                                items: 3,
                                slideBy: 3
                            },
                            992: {
                                items: 3,
                                slideBy: 3
                            },
                            1100: {
                                items: <?php echo intval($per_row); ?>,
                                slideBy: <?php echo intval($per_row); ?>
                            }
                        }
                    });
                    $('.' + unique_class + ' .stm-carousel-control-prev').on('click', function () {
                        owl.trigger('prev.owl.carousel');
                    });
                    $('.' + unique_class + ' .stm-carousel-control-next').on('click', function () {
                        owl.trigger('next.owl.carousel');
                    });
                }
            });
        })(jQuery);
    </script>
<?php endif; ?>
