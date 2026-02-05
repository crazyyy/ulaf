<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Canvas Shortcode Handler
 * 
 * Renders canvas galleries on the frontend.
 * Usage: [mgwpp_canvas id="123"]
 * 
 * @param array $atts Shortcode attributes
 * @return string Rendered HTML
 */
function mgwpp_canvas_shortcode($atts)
{
    $atts = shortcode_atts([
        'id' => 0,
    ], $atts, 'mgwpp_canvas');

    $canvas_id = absint($atts['id']);

    if (!$canvas_id) {
        return '<!-- Mini Gallery Canvas: No ID specified -->';
    }

    $canvas = get_post($canvas_id);

    if (!$canvas || $canvas->post_type !== 'mgwpp_canvas') {
        return '<!-- Mini Gallery Canvas: Invalid canvas ID -->';
    }

    // Check if canvas module is available
    if (!class_exists('MGWPP_Canvas_Post_Type')) {
        return '<!-- Mini Gallery Canvas: Canvas module is not enabled -->';
    }

    $data = MGWPP_Canvas_Post_Type::get_canvas_data($canvas_id);

    // Check for either legacy items or new slides
    if (empty($data) || (empty($data['items']) && empty($data['slides']))) {
        return '<!-- Mini Gallery Canvas: Empty canvas -->';
    }

    // Enqueue frontend styles
    wp_enqueue_style(
        'mgwpp-canvas-frontend',
        MG_PLUGIN_URL . '/includes/functions/mgwpp-canvas-frontend.css',
        [],
        filemtime(MG_PLUGIN_PATH . '/includes/functions/mgwpp-canvas-frontend.css')
    );

    $settings = isset($data['canvas_settings']) ? $data['canvas_settings'] : [];
    $settings = wp_parse_args($settings, [
        'width' => 1200,
        'height' => 800,
        'background' => '#ffffff'
    ]);

    $items = isset($data['items']) ? $data['items'] : [];


    $slider_id = 'mgwpp-slider-' . $canvas_id;
    $slider_settings = $data['slider_settings'] ?? ['autoplay' => false, 'effect' => 'slide', 'arrows' => true, 'dots' => true];

    // Ensure slides fallback
    if (empty($data['slides'])) {
        $slides = [['id' => 'default', 'items' => $data['items'] ?? []]];
    } else {
        $slides = $data['slides'];
    }

    ob_start();
?>
    <div id="<?php echo esc_attr($slider_id); ?>" class="mgwpp-canvas-slider"
        data-settings="<?php echo esc_attr(json_encode($slider_settings)); ?>"
        style="max-width: <?php echo esc_attr($settings['width']); ?>px; aspect-ratio: <?php echo esc_attr($settings['width']); ?> / <?php echo esc_attr($settings['height']); ?>;">

        <div class="mgwpp-slides-track">
            <?php foreach ($slides as $index => $slide) : ?>
                <div class="mgwpp-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo esc_attr($index); ?>"
                    style="background: <?php echo esc_attr($settings['background']); ?>;">
                    <?php foreach ($slide['items'] as $item) : ?>
                        <?php echo wp_kses_post(mgwpp_render_canvas_item($item, $settings)); ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($slider_settings['arrows'])) : ?>
            <button class="mgwpp-slider-arrow prev" aria-label="<?php esc_attr_e('Previous Slide', 'mini-gallery'); ?>">&lt;</button>
            <button class="mgwpp-slider-arrow next" aria-label="<?php esc_attr_e('Next Slide', 'mini-gallery'); ?>">&gt;</button>
        <?php endif; ?>

        <?php if (!empty($slider_settings['dots'])) : ?>
            <div class="mgwpp-slider-dots">
                <?php foreach ($slides as $index => $slide) : ?>
                    <?php
                    /* translators: %d: slide number */
                    $slide_label = sprintf(__('Go to slide %d', 'mini-gallery'), $index + 1);
                    ?>
                    <button class="mgwpp-slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr($slide_label); ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <script>
            (function() {
                const slider = document.getElementById('<?php echo esc_js($slider_id); ?>');
                if (!slider) return;

                const track = slider.querySelector('.mgwpp-slides-track');
                const slides = Array.from(slider.querySelectorAll('.mgwpp-slide'));
                const dots = slider.querySelectorAll('.mgwpp-slider-dot');
                const prevBtn = slider.querySelector('.mgwpp-slider-arrow.prev');
                const nextBtn = slider.querySelector('.mgwpp-slider-arrow.next');
                const settings = JSON.parse(slider.dataset.settings);

                // Apply Effect Class
                slider.classList.add('mgwpp-effect-' + (settings.effect || 'slide'));

                let currentIndex = 0;
                let autoplayTimer;

                // Initialize positions
                function updateSlideClasses() {
                    slides.forEach((slide, index) => {
                        slide.classList.remove('active', 'prev-slide', 'next-slide');

                        if (index === currentIndex) {
                            slide.classList.add('active');
                        } else if (index === getPrevIndex(currentIndex)) {
                            slide.classList.add('prev-slide');
                        } else if (index === getNextIndex(currentIndex)) {
                            slide.classList.add('next-slide');
                        }
                    });

                    // Dots
                    if (dots.length) {
                        dots.forEach((d, i) => {
                            d.classList.toggle('active', i === currentIndex);
                        });
                    }
                }

                function getPrevIndex(i) {
                    return (i - 1 + slides.length) % slides.length;
                }

                function getNextIndex(i) {
                    return (i + 1) % slides.length;
                }

                function goToSlide(index) {
                    if (index === currentIndex) return;

                    // Wrap index
                    if (index < 0) index = slides.length - 1;
                    if (index >= slides.length) index = 0;

                    currentIndex = index;
                    updateSlideClasses();
                }

                // Init
                updateSlideClasses();

                if (prevBtn) prevBtn.addEventListener('click', () => {
                    stopAutoplay();
                    goToSlide(currentIndex - 1);
                });
                if (nextBtn) nextBtn.addEventListener('click', () => {
                    stopAutoplay();
                    goToSlide(currentIndex + 1);
                });

                if (dots.length) {
                    dots.forEach((dot, idx) => {
                        dot.addEventListener('click', () => {
                            stopAutoplay();
                            goToSlide(idx);
                        });
                    });
                }

                function startAutoplay() {
                    if (settings.autoplay && !!settings.autoplaySpeed) {
                        autoplayTimer = setInterval(() => goToSlide(currentIndex + 1), parseInt(settings.autoplaySpeed) || 3000);
                    }
                }

                function stopAutoplay() {
                    if (autoplayTimer) clearInterval(autoplayTimer);
                }

                startAutoplay();
            })();
        </script>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Helper to format CSS values
 */
function mgwpp_format_css_value($val, $is_dimension = true)
{
    if ($val === 'auto') return 'auto';
    if (is_numeric($val)) {
        return $val . 'px'; // Default to px for raw numbers
    }
    return esc_attr($val); // Assume valid unit string
}

/**
 * Render individual canvas item
 * 
 * @param array $item Item data
 * @param array $settings Canvas settings
 * @param bool $is_root Is this a root item (absolute) or child (relative)?
 * @return string Rendered HTML
 */
function mgwpp_render_canvas_item($item, $settings, $is_root = true)
{
    // Styles calculation
    $styles = [];

    // Position & Size
    $styles[] = 'width: ' . mgwpp_format_css_value($item['width'] ?? 'auto');
    $styles[] = 'height: ' . mgwpp_format_css_value($item['height'] ?? 'auto');
    $styles[] = 'z-index: ' . intval($item['z_index'] ?? 1);
    $styles[] = 'opacity: ' . floatval($item['opacity'] ?? 1);
    $styles[] = 'transform: rotate(' . intval($item['rotation'] ?? 0) . 'deg)';

    if ($is_root) {
        $styles[] = 'position: absolute';
        $styles[] = 'left: ' . mgwpp_format_css_value($item['x'] ?? 0);
        $styles[] = 'top: ' . mgwpp_format_css_value($item['y'] ?? 0);
    } else {
        $styles[] = 'position: relative';
        // In flex container, left/top are ignored or auto, but let's reset them
        $styles[] = 'left: auto';
        $styles[] = 'top: auto';
    }

    $style_str = implode('; ', $styles) . ';';

    $type = $item['type'] ?? 'div';
    $output = '<div class="mgwpp-canvas-item mgwpp-canvas-item-' . esc_attr($type) . '" style="' . esc_attr($style_str) . '">';

    // Inner Content
    $inner_style = 'width: 100%; height: 100%; display: block;';

    switch ($type) {
        case 'image':
            $link_start = '';
            $link_end = '';

            if (!empty($item['link'])) {
                $link_start = '<a href="' . esc_url($item['link']) . '" style="display:block;width:100%;height:100%;">';
                $link_end = '</a>';
            }

            $output .= $link_start;
            $output .= '<img src="' . esc_url($item['image_url']) . '" alt="' . esc_attr($item['alt_text'] ?? '') . '" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">';
            $output .= $link_end;
            break;

        case 'text':
            $text_style = sprintf(
                'font-size: %s; color: %s; font-family: %s; text-align: %s; line-height: 1.4; white-space: pre-wrap; word-break: break-word;',
                mgwpp_format_css_value($item['font_size'] ?? 16),
                esc_attr($item['color'] ?? '#000'),
                esc_attr($item['font_family'] ?? 'Arial'),
                esc_attr($item['text_align'] ?? 'left')
            );
            $output .= '<div class="mgwpp-canvas-text" style="' . esc_attr($text_style) . '">';
            $output .= wp_kses_post($item['content'] ?? '');
            $output .= '</div>';
            break;

        case 'button':
            $btn_style = sprintf(
                'background-color: %s; color: %s; border-radius: %s; display: flex; align-items: center; justify-content: center; width: 100%%; height: 100%%; text-decoration: none; box-sizing: border-box;',
                esc_attr($item['bg_color'] ?? '#0073aa'),
                esc_attr($item['text_color'] ?? '#fff'),
                mgwpp_format_css_value($item['border_radius'] ?? 4)
            );
            $output .= '<a href="' . esc_url($item['link'] ?? '#') . '" class="mgwpp-canvas-button mgwpp-cta" style="' . esc_attr($btn_style) . '" data-item-id="' . esc_attr($item['id']) . '">';
            $output .= esc_html($item['text'] ?? 'Button');
            $output .= '</a>';
            break;

        case 'shape':
            $shape_style = sprintf(
                'background-color: %s; border: %s solid %s; box-sizing: border-box; width: 100%%; height: 100%%;',
                esc_attr($item['fill_color'] ?? '#ccc'),
                mgwpp_format_css_value($item['stroke_width'] ?? 0),
                esc_attr($item['stroke_color'] ?? '#333')
            );

            if (($item['shape_type'] ?? 'rectangle') === 'circle') {
                $shape_style .= ' border-radius: 50%;';
            }

            $output .= '<div class="mgwpp-canvas-shape" style="' . esc_attr($shape_style) . '"></div>';
            break;

        case 'container':
            $container_style = sprintf(
                'display: %s; flex-direction: %s; justify-content: %s; align-items: %s; gap: %s; padding: %s; background-color: %s; border: %s solid %s; box-sizing: border-box; width: 100%%; height: 100%%;',
                esc_attr($item['display'] ?? 'flex'),
                esc_attr($item['direction'] ?? 'row'),
                esc_attr($item['justify'] ?? 'flex-start'),
                esc_attr($item['align'] ?? 'stretch'),
                mgwpp_format_css_value($item['gap'] ?? 0),
                mgwpp_format_css_value($item['padding'] ?? 0),
                esc_attr($item['bg_color'] ?? 'transparent'),
                mgwpp_format_css_value($item['border_width'] ?? 0),
                esc_attr($item['border_color'] ?? 'transparent')
            );

            $output .= '<div class="mgwpp-canvas-container" style="' . esc_attr($container_style) . '">';

            // Recursive Render
            if (!empty($item['children']) && is_array($item['children'])) {
                // Sort by z-index
                usort($item['children'], function ($a, $b) {
                    return ($a['z_index'] ?? 0) - ($b['z_index'] ?? 0);
                });

                foreach ($item['children'] as $child) {
                    $output .= mgwpp_render_canvas_item($child, $settings, false); // false = not root
                }
            }

            $output .= '</div>';
            break;
    }

    $output .= '</div>';

    return $output;
}
