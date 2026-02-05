<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_Testimonials_View
{
    public static function render()
    {
        $testimonials = self::get_testimonials();
        self::enqueue_assets();
        ?>
        <div class="mgwpp-dashboard-container mgwpp-premium-dashboard">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    
                    <?php MGWPP_Inner_Header::render(); ?>
                    
                    <div class="wrap">
                        <div class="mgwpp-dashboard-header">
                            <h1 class="wp-heading-inline">
                                <?php esc_html_e('Testimonials', 'mini-gallery'); ?>
                            </h1>
                            <div class="mgwpp-header-actions">
                                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=mgwpp_testimonial')); ?>" 
                                   class="mgwpp-btn mgwpp-btn-primary">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php esc_html_e('Add New Testimonial', 'mini-gallery'); ?>
                                </a>
                            </div>
                        </div>

                        <?php if (empty($testimonials)) : ?>
                            <div class="mgwpp-empty-state">
                                <div class="mgwpp-empty-icon">
                                    <span class="dashicons dashicons-format-quote"></span>
                                </div>
                                <h3><?php esc_html_e('No testimonials found', 'mini-gallery'); ?></h3>
                                <p><?php esc_html_e('Create your first testimonial to get started', 'mini-gallery'); ?></p>
                                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=mgwpp_testimonial')); ?>" 
                                   class="mgwpp-btn mgwpp-btn-primary">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php esc_html_e('Add Testimonial', 'mini-gallery'); ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="mgwpp-testimonials-grid">
                                <?php foreach ($testimonials as $testimonial) : ?>
                                    <?php self::render_testimonial_card($testimonial); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private static function enqueue_assets()
    {
        $plugin_version = defined('MG_VERSION') ? MG_VERSION : '1.0.0';

        // Enqueue the galleries CSS for base premium styling
        wp_enqueue_style(
            'mgwpp-admin-galleries',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.css', dirname(__FILE__, 3)),
            array(),
            $plugin_version
        );

        // Enqueue testimonials-specific CSS
        wp_enqueue_style(
            'mgwpp-admin-testimonials',
            plugins_url('admin/views/testimonials/mgwpp-testimonials-view.css', dirname(__FILE__, 3)),
            array('mgwpp-admin-galleries'),
            $plugin_version
        );
    }

    private static function get_testimonials()
    {
        return get_posts([
            'post_type'      => 'mgwpp_testimonial',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);
    }

    private static function render_testimonial_card($testimonial)
    {
        $author = sanitize_text_field(
            get_post_meta($testimonial->ID, '_mgwpp_author', true)
        );
        $position = sanitize_text_field(
            get_post_meta($testimonial->ID, '_mgwpp_position', true)
        );
        $avatar_id = get_post_meta($testimonial->ID, '_mgwpp_avatar', true);
        $rating = intval(get_post_meta($testimonial->ID, '_mgwpp_rating', true));
        $content = wp_trim_words($testimonial->post_content, 30);
        
        $edit_url = get_edit_post_link($testimonial->ID);
        $delete_url = wp_nonce_url(
            admin_url("post.php?post={$testimonial->ID}&action=delete"),
            "delete-post_{$testimonial->ID}"
        );
        ?>
        <div class="mgwpp-testimonial-card">
            <div class="mgwpp-card-inner">
                <div class="mgwpp-card-header">
                    <div class="mgwpp-card-glare"></div>
                    <div class="mgwpp-testimonial-avatar">
                        <?php if ($avatar_id && wp_attachment_is_image($avatar_id)) : ?>
                            <?php echo wp_get_attachment_image($avatar_id, 'thumbnail', false, ['class' => 'mgwpp-avatar-img']); ?>
                        <?php else : ?>
                            <div class="mgwpp-avatar-placeholder">
                                <span class="dashicons dashicons-admin-users"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mgwpp-card-overlay">
                        <div class="mgwpp-overlay-actions">
                            <?php if (current_user_can('edit_post', $testimonial->ID)) : ?>
                                <a href="<?php echo esc_url($edit_url); ?>" title="<?php esc_attr_e('Edit', 'mini-gallery'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                            <?php endif; ?>
                            <?php if (current_user_can('delete_post', $testimonial->ID)) : ?>
                                <a href="<?php echo esc_url($delete_url); ?>" class="submitdelete" 
                                   title="<?php esc_attr_e('Delete', 'mini-gallery'); ?>"
                                   onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this testimonial?', 'mini-gallery'); ?>');">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mgwpp-card-body">
                    <div class="mgwpp-testimonial-quote">
                        <span class="dashicons dashicons-format-quote"></span>
                    </div>
                    
                    <div class="mgwpp-testimonial-content">
                        <?php echo esc_html($content); ?>
                    </div>
                    
                    <?php if ($rating > 0) : ?>
                        <div class="mgwpp-testimonial-rating">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <span class="dashicons dashicons-star-<?php echo ($i <= $rating) ? 'filled' : 'empty'; ?>"></span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mgwpp-testimonial-author">
                        <span class="mgwpp-author-name"><?php echo esc_html($author ?: __('Anonymous', 'mini-gallery')); ?></span>
                        <?php if ($position) : ?>
                            <span class="mgwpp-author-position"><?php echo esc_html($position); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}