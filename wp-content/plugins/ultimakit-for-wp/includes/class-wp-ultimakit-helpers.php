<?php

class UltimaKit_Helpers {
    public function __construct() {
    }

    public function ultimakit_asset_condition() {
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], ULTIMAKIT_FOR_WP_ALLOWED_PAGES ) ) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves or displays the custom header content for the theme.
     *
     * This function is a custom implementation for fetching or rendering the header content.
     * It can be used to include custom header templates or dynamic header elements specific
     * to the theme or plugin. The function can be tailored to support different header styles
     * or configurations based on context or preferences set in the theme options.
     */
    public function ultimakit_get_header() {
        ?>
		<div class="ultimakit-header">
			<div class="header-brand">
				<img src="<?php 
        echo esc_url( ULTIMAKIT_FOR_WP_LOGO );
        ?>" alt="UltimaKit Logo" class="logo">
				<div class="version-info"><?php 
        echo esc_html_e( 'Current version:', 'ultimakit-for-wp' );
        ?> <?php 
        echo esc_html_e( ULTIMAKIT_FOR_WP_VERSION );
        ?></div>
			</div>
			
			<div class="header-actions">
				<?php 
        ?>
						<a class="btn btn-primary" target="_blank" href="https://xwpstack.com/pricing">
							<?php 
        echo esc_html_e( 'Get Pro', 'ultimakit-for-wp' );
        ?>
						</a>
						<?php 
        ?>
				<a class="btn btn-primary" style="background:white !important;" target="_blank" href="https://pluginstack.dev/plugins">
					<?php 
        echo esc_html_e( 'Get All PluginStack Plugins for 75% OFF!', 'ultimakit-for-wp' );
        ?>
				</a>
			</div>
		</div>
		<?php 
    }

    public function ultimakit_generate_form( $args = array(), $modal_type = '' ) {
        $modal_title = sanitize_text_field( $args['title'] );
        $fields = $args['fields'];
        $custom_option = ( isset( $args['custom_option'] ) ? $args['custom_option'] : '' );
        ?>
		<!-- Modal -->
		<div class="wpuk_modal " id="<?php 
        echo esc_attr( $this->ID );
        ?>_modal" tabindex="-1" aria-labelledby="<?php 
        echo esc_attr( $this->ID );
        ?>_modal" aria-hidden="true">
			<div class="<?php 
        echo esc_attr( $modal_type );
        ?>">
				<div class="">
					<div class="modal-body">
						<form id="<?php 
        echo esc_attr( $args['ID'] );
        ?>_form" class="module_settings" method="post">
							<input type="hidden" id="module_id" value="<?php 
        echo esc_attr( $args['ID'] );
        ?>">
						<?php 
        $this->ultimakit_generate_fields( $fields );
        ?>
					</div>
					<div class="modal-footer">
						<!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php 
        esc_html_e( 'Reset', 'ultimakit-for-wp' );
        ?></button> -->
						<button type="submit" class="btn btn-primary wpuk_save_module_settings" custom-option="<?php 
        echo esc_attr( $custom_option );
        ?>" id="<?php 
        echo esc_attr( $this->ID );
        ?>_form"><?php 
        esc_html_e( 'Save changes', 'ultimakit-for-wp' );
        ?></button>

					</div>
					</form>
				</div>
			</div>
		</div>
		<?php 
    }

    public function ultimakit_generate_modal( $args = array(), $modal_type = '' ) {
        $modal_title = sanitize_text_field( $args['title'] );
        $fields = $args['fields'];
        $close_btn = ( isset( $args['close_btn'] ) ? sanitize_text_field( $args['close_btn'] ) : __( 'Close', 'ultimakit-for-wp' ) );
        $save_btn = ( isset( $args['save_btn'] ) ? sanitize_text_field( $args['save_btn'] ) : __( 'Save changes', 'ultimakit-for-wp' ) );
        if ( !$this->ultimakit_asset_condition() ) {
            return;
        }
        ?>
		<!-- Modal -->
		<div class="wpuk_modal modal modal-lg fade" id="<?php 
        echo esc_attr( $args['ID'] );
        ?>_modal" tabindex="-1" aria-labelledby="<?php 
        echo esc_attr( $this->ID );
        ?>_modal" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered <?php 
        echo esc_html_e( $modal_type );
        ?>">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><?php 
        echo esc_html( $modal_title );
        ?></h5>
					</div>
					<div class="modal-body">
						<form id="<?php 
        echo esc_attr( $args['ID'] );
        ?>_form" class="module_settings" method="post">
							<input type="hidden" id="module_id" value="<?php 
        echo esc_attr( $args['ID'] );
        ?>">
							<?php 
        $this->ultimakit_generate_fields( $fields );
        ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php 
        echo esc_html( $close_btn );
        ?></button>
						<button type="submit" class="btn btn-primary wpuk_save_module_settings <?php 
        echo esc_attr( $this->ID );
        ?>_form" id="<?php 
        echo esc_attr( $this->ID );
        ?>_form"><?php 
        echo esc_html( $save_btn );
        ?></button>

					</div>
					</form>
				</div>
			</div>
		</div>
		<?php 
    }

    public function ultimakit_generate_fields( $fields ) {
        if ( !empty( $fields ) ) {
            echo '<ul>';
            foreach ( $fields as $key => $value ) {
                echo '<li>';
                switch ( $value['type'] ) {
                    case 'text':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br />';
                        $placeholder = ( isset( $value['placeholder'] ) ? esc_attr( $value['placeholder'] ) : '' );
                        $required = ( isset( $value['required'] ) ? 'required' : '' );
                        $valueAttr = ( isset( $value['value'] ) ? esc_attr( $value['value'] ) : '' );
                        echo '<input type="text" ' . $required . ' id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . $valueAttr . '" placeholder="' . $placeholder . '">';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'number':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br />';
                        $placeholder = ( isset( $value['placeholder'] ) ? esc_attr( $value['placeholder'] ) : '' );
                        $required = ( isset( $value['required'] ) ? 'required' : '' );
                        $valueAttr = ( isset( $value['value'] ) ? esc_attr( $value['value'] ) : '' );
                        echo '<input type="number" ' . $required . ' id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . $valueAttr . '" placeholder="' . $placeholder . '">';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'color':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br />';
                        echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" class="ultimakit-color-picker" value="' . esc_attr( $value['value'] ) . '">';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'hidden':
                        echo '<input type="hidden" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value['value'] ) . '">';
                        break;
                    case 'textarea':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br />';
                        echo '<textarea rows="5" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_attr( $value['value'] ) . '</textarea>';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'textarea2':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br />';
                        echo '<textarea rows="5" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_attr( $value['value'] ) . '</textarea>';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'password':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br />';
                        echo '<input type="password" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value['value'] ) . '">';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'checkbox':
                        $checked = ( 'on' === $value['value'] ? 'checked' : '' );
                        echo '<input ' . esc_attr( $checked ) . ' type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value['value'] ) . '"> <label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label>';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'switch':
                        $checked = ( 'on' === $value['value'] ? 'checked' : '' );
                        echo '<div class="form-check form-switch module-switch"><input ' . esc_attr( $checked ) . ' class="form-check-input" type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value['value'] ) . '"> <label class="form-check-label switch-label" for="' . esc_attr( $key ) . '">toggle me</label>' . esc_html( $value['label'] );
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        echo '</div>';
                        break;
                    case 'radio':
                        $checked = ( 'on' === $value['value'] ? 'checked="checked"' : '' );
                        echo '<input ' . esc_attr( $checked ) . ' type="radio" name="' . esc_attr( $key ) . '"> <label for="' . esc_attr( $key ) . '"> ' . esc_html( $value['label'] ) . '</label>';
                        break;
                    case 'select':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br/>';
                        echo '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '">';
                        if ( !empty( $value['options'] ) ) {
                            foreach ( $value['options'] as $op_key => $op_value ) {
                                // Check if 'default' exists in $value, if not, set an empty string or a fallback value
                                $default_value = ( isset( $value['default'] ) ? $value['default'] : '' );
                                // Use the $default_value for comparison
                                $selected = selected( $op_key, $default_value, false );
                                echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $op_key ) . '">' . esc_html( $op_value ) . '</option>';
                            }
                        }
                        echo '</select>';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'select2':
                        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $value['label'] ) . '</label><br/>';
                        echo '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" class="form-control select2" multiple="multiple">';
                        if ( !empty( $value['options'] ) ) {
                            foreach ( $value['options'] as $op_key => $op_value ) {
                                $selected = selected( $op_key, $value['default'], false );
                                echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $op_key ) . '">' . esc_html( $op_value ) . '</option>';
                            }
                        }
                        echo '</select>';
                        if ( !empty( $value['desc'] ) ) {
                            echo '<br /><small>' . $value['desc'] . '</small>';
                        }
                        break;
                    case 'html':
                        echo wp_kses( $value['value'], array(
                            'a'      => array(
                                'href'   => array(),
                                'title'  => array(),
                                'target' => array(),
                                'class'  => array(),
                            ),
                            'b'      => array(),
                            'strong' => array(),
                            'i'      => array(),
                            'em'     => array(),
                            'span'   => array(
                                'class' => array(),
                            ),
                            'br'     => array(),
                            'button' => array(
                                'href'   => array(),
                                'title'  => array(),
                                'target' => array(),
                                'class'  => array(),
                            ),
                        ) );
                        break;
                }
                echo '</li>';
            }
            echo '</ul>';
        }
    }

    public function show_featured_image_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( post_type_supports( $post_type_key, 'thumbnail' ) ) {
                add_filter( "manage_{$post_type_name}_posts_columns", array($this, 'add_featured_image_column'), 999 );
                add_action(
                    "manage_{$post_type_name}_posts_custom_column",
                    array($this, 'add_featured_image'),
                    10,
                    2
                );
            }
        }
    }

    public function add_featured_image_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            if ( 'title' == $key ) {
                // We add featured image column before the 'title' column
                $new_columns['wpuk-featured-image'] = 'Featured Image';
            }
            if ( 'thumb' == $key ) {
                // For WooCommerce products, we add featured image column before it's native thumbnail column
                $new_columns['wpuk-featured-image'] = 'Product Image';
            }
            $new_columns[$key] = $value;
        }
        // Replace WooCommerce thumbnail column with ASE featured image column
        if ( array_key_exists( 'thumb', $new_columns ) ) {
            unset($new_columns['thumb']);
        }
        return $new_columns;
    }

    public function add_featured_image( $column_name, $id ) {
        if ( 'wpuk-featured-image' === $column_name ) {
            if ( has_post_thumbnail( $id ) ) {
                $size = 'thumbnail';
                echo get_the_post_thumbnail( $id, $size, '' );
            } else {
                echo '<img src="' . esc_url( plugins_url( 'assets/img/default_featured_image.jpg', __DIR__ ) ) . '" />';
            }
        }
    }

    public function show_excerpt_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( post_type_supports( $post_type_key, 'excerpt' ) ) {
                add_filter( "manage_{$post_type_name}_posts_columns", array($this, 'add_excerpt_column') );
                add_action(
                    "manage_{$post_type_name}_posts_custom_column",
                    array($this, 'add_excerpt'),
                    10,
                    2
                );
            }
        }
    }

    public function add_excerpt_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[$key] = $value;
            if ( $key == 'title' ) {
                $new_columns['wpuk-excerpt'] = 'Excerpt';
            }
        }
        return $new_columns;
    }

    public function add_excerpt( $column_name, $id ) {
        if ( 'wpuk-excerpt' === $column_name ) {
            $excerpt = get_the_excerpt( $id );
            // about 310 characters
            $excerpt = substr( $excerpt, 0, 160 );
            // truncate to 160 characters
            $short_excerpt = substr( $excerpt, 0, strrpos( $excerpt, ' ' ) );
            echo wp_kses_post( $short_excerpt );
        }
    }

    public function show_id_column() {
        // For pages and hierarchical post types list table
        add_filter( 'manage_pages_columns', array($this, 'add_id_column') );
        add_action(
            'manage_pages_custom_column',
            array($this, 'add_id_echo_value'),
            10,
            2
        );
        // For posts and non-hierarchical custom posts list table
        add_filter( 'manage_posts_columns', array($this, 'add_id_column') );
        add_action(
            'manage_posts_custom_column',
            array($this, 'add_id_echo_value'),
            10,
            2
        );
        // For media list table
        add_filter( 'manage_media_columns', array($this, 'add_id_column') );
        add_action(
            'manage_media_custom_column',
            array($this, 'add_id_echo_value'),
            10,
            2
        );
        // For list table of all taxonomies
        $taxonomies = get_taxonomies( array(
            'public' => true,
        ), 'names' );
        foreach ( $taxonomies as $taxonomy ) {
            add_filter( 'manage_edit-' . $taxonomy . '_columns', array($this, 'add_id_column') );
            add_action(
                'manage_' . $taxonomy . '_custom_column',
                array($this, 'add_id_return_value'),
                10,
                3
            );
        }
        // For users list table
        add_filter( 'manage_users_columns', array($this, 'add_id_column') );
        add_action(
            'manage_users_custom_column',
            array($this, 'add_id_return_value'),
            10,
            3
        );
        // For comments list table
        add_filter( 'manage_edit-comments_columns', array($this, 'add_id_column') );
        add_action(
            'manage_comments_custom_column',
            array($this, 'add_id_echo_value'),
            10,
            3
        );
    }

    public function add_id_column( $columns ) {
        $columns['wpuk-id'] = 'ID';
        return $columns;
    }

    public function add_id_echo_value( $column_name, $id ) {
        if ( 'wpuk-id' === $column_name ) {
            echo esc_html( $id );
        }
    }

    public function show_id_in_action_row() {
        add_filter(
            'page_row_actions',
            array($this, 'add_id_in_action_row'),
            10,
            2
        );
        add_filter(
            'post_row_actions',
            array($this, 'add_id_in_action_row'),
            10,
            2
        );
        add_filter(
            'cat_row_actions',
            array($this, 'add_id_in_action_row'),
            10,
            2
        );
        add_filter(
            'tag_row_actions',
            array($this, 'add_id_in_action_row'),
            10,
            2
        );
        add_filter(
            'media_row_actions',
            array($this, 'add_id_in_action_row'),
            10,
            2
        );
        add_filter(
            'comment_row_actions',
            array($this, 'add_id_in_action_row'),
            10,
            2
        );
        add_filter(
            'user_row_actions',
            array($this, 'add_id_in_action_row'),
            10,
            2
        );
    }

    public function add_id_in_action_row( $actions, $object ) {
        if ( current_user_can( 'edit_posts' ) ) {
            // For pages, posts, custom post types, media/attachments, users
            if ( property_exists( $object, 'ID' ) ) {
                $id = $object->ID;
            }
            // For taxonomies
            if ( property_exists( $object, 'term_id' ) ) {
                $id = $object->term_id;
            }
            // For comments
            if ( property_exists( $object, 'comment_ID' ) ) {
                $id = $object->comment_ID;
            }
            $actions['wpuk-list-table-item-id'] = '<span class="wpuk-list-table-item-id">ID: ' . $id . '</span>';
        }
        return $actions;
    }

    public function show_custom_taxonomy_filters( $post_type ) {
        $post_taxonomies = get_object_taxonomies( $post_type, 'objects' );
        // Only show custom taxonomy filters for post types other than 'post'
        if ( 'post' != $post_type ) {
            array_walk( $post_taxonomies, array($this, 'output_taxonomy_filter') );
        }
    }

    public function output_taxonomy_filter( $post_taxonomy ) {
        // Only show taxonomy filter when the taxonomy is hierarchical
        if ( true === $post_taxonomy->hierarchical ) {
            $get = ( isset( $_GET[$post_taxonomy->query_var] ) ? $_GET[$post_taxonomy->query_var] : '' );
            wp_dropdown_categories( array(
                'show_option_all' => sprintf( 'All %s', $post_taxonomy->label ),
                'orderby'         => 'name',
                'order'           => 'ASC',
                'hide_empty'      => false,
                'hide_if_empty'   => true,
                'selected'        => sanitize_text_field( $get ),
                'hierarchical'    => true,
                'name'            => $post_taxonomy->query_var,
                'taxonomy'        => $post_taxonomy->name,
                'value_field'     => 'slug',
            ) );
        }
    }

    public function hide_comments_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( post_type_supports( $post_type_key, 'comments' ) ) {
                if ( 'attachment' != $post_type_name ) {
                    // For list tables of pages, posts and other post types
                    add_filter( "manage_{$post_type_name}_posts_columns", array($this, 'remove_comment_column') );
                } else {
                    // For list table of media/attachment
                    add_filter( 'manage_media_columns', array($this, 'remove_comment_column') );
                }
            }
        }
    }

    public function remove_comment_column( $columns ) {
        unset($columns['comments']);
        return $columns;
    }

    public function hide_post_tags_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( $post_type_name == 'post' ) {
                add_filter( 'manage_posts_columns', array($this, 'remove_post_tags_column') );
            }
        }
    }

    public function remove_post_tags_column( $columns ) {
        unset($columns['tags']);
        return $columns;
    }

    public function is_table_exists( $table_name ) {
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
        if ( $wpdb->get_var( $query ) == $table_name ) {
            return true;
        }
        return false;
    }

    public function ultimakit_get_the_user_ip() {
        $ipaddress = '';
        if ( getenv( 'HTTP_CLIENT_IP' ) ) {
            $ipaddress = getenv( 'HTTP_CLIENT_IP' );
        } elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
            $ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
        } elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
            $ipaddress = getenv( 'HTTP_X_FORWARDED' );
        } elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
            $ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
        } elseif ( getenv( 'HTTP_FORWARDED' ) ) {
            $ipaddress = getenv( 'HTTP_FORWARDED' );
        } elseif ( getenv( 'REMOTE_ADDR' ) ) {
            $ipaddress = getenv( 'REMOTE_ADDR' );
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public function get_all_user_roles() {
        global $wp_roles;
        $roles = array();
        if ( !isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }
        foreach ( $wp_roles->roles as $role => $details ) {
            $roles[$role] = $details['name'];
        }
        return $roles;
    }

    public function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    public function get_module_block( $all_modules = array() ) {
        if ( !empty( $all_modules ) ) {
            foreach ( $all_modules as $module ) {
                ?>
				<div class="module-block <?php 
                echo esc_attr( $this->add_non_paying_classes( $module['plan'] ) );
                ?> <?php 
                echo esc_attr( $module['category'] );
                ?> <?php 
                echo esc_attr( $module['type'] );
                ?> <?php 
                echo ( true === $module['is_active'] ? 'active' : 'inactive' );
                ?> <?php 
                echo esc_attr( $module['plan'] );
                ?>-plan">
					<!-- Module Title -->
					<h5 class="module-title"><?php 
                echo esc_html( $module['name'] );
                ?></h5>

					<!-- Module Description -->
					<p class="module-description"><?php 
                echo esc_html( $module['description'] );
                ?></p>

					<!-- Module Switch -->
					<div class="form-check form-switch module-switch">
						<input type="checkbox" class="form-check-input ultimakit_module_action" module-name="<?php 
                echo esc_attr( $module['name'] );
                ?>" id="<?php 
                echo esc_attr( $module['id'] );
                ?>" 
																														<?php 
                if ( true === $module['is_active'] ) {
                    echo 'checked';
                }
                ?>
						>
						<label class="form-check-label switch-label" for="<?php 
                echo esc_attr( $module['id'] );
                ?>">Toggle me</label>
					</div>

					<!-- Settings Link -->
					<?php 
                if ( isset( $module['settings'] ) && 'yes' == $module['settings'] ) {
                    ?>
							<a href="javascript:void()" class="
							<?php 
                    if ( !$module['is_active'] ) {
                        echo 'ultimakit_hide_settings ';
                    }
                    ?>
							learn-more-link <?php 
                    echo esc_attr( $module['id'] );
                    ?>"><?php 
                    echo esc_html( __( 'Settings', 'ultimakit-for-wp' ) );
                    ?></a>
						<?php 
                }
                ?>

					<!-- Module Badges -->
					<span class="plugin-badge"><?php 
                echo esc_html( $module['type'] );
                ?></span>
				</div>
				<?php 
            }
        } else {
            return array();
        }
    }

    public function add_non_paying_classes( $plan = 'pro' ) {
        $classes = '';
        if ( 'pro' === $plan ) {
            $classes .= 'pro-module-opacity not_paying';
        }
        return $classes;
    }

    public function woo_activation_check() {
        return is_plugin_active( 'woocommerce/woocommerce.php' );
    }

    /**
     * Function to check migration status, create custom table, and migrate settings if needed.
     */
    public function ultimakit_check_and_migrate_settings() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // Check if migration has already been performed
        $migration_completed = get_option( 'ultimakit_migration_completed' );
        if ( $migration_completed ) {
            return;
        }
        // Create the custom table if it does not exist
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (\n\t\t\t\tid BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n\t\t\t\tmodule_name VARCHAR(255) NOT NULL,\n\t\t\t\tsetting_key VARCHAR(255) NOT NULL,\n\t\t\t\tsetting_value LONGTEXT,\n\t\t\t\tautoload BOOLEAN DEFAULT FALSE,\n\t\t\t\tUNIQUE KEY module_setting (module_name, setting_key)\n\t\t\t) {$charset_collate};";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
        // Retrieve the old serialized settings option
        $old_settings = get_option( 'ultimakit_options' );
        if ( $old_settings && is_array( $old_settings ) ) {
            // Loop through each module's settings and insert into the custom table
            foreach ( $old_settings as $module_name => $settings ) {
                foreach ( $settings as $setting_key => $setting_value ) {
                    // Insert each setting into the new custom settings table
                    $autoload = $setting_key === 'enabled';
                    // Set autoload true for "enabled" settings only
                    $this->ultimakit_update_module_setting(
                        $module_name,
                        $setting_key,
                        $setting_value,
                        $autoload
                    );
                }
            }
        }
        // Optionally delete the old serialized option to clean up the database
        delete_option( 'ultimakit_options' );
        // Mark migration as complete to prevent reruns
        return update_option( 'ultimakit_migration_completed', true );
    }

    /**
     * Helper function to update module settings in the custom table.
     */
    public function ultimakit_update_module_setting(
        $module_name,
        $setting_key,
        $setting_value,
        $autoload = false
    ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // Check if the setting already exists
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE module_name = %s AND setting_key = %s", $module_name, $setting_key ) );
        if ( $exists ) {
            // Update existing setting
            return $wpdb->update( $table_name, array(
                'setting_value' => maybe_serialize( $setting_value ),
                'autoload'      => $autoload,
            ), array(
                'module_name' => $module_name,
                'setting_key' => $setting_key,
            ) );
        } else {
            // Insert new setting
            return $wpdb->insert( $table_name, array(
                'module_name'   => $module_name,
                'setting_key'   => $setting_key,
                'setting_value' => maybe_serialize( $setting_value ),
                'autoload'      => $autoload,
            ) );
        }
    }

    public function get_modules_count( $admin ) {
        return count( $admin->getAllModules() );
    }

    public function get_modules_count_by_category( $admin, $category, $types = array() ) {
        return count( $admin->getAllModulesByCategory( $category, $types ) );
    }

    /**
     * Get countries array values.
     *
     * @return [type] [description]
     */
    public function get_countries() {
        $countries = array(
            'AF' => 'Afghanistan (‫افغانستان‬‎)',
            'AX' => 'Åland Islands (Åland)',
            'AL' => 'Albania (Shqipëri)',
            'DZ' => 'Algeria (‫الجزائر‬‎)',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia (Հայաստան)',
            'AW' => 'Aruba',
            'AC' => 'Ascension Island',
            'AU' => 'Australia',
            'AT' => 'Austria (Österreich)',
            'AZ' => 'Azerbaijan (Azərbaycan)',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain (‫البحرين‬‎)',
            'BD' => 'Bangladesh (বাংলাদেশ)',
            'BB' => 'Barbados',
            'BY' => 'Belarus (Беларусь)',
            'BE' => 'Belgium (België)',
            'BZ' => 'Belize',
            'BJ' => 'Benin (Bénin)',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan (འབྲུག)',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina (Босна и Херцеговина)',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil (Brasil)',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria (България)',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi (Uburundi)',
            'KH' => 'Cambodia (កម្ពុជា)',
            'CM' => 'Cameroon (Cameroun)',
            'CA' => 'Canada',
            'IC' => 'Canary Islands (islas Canarias)',
            'CV' => 'Cape Verde (Kabu Verdi)',
            'BQ' => 'Caribbean Netherlands',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic (République centrafricaine)',
            'EA' => 'Ceuta and Melilla (Ceuta y Melilla)',
            'TD' => 'Chad (Tchad)',
            'CL' => 'Chile',
            'CN' => 'China (中国)',
            'CX' => 'Christmas Island',
            'CP' => 'Clipperton Island',
            'CC' => 'Cocos (Keeling) Islands (Kepulauan Cocos (Keeling))',
            'CO' => 'Colombia',
            'KM' => 'Comoros (‫جزر القمر‬‎)',
            'CD' => 'Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)',
            'CG' => 'Congo (Republic) (Congo-Brazzaville)',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Côte d\'Ivoire',
            'HR' => 'Croatia (Hrvatska)',
            'CU' => 'Cuba',
            'CW' => 'Curaçao',
            'CY' => 'Cyprus (Κύπρος)',
            'CZ' => 'Czech Republic (Česká republika)',
            'DK' => 'Denmark (Danmark)',
            'DG' => 'Diego Garcia',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic (República Dominicana)',
            'EC' => 'Ecuador',
            'EG' => 'Egypt (‫مصر‬‎)',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea (Guinea Ecuatorial)',
            'ER' => 'Eritrea',
            'EE' => 'Estonia (Eesti)',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Islas Malvinas)',
            'FO' => 'Faroe Islands (Føroyar)',
            'FJ' => 'Fiji',
            'FI' => 'Finland (Suomi)',
            'FR' => 'France',
            'GF' => 'French Guiana (Guyane française)',
            'PF' => 'French Polynesia (Polynésie française)',
            'TF' => 'French Southern Territories (Terres australes françaises)',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia (საქართველო)',
            'DE' => 'Germany (Deutschland)',
            'GH' => 'Ghana (Gaana)',
            'GI' => 'Gibraltar',
            'GR' => 'Greece (Ελλάδα)',
            'GL' => 'Greenland (Kalaallit Nunaat)',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea (Guinée)',
            'GW' => 'Guinea-Bissau (Guiné Bissau)',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard & McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong (香港)',
            'HU' => 'Hungary (Magyarország)',
            'IS' => 'Iceland (Ísland)',
            'IN' => 'India (भारत)',
            'ID' => 'Indonesia',
            'IR' => 'Iran (‫ایران‬‎)',
            'IQ' => 'Iraq (‫العراق‬‎)',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel (‫תירבע‬‎)',
            'IT' => 'Italy (Italia)',
            'JM' => 'Jamaica',
            'JP' => 'Japan (日本)',
            'JE' => 'Jersey',
            'JO' => 'Jordan (‫الأردن‬‎)',
            'KZ' => 'Kazakhstan (Казахстан)',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'XK' => 'Kosovo (Kosovë)',
            'KW' => 'Kuwait (‫الكويت‬‎)',
            'KG' => 'Kyrgyzstan (Кыргызстан)',
            'LA' => 'Laos (ລາວ)',
            'LV' => 'Latvia (Latvija)',
            'LB' => 'Lebanon (‫لبنان‬‎)',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya (‫ليبيا‬‎)',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania (Lietuva)',
            'LU' => 'Luxembourg',
            'MO' => 'Macau (澳門)',
            'MK' => 'Macedonia (FYROM) (Македонија)',
            'MG' => 'Madagascar (Madagasikara)',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania (‫موريتانيا‬‎)',
            'MU' => 'Mauritius (Moris)',
            'YT' => 'Mayotte',
            'MX' => 'Mexico (México)',
            'FM' => 'Micronesia',
            'MD' => 'Moldova (Republica Moldova)',
            'MC' => 'Monaco',
            'MN' => 'Mongolia (Монгол)',
            'ME' => 'Montenegro (Crna Gora)',
            'MS' => 'Montserrat',
            'MA' => 'Morocco (‫المغرب‬‎)',
            'MZ' => 'Mozambique (Moçambique)',
            'MM' => 'Myanmar (Burma) (မြန်မာ)',
            'NA' => 'Namibia (Namibië)',
            'NR' => 'Nauru',
            'NP' => 'Nepal (नेपाल)',
            'NL' => 'Netherlands (Nederland)',
            'NC' => 'New Caledonia (Nouvelle-Calédonie)',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger (Nijar)',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'KP' => 'North Korea (조선 민주주의 인민 공화국)',
            'NO' => 'Norway (Norge)',
            'OM' => 'Oman (‫عُمان‬‎)',
            'PK' => 'Pakistan (‫پاکستان‬‎)',
            'PW' => 'Palau',
            'PS' => 'Palestine (‫فلسطين‬‎)',
            'PA' => 'Panama (Panamá)',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru (Perú)',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland (Polska)',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar (‫قطر‬‎)',
            'RE' => 'Réunion (La Réunion)',
            'RO' => 'Romania (România)',
            'RU' => 'Russia (Россия)',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthélemy (Saint-Barthélemy)',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin (Saint-Martin (partie française))',
            'PM' => 'Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'São Tomé and Príncipe (São Tomé e Príncipe)',
            'SA' => 'Saudi Arabia (‫المملكة العربية السعودية‬‎)',
            'SN' => 'Senegal (Sénégal)',
            'RS' => 'Serbia (Србија)',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SX' => 'Sint Maarten',
            'SK' => 'Slovakia (Slovensko)',
            'SI' => 'Slovenia (Slovenija)',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia (Soomaaliya)',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia & South Sandwich Islands',
            'KR' => 'South Korea (대한민국)',
            'SS' => 'South Sudan (‫جنوب السودان‬‎)',
            'ES' => 'Spain (España)',
            'LK' => 'Sri Lanka (ශ්‍රී ලංකාව)',
            'VC' => 'St. Vincent & Grenadines',
            'SD' => 'Sudan (‫السودان‬‎)',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen (Svalbard og Jan Mayen)',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden (Sverige)',
            'CH' => 'Switzerland (Schweiz)',
            'SY' => 'Syria (‫سوريا‬‎)',
            'TW' => 'Taiwan (台灣)',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand (ไทย)',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TA' => 'Tristan da Cunha',
            'TN' => 'Tunisia (‫تونس‬‎)',
            'TR' => 'Turkey (Türkiye)',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UM' => 'U.S. Outlying Islands',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine (Україна)',
            'AE' => 'United Arab Emirates (‫الإمارات العربية المتحدة‬‎)',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan (Oʻzbekiston)',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City (Città del Vaticano)',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam (Việt Nam)',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara (‫الصحراء الغربية‬‎)',
            'YE' => 'Yemen (‫اليمن‬‎)',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );
        return $countries;
    }

    public function get_ultimakit_addons_data() {
        $api_url = ULTIMAKIT_API;
        $response = wp_remote_get( $api_url );
        if ( is_wp_error( $response ) ) {
            return false;
        }
        $body = wp_remote_retrieve_body( $response );
        return json_decode( $body, true );
    }

    public function display_main_banner( $banner ) {
        if ( !$banner ) {
            return;
        }
        ?>
		<div class="container my-2">
			<!-- Hero Section -->
			<div class="text-center py-5" style="background: linear-gradient(135deg, #6a11cb, #2575fc); color: white; border-radius: 15px;">
				<h1 class="display-4 fw-bold"><?php 
        echo esc_html( $banner['heading'] );
        ?></h1>
				<p class="lead"><?php 
        echo esc_html( $banner['subheading'] );
        ?></p>
				<a href="<?php 
        echo esc_url( $banner['cta']['link'] );
        ?>" class="btn btn-light btn-lg shadow-sm"><?php 
        echo esc_html( $banner['cta']['label'] );
        ?></a>
			</div>
		</div>
		<?php 
    }

    public function display_promotional_banner( $promo ) {
        if ( !$promo || empty( $promo ) ) {
            return;
        }
        // Add promotional banner HTML when API includes the data
        if ( !$promo['active'] ) {
            return;
        }
        ?>
		<!-- Promotional Banner HTML -->
		<div class="ultimakit-promo-banner">
			<div class="promo-content">
				<div class="promo-badge"><?php 
        echo esc_html( $promo['badge_text'] );
        ?></div>
				<div class="promo-main">
					<h3 class="promo-heading"><?php 
        echo esc_html( $promo['heading'] );
        ?></h3>
					<p class="promo-description"><?php 
        echo esc_html( $promo['description'] );
        ?></p>
				</div>
				<div class="promo-offer">
					<a href="https://xwpstack.com/pricing" target="_blank"><span class="discount-text"><?php 
        echo esc_html( $promo['discount_text'] );
        ?></span></a>
					<div class="promo-code-container">
						<span class="promo-code"><?php 
        echo esc_html( $promo['promo_code'] );
        ?></span>
						<button class="copy-code" onclick="copyPromoCode()">Copy</button>
					</div>
					<div class="valid-until"><?php 
        echo esc_html( $promo['valid_until'] );
        ?></div>
				</div>
			</div>
		</div>
		<script>
			function copyPromoCode() {
				const promoCode = document.querySelector('.promo-code').textContent;
				const button = document.querySelector('.copy-code');

				// Function to update button text with visual feedback
				const updateButtonText = (success = true) => {
					const originalText = button.textContent;
					button.textContent = success ? 'Copied! ✓' : 'Failed! ✗';
					button.style.backgroundColor = success ? '#4CAF50' : '#f44336';
					button.style.color = 'white';

					setTimeout(() => {
						button.textContent = originalText;
						button.style.backgroundColor = '';
						button.style.color = '';
					}, 2000);
				};

				// Try modern clipboard API first
				if (navigator.clipboard && window.isSecureContext) {
					navigator.clipboard.writeText(promoCode)
						.then(() => {
							updateButtonText(true);
						})
						.catch((err) => {
							console.error('Failed to copy: ', err);
							fallbackCopyText(promoCode);
						});
				} else {
					// Fallback for older browsers
					fallbackCopyText(promoCode);
				}

				// Fallback copy method
				function fallbackCopyText(text) {
					try {
						const textArea = document.createElement('textarea');
						textArea.value = text;
						textArea.style.position = 'fixed';
						textArea.style.left = '-9999px';
						document.body.appendChild(textArea);
						textArea.focus();
						textArea.select();

						try {
							document.execCommand('copy');
							textArea.remove();
							updateButtonText(true);
						} catch (err) {
							console.error('Fallback: Oops, unable to copy', err);
							updateButtonText(false);
						}
					} catch (err) {
						console.error('Fallback: Oops, unable to copy', err);
						updateButtonText(false);
					}
				}
			}
		</script>
		<?php 
    }

    public function format_price( $price ) {
        return '$' . number_format(
            (float) $price,
            2,
            '.',
            ','
        );
    }

    public function display_addon_card( $addon ) {
        $has_sale = !empty( $addon['price']['sale'] ) && $addon['price']['sale'] < $addon['price']['regular'];
        ?>
		<!-- Card block started -->
		<div class="addon-card <?php 
        echo esc_attr( ( $addon['featured'] ? 'featured' : '' ) );
        ?>">
			<div class="addon-thumbnail">
				<?php 
        if ( !empty( $addon['icon'] ) ) {
            ?>
					<img src="<?php 
            echo esc_url( $addon['icon'] );
            ?>" alt="<?php 
            echo esc_attr( $addon['title'] );
            ?>">
				<?php 
        } else {
            ?>
					<img src="https://placehold.co/400x200" alt="Add-on thumbnail">
				<?php 
        }
        ?>
				
				<?php 
        if ( !empty( $addon['price']['sale'] ) && $addon['price']['sale'] < $addon['price']['regular'] ) {
            ?>
					<span class="addon-badge"><?php 
            echo esc_html_e( 'SALE', 'ultimaki-for-wp' );
            ?></span>
				<?php 
        }
        ?>
			</div>
			
			<div class="addon-content">
				<div class="addon-header">
					<div class="addon-title-wrapper">
						<h3 class="addon-title"><?php 
        echo esc_html( $addon['title'] );
        ?></h3>
						<div class="addon-category">
							<?php 
        if ( !empty( $addon['categories'] ) ) {
            echo esc_html( implode( ', ', array_column( $addon['categories'], 'name' ) ) );
        }
        ?>
						</div>
					</div>
					<div class="addon-pricing">
						<?php 
        if ( !empty( $addon['price']['sale'] ) && $addon['price']['sale'] < $addon['price']['regular'] ) {
            ?>
							<span class="regular-price">$<?php 
            echo esc_html( $addon['price']['regular'] );
            ?></span>
							<span class="sale-price">$<?php 
            echo esc_html( $addon['price']['sale'] );
            ?></span>
						<?php 
        } else {
            ?>
							<span class="sale-price">$<?php 
            echo esc_html( $addon['price']['regular'] );
            ?></span>
						<?php 
        }
        ?>
					</div>
				</div>

				<div class="addon-description">
					<?php 
        echo wp_kses_post( $addon['short_description'] );
        ?>
				</div>

				<div class="addon-meta">
					<span>
						<i class="fas fa-code"></i>
						<?php 
        echo esc_html_e( 'Version', 'ultimaki-for-wp' );
        ?> <strong><?php 
        echo esc_html( $addon['version'] );
        ?></strong>
					</span>
					<span>
						<i class="fas fa-wordpress"></i>
						<?php 
        echo esc_html_e( 'WordPress', 'ultimaki-for-wp' );
        ?> <strong><?php 
        echo esc_html( $addon['compatibility']['wordpress'] );
        ?></strong>
					</span>
					<span>
						<i class="fas fa-plug"></i>
						<?php 
        echo esc_html_e( 'UltimaKit', 'ultimaki-for-wp' );
        ?> <strong><?php 
        echo esc_html( $addon['compatibility']['ultimakit'] );
        ?></strong>
					</span>
				</div>

				<?php 
        if ( !empty( $addon['features'] ) ) {
            ?>
					<div class="addon-features">
						<?php 
            foreach ( $addon['features'] as $feature ) {
                ?>
							<span class="feature-tag"><?php 
                echo esc_html( trim( $feature ) );
                ?></span>
						<?php 
            }
            ?>
					</div>
				<?php 
        }
        ?>

				<div class="addon-footer">
					<?php 
        if ( !empty( $addon['urls']['video'] ) ) {
            ?>
						<a target="_blank" href="<?php 
            echo esc_url( $addon['urls']['video'] );
            ?>" class="btn btn-primary"><?php 
            echo esc_html_e( 'Buy Now', 'ultimaki-for-wp' );
            ?></a>
					<?php 
        }
        ?>
					<?php 
        if ( !empty( $addon['urls']['demo'] ) ) {
            ?>
						<a target="_blank" href="<?php 
            echo esc_url( $addon['urls']['demo'] );
            ?>" class="btn btn-outline"><?php 
            echo esc_html_e( 'Learn more', 'ultimaki-for-wp' );
            ?></a>
					<?php 
        }
        ?>
				</div>
			</div>
		</div>
		<!-- Card block ended -->
		<?php 
    }

    /**
     * Check if table exists
     *
     * @return bool
     */
    public function table_exists( $transient_key, $table_name ) {
        global $wpdb;
        $table_exists = get_transient( $transient_key );
        if ( $table_exists === false ) {
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== null;
            if ( $table_exists ) {
                set_transient( $transient_key, true, DAY_IN_SECONDS );
            }
        }
        return (bool) $table_exists;
    }

    public function string_to_slug( $string ) {
        // Convert string to lowercase
        $string = strtolower( $string );
        // Remove special characters and replace with spaces
        $string = preg_replace( '/[^a-z0-9\\s-]/', '', $string );
        // Replace multiple spaces and hyphens with a single underscore
        $string = preg_replace( '/[\\s-]+/', '_', $string );
        // Remove underscores from the beginning and end
        $string = trim( $string, '_' );
        return $string;
    }

}
