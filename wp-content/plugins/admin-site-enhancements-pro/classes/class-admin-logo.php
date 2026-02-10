<?php

namespace ASENHA\Classes;

/**
 * Class for Admin Logo module
 *
 * @since 7.2.0
 */
class Admin_Logo {

    /**
     * Get the admin menu width (px) based on settings.
     *
     * @since 9.2.0
     *
     * @param array $options Plugin options.
     * @return int Admin menu width in pixels.
     */
    private function get_admin_menu_width_px( $options ) {
        $default_width_px = 160;

        if ( ! is_array( $options ) ) {
            return $default_width_px;
        }

        if ( array_key_exists( 'wider_admin_menu', $options ) && $options['wider_admin_menu'] ) {
            $width_raw = isset( $options['admin_menu_width'] ) ? $options['admin_menu_width'] : '';
            $width_px  = absint( preg_replace( '/[^0-9]/', '', (string) $width_raw ) );

            return $width_px > 0 ? $width_px : 200;
        }

        return $default_width_px;
    }

    /**
     * Attempt to get the intrinsic dimensions of the configured admin logo image.
     *
     * Works best when the value is from the Media Library / uploads. External URLs may not be resolvable.
     *
     * @since 9.2.0
     *
     * @param string $logo_image_raw Raw stored option value (may be relative uploads path or URL).
     * @param string $logo_image_url Normalized logo URL.
     * @return array{width:int,height:int} Intrinsic dimensions if known, otherwise 0/0.
     */
    private function get_admin_logo_image_dimensions( $logo_image_raw, $logo_image_url ) {
        $dims = array(
            'width'  => 0,
            'height' => 0,
        );

        $logo_image_raw = (string) $logo_image_raw;
        $logo_image_url = (string) $logo_image_url;

        // 1) If the saved value is a relative uploads path, try reading dimensions from the local file.
        if ( false === strpos( $logo_image_raw, 'http' ) && false !== strpos( $logo_image_raw, '/uploads/' ) ) {
            $uploads = wp_get_upload_dir();
            if ( ! empty( $uploads['basedir'] ) ) {
                $relative_path = str_replace( '/uploads', '', $logo_image_raw );
                $file_path     = wp_normalize_path( trailingslashit( $uploads['basedir'] ) . ltrim( $relative_path, '/' ) );

                if ( @file_exists( $file_path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                    $ext = strtolower( (string) pathinfo( $file_path, PATHINFO_EXTENSION ) );

                    // SVGs may use width/height="100%"; prefer viewBox-derived dimensions.
                    if ( 'svg' === $ext ) {
                        $common_methods = new Common_Methods;
                        $svg_dims       = $common_methods->get_svg_intrinsic_dimensions_from_file( $file_path );

                        if ( ! empty( $svg_dims['width'] ) && ! empty( $svg_dims['height'] ) ) {
                            $dims['width']  = absint( $svg_dims['width'] );
                            $dims['height'] = absint( $svg_dims['height'] );

                            return $dims;
                        }
                    } else {
                        $image_size = @getimagesize( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPressVIPMinimum.Functions.RestrictedFunctions.getimagesize
                        if ( is_array( $image_size ) && ! empty( $image_size[0] ) && ! empty( $image_size[1] ) ) {
                            $dims['width']  = absint( $image_size[0] );
                            $dims['height'] = absint( $image_size[1] );

                            return $dims;
                        }
                    }
                }
            }
        }

        // 2) Try attachment metadata when the URL can be mapped back to an attachment ID.
        if ( ! empty( $logo_image_url ) ) {
            $attachment_id = attachment_url_to_postid( $logo_image_url );
            if ( $attachment_id ) {
                // For SVG attachments, use the file's intrinsic viewBox ratio when needed.
                $mime_type = get_post_mime_type( $attachment_id );
                if ( 'image/svg+xml' === $mime_type ) {
                    $svg_path       = get_attached_file( $attachment_id );
                    $common_methods = new Common_Methods;
                    $svg_dims       = $common_methods->get_svg_intrinsic_dimensions_from_file( $svg_path );

                    if ( ! empty( $svg_dims['width'] ) && ! empty( $svg_dims['height'] ) ) {
                        $dims['width']  = absint( $svg_dims['width'] );
                        $dims['height'] = absint( $svg_dims['height'] );

                        return $dims;
                    }
                }

                $meta = wp_get_attachment_metadata( $attachment_id );
                if ( is_array( $meta ) && ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
                    $dims['width']  = absint( $meta['width'] );
                    $dims['height'] = absint( $meta['height'] );
                }
            }
        }

        return $dims;
    }

    /**
     * Compute a stable reserved height for the admin menu logo to prevent layout shift.
     *
     * @since 9.2.0
     *
     * @param int   $menu_width_px Admin menu width in pixels.
     * @param array $image_dims    Array with 'width' and 'height' intrinsic dimensions.
     * @return int Reserved height in pixels.
     */
    private function get_admin_menu_logo_reserved_height_px( $menu_width_px, $image_dims ) {
        $menu_width_px = absint( $menu_width_px );
        $menu_width_px = $menu_width_px > 0 ? $menu_width_px : 160;

        $padding_left   = 10;
        $padding_right  = 16;
        $padding_top    = 16;
        $padding_bottom = 16;
        $min_height_px  = 28;

        $content_width_px = max( 1, $menu_width_px - ( $padding_left + $padding_right ) );

        $img_w = ( is_array( $image_dims ) && isset( $image_dims['width'] ) ) ? absint( $image_dims['width'] ) : 0;
        $img_h = ( is_array( $image_dims ) && isset( $image_dims['height'] ) ) ? absint( $image_dims['height'] ) : 0;

        if ( $img_w > 0 && $img_h > 0 ) {
            $img_render_h = ( $content_width_px * $img_h ) / $img_w;
        } else {
            // Fallback: assume a typical wide logo. This scales with the menu width.
            $fallback_aspect_ratio = 4; // width:height.
            $img_render_h          = $content_width_px / $fallback_aspect_ratio;
        }

        $reserved_height_px = (int) ceil( $img_render_h + $padding_top + $padding_bottom );

        return max( $min_height_px, $reserved_height_px );
    }

    /**
     * Add admin logo to the admin bar
     * 
     * @since 7.2.0
     */
    public function add_admin_bar_logo( $wp_admin_bar ) {
        $common_methods = new Common_Methods;
        $logo_image = $common_methods->get_image_url( 'admin_logo_image' );

        $options = get_option( ASENHA_SLUG_U, array() );
        $admin_bar_logo_on_frontend_link_to_dashboard = ( isset( $options['admin_logo_link_frontend'] ) ) ? $options['admin_logo_link_frontend'] : false;

        if ( $admin_bar_logo_on_frontend_link_to_dashboard ) {
            if ( ! is_admin() ) {
                $logo_link = get_admin_url();            
            } else {
                $logo_link = get_site_url();            
            }
        } else {
            $logo_link = get_site_url();
        }

        if ( ! empty( $logo_image ) ) {
            $args = array(
                'id' => 'asenha-admin-bar-logo',
                'href' => esc_url( $logo_link ),
                'title' => sprintf(
                    '<img src="%s" alt="%s" />', 
                    esc_url( $logo_image ),
                    esc_attr__( 'Admin Logo', 'admin-site-enhancements' )
                ),
                'meta' => array(
                    'class'     => 'asenha-admin-logo', 
                    'title'     => __( 'Visit the homepage', 'admin-site-enhancements' ), 
                    // 'target'    => '_blank',
                )
            );

            $wp_admin_bar->add_node( $args );            
        }
    }
    
    /**
     * Add inline styles for admin bar logo
     * 
     * @since 7.2.0
     */
    public function add_admin_bar_logo_css() {
        ?>
        <style type="text/css" id="admin-menu-logo-css">
            .asenha-admin-logo .ab-item, 
            .asenha-admin-logo a {
                line-height: 28px !important;
                display: flex;
                align-items: center;
            }

            .asenha-admin-logo img {
                vertical-align: middle;
                height: 20px !important;
            }
            
            @media screen and (max-width: 782px) {
                #wpadminbar li#wp-admin-bar-asenha-admin-bar-logo {
                    display: block;
                }
                
                #wpadminbar li#wp-admin-bar-asenha-admin-bar-logo a {
                    display: flex;
                    margin-left: 8px;
                }
            }
        </style>
        <?php
    }

    /**
     * Replace the admin bar home icon (site-name) with the site icon (wp-admin only).
     *
     * @since 9.2.0
     */
    public function replace_admin_bar_home_icon_with_site_icon_css() {
        if ( ! is_admin() ) {
            return;
        }

        if ( function_exists( 'is_admin_bar_showing' ) && ! is_admin_bar_showing() ) {
            return;
        }

        if ( ! function_exists( 'get_site_icon_url' ) ) {
            return;
        }

        $site_icon_url = get_site_icon_url( 64 );
        if ( empty( $site_icon_url ) ) {
            return;
        }

        ?>
        <style type="text/css" id="asenha-admin-bar-site-icon-css">
            .wp-admin #wpadminbar #wp-admin-bar-site-name > .ab-item:before {
                content: "" !important;
                background-image: url("<?php echo esc_url( $site_icon_url ); ?>") !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
                background-size: 20px 20px !important;
                width: 20px !important;
                height: 20px !important;
                display: inline-block !important;
            }
        </style>
        <?php
    }
    
    /**
     * Add admin logo to the top of the admin menu
     * 
     * @since 7.2.0
     */
    public function add_admin_menu_logo() {
        $common_methods = new Common_Methods;
        $logo_image_url = $common_methods->get_image_url( 'admin_logo_image' );

        $options = get_option( ASENHA_SLUG_U, array() );

        if ( empty( $logo_image_url ) ) {
            return;
        }

        $menu_width_px   = $this->get_admin_menu_width_px( $options );
        $logo_image_raw  = isset( $options['admin_logo_image'] ) ? $options['admin_logo_image'] : '';
        $logo_image_dims = $this->get_admin_logo_image_dimensions( $logo_image_raw, $logo_image_url );

        // For external URLs, attempt to use stored intrinsic dimensions (captured in the settings screen via JS).
        if ( empty( $logo_image_dims['width'] ) || empty( $logo_image_dims['height'] ) ) {
            $stored_width  = isset( $options['admin_logo_image_width'] ) ? absint( $options['admin_logo_image_width'] ) : 0;
            $stored_height = isset( $options['admin_logo_image_height'] ) ? absint( $options['admin_logo_image_height'] ) : 0;

            if ( $stored_width > 0 && $stored_height > 0 ) {
                $logo_image_dims = array(
                    'width'  => $stored_width,
                    'height' => $stored_height,
                );
            }
        }

        $logo_height_px = $this->get_admin_menu_logo_reserved_height_px( $menu_width_px, $logo_image_dims );

        $logo_link  = get_site_url();
        $logo_title = __( 'Visit the homepage', 'admin-site-enhancements' );

        $img_dim_attr = '';
        if ( ! empty( $logo_image_dims['width'] ) && ! empty( $logo_image_dims['height'] ) ) {
            $img_dim_attr = sprintf(
                ' width="%d" height="%d"',
                absint( $logo_image_dims['width'] ),
                absint( $logo_image_dims['height'] )
            );
        }

        $logo_markup = sprintf(
            '<a id="admin_menu_logo" href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s" aria-label="%2$s"><img src="%3$s" alt="%4$s"%5$s /></a>',
            esc_url( $logo_link ),
            esc_attr( $logo_title ),
            esc_url( $logo_image_url ),
            esc_attr__( 'Admin Logo', 'admin-site-enhancements' ),
            $img_dim_attr
        );

        $needs_dims_backfill = empty( $logo_image_dims['width'] ) || empty( $logo_image_dims['height'] );
        $dims_nonce          = wp_create_nonce( 'asenha_admin_logo_dims_' . get_current_user_id() );
        ?>
        <script type="text/javascript" id="admin-menu-logo-script">
            /* <![CDATA[ */
            jQuery(document).ready(function() {
                if ( jQuery("#admin_menu_logo").length ) {
                    return;
                }

                var logoMarkup = <?php echo wp_json_encode( $logo_markup ); ?>;
                var $adminMenuWrap = jQuery("#adminmenuwrap");

                if ( $adminMenuWrap.length ) {
                    $adminMenuWrap.prepend( logoMarkup );
                }

                // Backfill missing intrinsic dimensions (for previously-saved external URLs) and persist via AJAX.
                var needsDimsBackfill = <?php echo wp_json_encode( $needs_dims_backfill ); ?>;
                if ( ! needsDimsBackfill || window.asenhaAdminLogoDimsBackfillRan ) {
                    return;
                }

                window.asenhaAdminLogoDimsBackfillRan = true;

                var logoUrl = <?php echo wp_json_encode( $logo_image_url ); ?>;
                var logoImageRaw = <?php echo wp_json_encode( (string) $logo_image_raw ); ?>;
                var menuWidthPx = <?php echo wp_json_encode( (int) $menu_width_px ); ?>;
                var nonce = <?php echo wp_json_encode( $dims_nonce ); ?>;

                if ( ! logoUrl ) {
                    return;
                }

                var img = new Image();
                img.onload = function() {
                    var imgW = parseInt(img.naturalWidth, 10);
                    var imgH = parseInt(img.naturalHeight, 10);

                    if ( isNaN(imgW) || isNaN(imgH) || imgW <= 0 || imgH <= 0 ) {
                        return;
                    }

                    // Match the PHP reserved-height calculation.
                    var paddingLeft = 10;
                    var paddingRight = 16;
                    var paddingTop = 16;
                    var paddingBottom = 16;
                    var minHeightPx = 28;

                    var contentWidthPx = Math.max(1, parseInt(menuWidthPx, 10) - (paddingLeft + paddingRight));
                    var imgRenderH = (contentWidthPx * imgH) / imgW;
                    var reservedHeightPx = Math.ceil(imgRenderH + paddingTop + paddingBottom);
                    reservedHeightPx = Math.max(minHeightPx, reservedHeightPx);

                    var $logoImg = jQuery('#admin_menu_logo img');
                    if ( $logoImg.length ) {
                        $logoImg.attr('width', imgW);
                        $logoImg.attr('height', imgH);
                    }

                    if ( typeof ajaxurl === 'undefined' ) {
                        return;
                    }

                    jQuery.post(ajaxurl, {
                        action: 'asenha_save_admin_logo_dims',
                        nonce: nonce,
                        width: imgW,
                        height: imgH,
                        logo_image_raw: logoImageRaw
                    });
                };
                img.onerror = function() {
                    // Do nothing. We'll keep fallback aspect ratio.
                };
                img.src = logoUrl;
            });
            /* ]]> */
        </script>
        <style type="text/css" id="admin-menu-logo-css">
            :root {
                --asenha-admin-menu-logo-height: <?php echo esc_html( $logo_height_px ); ?>px;
            }

            /* Reserve space for the logo without affecting #adminmenuwrap (prevents scroll trap). */
            #adminmenu {
                padding-top: var(--asenha-admin-menu-logo-height);
            }

            #admin_menu_logo {
                box-sizing: border-box;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 10;
                width: 100%;
                height: var(--asenha-admin-menu-logo-height);
                padding: 16px 16px 16px 10px;
                display: flex;
                align-items: center;
                transition: .25s;
                cursor: pointer;
            }
            #admin_menu_logo:hover {
                background: #2c3338;
            }
            #admin_menu_logo img {
                display: block;
                width: 100%;
                height: auto;
                max-height: calc(var(--asenha-admin-menu-logo-height) - 32px);
            }
            .folded #admin_menu_logo {
                display: none;
            }
            .folded #adminmenu {
                padding-top: 0;
            }
            @media screen and ( min-width: 783px ) {
                #adminmenu {
                    margin-top: 0;
                }                
            }
            @media screen and ( max-width: 960px ) {
                #admin_menu_logo {
                    display: none;
                }
                #adminmenu {
                    padding-top: 0;
                }
            }
        </style>
        <?php        
    }

    /**
     * Save intrinsic dimensions for the configured admin logo image (AJAX).
     *
     * Used for external image URLs where attachment metadata is not available.
     *
     * @since 9.2.0
     */
    public function save_admin_logo_dims() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ),
                ),
                403
            );
        }

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'asenha_admin_logo_dims_' . get_current_user_id() ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid nonce.', 'admin-site-enhancements' ),
                ),
                403
            );
        }

        $width  = isset( $_POST['width'] ) ? absint( wp_unslash( $_POST['width'] ) ) : 0;
        $height = isset( $_POST['height'] ) ? absint( wp_unslash( $_POST['height'] ) ) : 0;

        if ( $width <= 0 || $height <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid image dimensions.', 'admin-site-enhancements' ),
                ),
                400
            );
        }

        $posted_logo_raw = isset( $_POST['logo_image_raw'] ) ? sanitize_text_field( wp_unslash( $_POST['logo_image_raw'] ) ) : '';

        $options        = get_option( ASENHA_SLUG_U, array() );
        $current_logo   = isset( $options['admin_logo_image'] ) ? (string) $options['admin_logo_image'] : '';
        $current_logo   = (string) $current_logo;
        $posted_logo_raw = (string) $posted_logo_raw;

        // Safety: only update dims when the request matches the currently saved value.
        if ( ! empty( $posted_logo_raw ) && $posted_logo_raw !== $current_logo ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Logo value mismatch.', 'admin-site-enhancements' ),
                ),
                409
            );
        }

        $options['admin_logo_image_width']  = $width;
        $options['admin_logo_image_height'] = $height;

        update_option( ASENHA_SLUG_U, $options, true );

        wp_send_json_success(
            array(
                'width'  => $width,
                'height' => $height,
            )
        );
    }

}