<?php

class cfgroup_init
{

    function __construct() {
        add_action( 'init', [ $this, 'init' ] );
    }


    function init() {

        add_action( 'admin_head',                       [ $this, 'admin_head' ] );
        add_action( 'admin_enqueue_scripts',            [ $this, 'admin_enqueue_scripts' ] );
        add_action( 'admin_init',                       [ $this, 'add_cfgroups_to_taxonomy_terms' ] );
        // add_action( 'admin_menu',                       [ $this, 'admin_menu' ] ); // CFG Tools
        add_action( 'save_post',                        [ $this, 'save_post' ] );
        add_action( 'delete_post',                      [ $this, 'delete_post' ] );
        add_action( 'add_meta_boxes',                   [ $this, 'add_meta_boxes' ] );
        add_action( 'wp_ajax_cfgroup_ajax_handler',         [ $this, 'ajax_handler' ] );
        add_filter( 'manage_asenha_cfgroup_posts_columns',         [ $this, 'cfgroup_columns' ] );
        add_action( 'manage_asenha_cfgroup_posts_custom_column',   [ $this, 'cfgroup_column_content' ], 10, 2 );
        add_filter( 'image_add_caption_shortcode', [ $this, 'render_image_caption_shortcode' ], 10, 2 );

        include( CFG_DIR . '/includes/api.php' );
        include( CFG_DIR . '/includes/upgrade.php' );
        include( CFG_DIR . '/includes/field.php' );
        include( CFG_DIR . '/includes/field_group.php' );
        include( CFG_DIR . '/includes/session.php' );
        include( CFG_DIR . '/includes/form.php' );
        include( CFG_DIR . '/includes/third_party.php' );
        include( CFG_DIR . '/includes/revision.php' );


        $this->register_post_type();
        CFG()->fields = $this->get_field_types();

        // CFG is ready
        do_action( 'cfgroup_init' );
    }


    /**
     * Register the field group post type
     */
    function register_post_type() {
        register_post_type( 'asenha_cfgroup', [
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => 'options-general.php',
            'capability_type'   => 'page',
            'hierarchical'      => false,
            'supports'          => [ 'title' ],
            'query_var'         => false,
            'labels'            => [
                'name'                  => __( 'Custom Field Groups', 'admin-site-enhancements' ),
                'singular_name'         => __( 'Custom Field Group', 'admin-site-enhancements' ),
                'all_items'             => __( 'Custom Field Groups', 'admin-site-enhancements' ),
                'add_new_item'          => __( 'Add New Field Group', 'admin-site-enhancements' ),
                'edit_item'             => __( 'Edit Field Group', 'admin-site-enhancements' ),
                'new_item'              => __( 'New Field Group', 'admin-site-enhancements' ),
                'view_item'             => __( 'View Field Group', 'admin-site-enhancements' ),
                'search_items'          => __( 'Search Field Groups', 'admin-site-enhancements' ),
                'not_found'             => __( 'No Field Groups found', 'admin-site-enhancements' ),
                'not_found_in_trash'    => __( 'No Field Groups found in Trash', 'admin-site-enhancements' ),
            ],
        ] );
    }


    /**
     * Register field types
     */
    function get_field_types() {

        // support custom field types
        $field_types = apply_filters( 'cfgroup_field_types', [
            // Content
            'text'          => CFG_DIR . '/includes/fields/text.php',
            'textarea'      => CFG_DIR . '/includes/fields/textarea.php',
            'wysiwyg'       => CFG_DIR . '/includes/fields/wysiwyg.php',
            'file'          => CFG_DIR . '/includes/fields/file.php',
            'gallery'       => CFG_DIR . '/includes/fields/gallery.php',
            // Choice
            'true_false'    => CFG_DIR . '/includes/fields/true_false.php',
            'radio'         => CFG_DIR . '/includes/fields/radio.php',
            'select'        => CFG_DIR . '/includes/fields/select.php',
            'checkbox'      => CFG_DIR . '/includes/fields/checkbox.php',
            // Extra
            'hyperlink'     => CFG_DIR . '/includes/fields/hyperlink.php',
            'number'        => CFG_DIR . '/includes/fields/number.php',
            'date'          => CFG_DIR . '/includes/fields/date.php',
            'time'          => CFG_DIR . '/includes/fields/time.php',
            'datetime'      => CFG_DIR . '/includes/fields/datetime.php',
            'color'         => CFG_DIR . '/includes/fields/color.php',
            // Relationship
            'relationship'  => CFG_DIR . '/includes/fields/relationship.php',
            'term'          => CFG_DIR . '/includes/fields/term.php',
            'user'          => CFG_DIR . '/includes/fields/user.php',
            // Special
            'repeater'      => CFG_DIR . '/includes/fields/repeater.php',
            // Layout
            'tab'           => CFG_DIR . '/includes/fields/tab.php',
            'heading'       => CFG_DIR . '/includes/fields/heading.php',
            'line_break'    => CFG_DIR . '/includes/fields/line_break.php',
        ] );

        foreach ( $field_types as $type => $path ) {
            $class_name = 'cfgroup_' . $type;

            // allow for multiple classes per file
            if ( ! class_exists( $class_name ) ) {
                include_once( $path );
            }

            $field_types[ $type ] = new $class_name();
        }

        return $field_types;
    }


    /**
     * admin_head
     */
    function admin_head() {
        $screen = get_current_screen();

        // if ( is_object( $screen ) && 'post' == $screen->base ) {
        if ( is_object( $screen ) ) {
            include( CFG_DIR . '/templates/admin_head.php' );
        }
    }

    /**
     * Enqueue admin assets early for specific field UIs.
     *
     * This is primarily needed for taxonomy term screens where enqueuing styles/scripts
     * during field rendering can be too late (after wp_head), causing the core wpLink
     * modal to appear unstyled.
     *
     * @since 7.8.11
     *
     * @return void
     */
    function admin_enqueue_scripts() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        if ( ! is_object( $screen ) ) {
            return;
        }

        // Never load on the Field Group editor itself (not needed).
        if ( 'asenha_cfgroup' === $screen->post_type ) {
            return;
        }

        $group_ids = array();

        // Post edit screens (incl. options pages that render with a post context).
        if ( 'post' === $screen->base ) {
            $post_id = 0;

            if ( isset( $_GET['post'] ) ) {
                $post_id = absint( $_GET['post'] );
            } elseif ( isset( $GLOBALS['post'] ) && is_object( $GLOBALS['post'] ) && isset( $GLOBALS['post']->ID ) ) {
                $post_id = absint( $GLOBALS['post']->ID );
            }

            if ( 0 < $post_id ) {
                $matched = CFG()->api->get_matching_groups( $post_id, true );
                $group_ids = array_keys( $matched );
            }
        }

        // Taxonomy term add/edit screens.
        if ( in_array( $screen->base, array( 'edit-tags', 'term' ), true ) ) {
            $taxonomy = '';

            if ( ! empty( $screen->taxonomy ) ) {
                $taxonomy = $screen->taxonomy;
            } elseif ( isset( $_GET['taxonomy'] ) ) {
                $taxonomy = sanitize_key( $_GET['taxonomy'] );
            }

            if ( ! empty( $taxonomy ) ) {
                $group_ids = CFG()->api->get_matching_groups_for_taxonomy( $taxonomy );
            }
        }

        if ( empty( $group_ids ) ) {
            return;
        }

        if ( ! $this->has_hyperlink_wplink_fields( $group_ids ) ) {
            return;
        }

        // Ensure the wpLink modal and its dependencies are available.
        if ( function_exists( 'wp_enqueue_editor' ) ) {
            wp_enqueue_editor();
        }

        // Ensure our bridge script is available early enough.
        wp_enqueue_script(
            'asenha-cfgroup-hyperlink-wplink',
            CFG_URL . '/assets/js/hyperlink-wplink.js',
            array( 'jquery', 'wplink' ),
            CFG_VERSION,
            true
        );
    }

    /**
     * Check whether any Hyperlink fields in the given groups are configured to use wpLink UI.
     *
     * @since 7.8.11
     *
     * @param array $group_ids Field group IDs.
     * @return bool
     */
    private function has_hyperlink_wplink_fields( $group_ids ) {
        $group_ids = array_filter( array_map( 'absint', (array) $group_ids ) );

        if ( empty( $group_ids ) ) {
            return false;
        }

        $fields = CFG()->api->find_input_fields( array( 'group_id' => $group_ids ) );

        foreach ( $fields as $field ) {
            if ( isset( $field['type'] ) && 'hyperlink' === $field['type'] ) {
                if ( isset( $field['options']['ui'] ) && 'wp_link' === $field['options']['ui'] ) {
                    return true;
                }
            }
        }

        return false;
    }
    
    /**
     * Output CFG on taxonomy term screens
     * 
     * @link https://rudrastyh.com/wordpress/add-custom-fields-to-taxonomy-terms.html
     * @since 7.8.10
     */
    function add_cfgroups_to_taxonomy_terms() {
        $cfgroup_ids_for_taxonomy_terms = get_cfgroup_ids_by_placement( 'taxonomy-terms' ); // array of IDs
        
        foreach ( $cfgroup_ids_for_taxonomy_terms as $cfgroup_id ) {
            $rules = get_post_meta( $cfgroup_id, 'cfgroup_rules', true );
            $cfgroup_taxonomies = $rules['taxonomies']['values'];
            
            switch ( $rules['taxonomies']['operator'] ) {
                case '==':
                    foreach ( $cfgroup_taxonomies as $taxonomy ) {
                        add_action( $taxonomy . '_add_form_fields', [ $this, 'add_term_fields' ] );
                        add_action( $taxonomy . '_edit_form_fields', [ $this, 'edit_term_fields' ], 20, 2 );
                    }                
                    break;
                    
                case '!=':
                    $args = array(
                        'public'        => true,
                        'show_ui'       => true,
                    );

                    $applicable_taxonomies = get_taxonomies( $args, 'names' );

                    foreach ( $applicable_taxonomies as $applicable_taxonomy ) {
                        if ( ! in_array( $applicable_taxonomy, $cfgroup_taxonomies ) ) {
                            add_action( $applicable_taxonomy . '_add_form_fields', [ $this, 'add_term_fields' ] );
                            add_action( $applicable_taxonomy . '_edit_form_fields', [ $this, 'edit_term_fields' ], 20, 2 );
                        }
                    }
                    break;
            }
        }
    }

    function add_term_fields( $taxonomy ) {
        ?>
        <h3 class="form-field-cfg-title"><?php echo esc_html__( 'Additional Info', 'admin-site-enhancements' ); ?></h3>
        <div class="form-field form-field-cfg tax-term-add">
        <?php
            $matching_cfgroup_ids = CFG()->api->get_matching_groups_for_taxonomy( $taxonomy );
            // vi( $matching_cfgroup_ids, '', 'add term' );
        
            $args = array (
                'box'       => 'term',
                'group_id'  => $matching_cfgroup_ids,
            );
            
            $this->term_meta_box( false, $args );
        ?>
        </div>
        <?php
    }

    function edit_term_fields( $term, $taxonomy ) {
        // get meta data value
        $text_field = get_term_meta( $term->term_id, 'rudr_text', true );
        $image_id = get_term_meta( $term->term_id, 'rudr_img', true );
        ?>
        <tr class="form-field form-field-cfg tax-term-edit">
            <th><label for="rudr_text"><?php echo esc_html__( 'Additional Info', 'admin-site-enhancements' ); ?></label></th>
            <td>
            <?php
                $matching_cfgroup_ids = CFG()->api->get_matching_groups_for_taxonomy( $taxonomy );
                // vi( $matching_cfgroup_ids, '', 'edit term'  );
            
                $args = array (
                    'box'       => 'term',
                    'group_id'  => $matching_cfgroup_ids,
                );
                
                $this->term_meta_box( $term, $args );
            ?>
            </td>
        </tr>
        <?php
    }
    
    /**
    * admin_menu
    */
    // function admin_menu() {
    //     if ( false === apply_filters( 'cfgroup_disable_admin', false ) ) {
    //         add_submenu_page( 'tools.php', __( 'CFG Tools', 'admin-site-enhancements' ), __( 'CFG Tools', 'admin-site-enhancements' ), 'manage_options', 'cfgroup-tools', [ $this, 'page_tools' ] );
    //     }
    // }

    /**
     * add_meta_boxes
     */
    function add_meta_boxes() {
        add_meta_box( 
            'asenha_cfgroup_meta_box', 
            'Field Group Options', 
            [ $this, 'render_cfgroup_meta_box' ], 
            'asenha_cfgroup', 
            'normal', 
            'high'
        );
        // add_meta_box( 'cfgroup_fields', __('Fields', 'cfgroup'), [ $this, 'meta_box' ], 'cfgroup', 'normal', 'high', [ 'box' => 'fields' ] );
        // add_meta_box( 'cfgroup_rules', __('Placement Rules', 'cfgroup'), [ $this, 'meta_box' ], 'cfgroup', 'normal', 'high', [ 'box' => 'rules' ] );
        // add_meta_box( 'cfgroup_extras', __('Extras', 'cfgroup'), [ $this, 'meta_box' ], 'cfgroup', 'normal', 'high', [ 'box' => 'extras' ] );

        add_meta_box( 
            'asenha_cfgroup_tips_meta_box', 
            'Tips', 
            [ $this, 'render_cfgroup_tips_meta_box' ], 
            'asenha_cfgroup', 
            'side', 
            'default'
        );
    }

    /**
     * meta_box
     * @param object $post
     * @param array $metabox
     */
    function meta_box( $post, $metabox ) {
        $box = $metabox['args']['box'];
        include( CFG_DIR . "/templates/meta_box_$box.php" );
    }

    /**
     * meta_box
     * @param object $term
     * @param array $metabox
     */
    function term_meta_box( $term, $args ) {
        include( CFG_DIR . "/templates/meta_box_term.php" );
    }
      
    /**
     * Render main meta box for custom field group
     */
    public function render_cfgroup_meta_box() {
        require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup/templates/meta_box_cfgroup.php';        
    }
    
    /**
     * Render tips meta box for custom field group
     */
    public function render_cfgroup_tips_meta_box() {
        require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup/templates/meta_box_tips.php';        
    }


    /**
     * page_tools
     */
    // function page_tools() {
    //     include( CFG_DIR . '/templates/page_tools.php' );
    // }


    /**
     * save_post
     */
    function save_post( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! isset( $_POST['cfgroup']['save'] ) ) {
            return;
        }

        // If this is the most recent revision, a.k.a. the 'parent' post, do nothing. Ref: https://wordpress.stackexchange.com/a/308190
        if ( false !== wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( wp_verify_nonce( $_POST['cfgroup']['save'], 'cfgroup_save_fields' ) ) {
            $fields = isset( $_POST['cfgroup']['fields'] ) ? $_POST['cfgroup']['fields'] : [];
            $rules = isset( $_POST['cfgroup']['rules'] ) ? $_POST['cfgroup']['rules'] : [];
            $extras = isset( $_POST['cfgroup']['extras'] ) ? $_POST['cfgroup']['extras'] : [];

            CFG()->field_group->save( [
                'post_id'   => $post_id,
                'fields'    => $fields,
                'rules'     => $rules,
                'extras'    => $extras,
            ] );
        }
    }


    /**
     * delete_post
     * @return boolean
     */
    function delete_post( $post_id ) {
        global $wpdb;

        if ( 'asenha_cfgroup' != get_post_type( $post_id ) ) {
            $post_id = (int) $post_id;
            $wpdb->query( "DELETE FROM {$wpdb->prefix}asenha_cfgroup_values WHERE post_id = $post_id" );
        }

        return true;
    }


    /**
     * ajax_handler
     */
    function ajax_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            exit;
        }

        if ( ! check_ajax_referer( 'cfgroup_admin_nonce', 'nonce', false ) ) {
            exit;
        }

        $ajax_method = isset( $_POST['action_type'] ) ? $_POST['action_type'] : false;

        if ( $ajax_method && is_admin() ) {
            include( CFG_DIR . '/includes/ajax.php' );
            $ajax = new cfgroup_ajax();

            if ( 'import' == $ajax_method ) {
                $options = [
                    'import_code' => json_decode( stripslashes( $_POST['import_code'] ), true ),
                ];
                echo CFG()->field_group->import( $options );
            }
            elseif ('export' == $ajax_method) {
                echo json_encode( CFG()->field_group->export( $_POST ) );
            }
            elseif ('reset' == $ajax_method) {
                $ajax->reset();
                deactivate_plugins( plugin_basename( __FILE__ ) );
                echo admin_url( 'plugins.php' );
            }
            elseif ( method_exists( $ajax, $ajax_method ) ) {
                echo $ajax->$ajax_method( $_POST );
            }
        }

        exit;
    }


    /**
     * Customize table columns on the Field Group listing
     */
    function cfgroup_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'title'         => __( 'Title', 'admin-site-enhancements' ),
            'placement'     => __( 'Placement', 'admin-site-enhancements' ),
            'author' => 'Author',
            'date' => 'Date',
        ];
    }


    /**
     * Populate the "Placement" column on the Field Group listing
     */
    function cfgroup_column_content( $column_name, $post_id ) {
        if ( 'placement' == $column_name ) {
            global $wpdb;

            $placement = __( 'On Posts', 'admin-site-enhancements' );

            /*
             * Cache expensive lookups per request.
             *
             * This callback runs once per row in the list table.
             */
            static $asenha_cfg_display_cache = array(
                'page_templates' => null,
                'options_pages'  => null,
                'user_roles'     => null,
                'post_types'     => array(),
                'post_formats'   => array(),
                'taxonomies'     => array(),
                'terms'          => array(),
            );

            $labels = [
                'placement'             => __( 'Placement', 'admin-site-enhancements' ),
                'post_types'            => __( 'Post Types', 'admin-site-enhancements' ),
                'user_roles'            => __( 'User Roles', 'admin-site-enhancements' ),
                'post_ids'              => __( 'Posts', 'admin-site-enhancements' ),
                'term_ids'              => __( 'Terms', 'admin-site-enhancements' ),
                'page_templates'        => __( 'Page Templates', 'admin-site-enhancements' ),
                'post_formats'          => __( 'Post Formats', 'admin-site-enhancements' ),
                'options_pages'         => __( 'Options Pages', 'admin-site-enhancements' ),
                'taxonomies'            => __( 'Taxonomies', 'admin-site-enhancements' ),
            ];

            $field_groups = CFG()->field_group->load_field_groups();

            // Make sure the field group exists
            $rules = [];
            if ( isset( $field_groups[ $post_id ] ) ) {
                $rules = $field_groups[ $post_id ]['rules'];
            }
            
            // if ( '11230' == $post_id ) {
            //     vi ( $rules );            
            // }

            $posts_placement_criterias = array(
                'post_types',
                'user_roles',
                'post_ids',
                'term_ids',
                'page_templates',
                'post_formats'
            );

            $options_pages_placement_criterias = array(
                'options_pages',
            );

            $taxonomy_terms_placement_criterias = array(
                'taxonomies',
            );

            $showable_placement_criterias = array();

            if ( ! isset( $rules['placement'] ) ) {
                $showable_placement_criterias = $posts_placement_criterias;
            }

            foreach ( $rules as $criteria => $data ) {
                if ( 'placement' == $criteria ) {
                    if ( empty( $data['values'] ) || 'posts' == $data['values'] ) {
                        $placement = __( 'On Posts', 'admin-site-enhancements' );
                        $showable_placement_criterias = $posts_placement_criterias;
                    }
                    
                    if ( 'options-pages' == $data['values'] ) {
                        $placement = __( 'On Options Pages', 'admin-site-enhancements' );
                        $showable_placement_criterias = $options_pages_placement_criterias;
                    }

                    if ( 'taxonomy-terms' == $data['values'] ) {
                        $placement = __( 'On Taxonomy Terms', 'admin-site-enhancements' );
                        $showable_placement_criterias = $taxonomy_terms_placement_criterias;
                    }
                }
            }

            if ( empty( $rules ) 
                || isset( $rules['placement'] ) && (
                    'posts' == $rules['placement']['values'] && empty( $rules['post_types'] ) && empty( $rules['user_roles'] ) && empty( $rules['post_ids'] ) && empty( $rules['term_ids'] ) && empty( $rules['page_templates'] )
                    || 'options-pages' == $rules['placement']['values'] && empty( $rules['options_pages'] )
                    || 'taxonomy-terms' == $rules['placement']['values'] && empty( $rules['taxonomies'] )
                )
            ) {
                echo '<div class="asenha-warning-message missing-placement-rules-warning">' . esc_html__( 'No placement rules have been defined.', 'admin-site-enhancements' ) . '</div>';
            } else {
                printf(
                    '<div><strong>%s</strong></div>',
                    esc_html( $placement )
                );

                foreach ( $rules as $criteria => $data ) {
                    if ( in_array( $criteria, $showable_placement_criterias ) ) {
                        $label  = isset( $labels[ $criteria ] ) ? $labels[ $criteria ] : $criteria;
                        $values = isset( $data['values'] ) ? $data['values'] : array();

                        $operator = isset( $data['operator'] ) ? (array) $data['operator'] : array( '==' );
                        $operator = ( isset( $operator[0] ) && '!=' === $operator[0] ) ? '!=' : '=';

                        $values_html = '';

                        switch ( $criteria ) {
                            case 'post_types':
                                $formatted = array();
                                foreach ( (array) $values as $val ) {
                                    $post_type = sanitize_key( $val );

                                    if ( empty( $post_type ) ) {
                                        continue;
                                    }

                                    if ( ! isset( $asenha_cfg_display_cache['post_types'][ $post_type ] ) ) {
                                        $post_type_obj = get_post_type_object( $post_type );
                                        $post_type_label = $post_type;

                                        if ( is_object( $post_type_obj ) && isset( $post_type_obj->labels ) ) {
                                            if ( isset( $post_type_obj->labels->singular_name ) ) {
                                                $post_type_label = $post_type_obj->labels->singular_name;
                                            } elseif ( isset( $post_type_obj->labels->name ) ) {
                                                $post_type_label = $post_type_obj->labels->name;
                                            }
                                        }

                                        $asenha_cfg_display_cache['post_types'][ $post_type ] = $post_type_label;
                                    }

                                    $formatted[] = sprintf(
                                        '%1$s (%2$s)',
                                        $asenha_cfg_display_cache['post_types'][ $post_type ],
                                        $post_type
                                    );
                                }

                                $values_html = esc_html( implode( ', ', $formatted ) );
                                break;

                            case 'post_formats':
                                $formatted = array();
                                foreach ( (array) $values as $val ) {
                                    $post_format = sanitize_key( $val );

                                    if ( empty( $post_format ) ) {
                                        continue;
                                    }

                                    if ( ! isset( $asenha_cfg_display_cache['post_formats'][ $post_format ] ) ) {
                                        $post_format_label = $post_format;

                                        if ( 'standard' === $post_format ) {
                                            $post_format_label = __( 'Standard', 'admin-site-enhancements' );
                                        } else {
                                            $post_format_string = get_post_format_string( $post_format );
                                            if ( ! empty( $post_format_string ) ) {
                                                $post_format_label = $post_format_string;
                                            }
                                        }

                                        $asenha_cfg_display_cache['post_formats'][ $post_format ] = $post_format_label;
                                    }

                                    $formatted[] = sprintf(
                                        '%1$s (%2$s)',
                                        $asenha_cfg_display_cache['post_formats'][ $post_format ],
                                        $post_format
                                    );
                                }

                                $values_html = esc_html( implode( ', ', $formatted ) );
                                break;

                            case 'user_roles':
                                if ( null === $asenha_cfg_display_cache['user_roles'] ) {
                                    $asenha_cfg_display_cache['user_roles'] = array();

                                    $wp_roles = function_exists( 'wp_roles' ) ? wp_roles() : null;
                                    if ( is_object( $wp_roles ) && isset( $wp_roles->roles ) && is_array( $wp_roles->roles ) ) {
                                        foreach ( $wp_roles->roles as $role_key => $role_data ) {
                                            $role_name = isset( $role_data['name'] ) ? translate_user_role( $role_data['name'] ) : $role_key;
                                            $asenha_cfg_display_cache['user_roles'][ $role_key ] = $role_name;
                                        }
                                    }
                                }

                                $formatted = array();
                                foreach ( (array) $values as $val ) {
                                    $role_key = sanitize_key( $val );

                                    if ( empty( $role_key ) ) {
                                        continue;
                                    }

                                    $role_label = isset( $asenha_cfg_display_cache['user_roles'][ $role_key ] )
                                        ? $asenha_cfg_display_cache['user_roles'][ $role_key ]
                                        : $role_key;

                                    $formatted[] = sprintf(
                                        '%1$s (%2$s)',
                                        $role_label,
                                        $role_key
                                    );
                                }

                                $values_html = esc_html( implode( ', ', $formatted ) );
                                break;

                            case 'term_ids':
                                $formatted = array();
                                foreach ( (array) $values as $val ) {
                                    $term_id = absint( $val );

                                    if ( 0 === $term_id ) {
                                        continue;
                                    }

                                    if ( ! array_key_exists( $term_id, $asenha_cfg_display_cache['terms'] ) ) {
                                        $term = get_term( $term_id );
                                        $asenha_cfg_display_cache['terms'][ $term_id ] = ( is_object( $term ) && ! is_wp_error( $term ) && isset( $term->name ) )
                                            ? $term->name
                                            : '';
                                    }

                                    $term_name = $asenha_cfg_display_cache['terms'][ $term_id ];
                                    if ( '' === $term_name ) {
                                        $formatted[] = (string) $term_id;
                                    } else {
                                        $formatted[] = sprintf(
                                            '%1$s (%2$d)',
                                            $term_name,
                                            $term_id
                                        );
                                    }
                                }

                                $values_html = esc_html( implode( ', ', $formatted ) );
                                break;

                            case 'page_templates':
                                if ( null === $asenha_cfg_display_cache['page_templates'] ) {
                                    $asenha_cfg_display_cache['page_templates'] = array();
                                    $templates = get_page_templates();

                                    if ( is_array( $templates ) ) {
                                        foreach ( $templates as $template_name => $template_filename ) {
                                            $asenha_cfg_display_cache['page_templates'][ $template_filename ] = $template_name;
                                        }
                                    }

                                    // Common core value for "no custom template".
                                    $asenha_cfg_display_cache['page_templates']['default'] = __( 'Default', 'admin-site-enhancements' );
                                }

                                $formatted = array();
                                foreach ( (array) $values as $val ) {
                                    $raw_template = (string) $val;

                                    if ( '' === $raw_template ) {
                                        continue;
                                    }

                                    $template_label = isset( $asenha_cfg_display_cache['page_templates'][ $raw_template ] )
                                        ? $asenha_cfg_display_cache['page_templates'][ $raw_template ]
                                        : $raw_template;

                                    $formatted[] = sprintf(
                                        '%1$s (%2$s)',
                                        $template_label,
                                        $raw_template
                                    );
                                }

                                $values_html = esc_html( implode( ', ', $formatted ) );
                                break;

                            case 'taxonomies':
                                $formatted = array();
                                foreach ( (array) $values as $val ) {
                                    $taxonomy = sanitize_key( $val );

                                    if ( empty( $taxonomy ) ) {
                                        continue;
                                    }

                                    if ( ! isset( $asenha_cfg_display_cache['taxonomies'][ $taxonomy ] ) ) {
                                        $tax_obj = get_taxonomy( $taxonomy );
                                        $taxonomy_label = $taxonomy;

                                        if ( is_object( $tax_obj ) ) {
                                            if ( isset( $tax_obj->labels ) && isset( $tax_obj->labels->name ) ) {
                                                // Use plural label.
                                                $taxonomy_label = $tax_obj->labels->name;
                                            } elseif ( isset( $tax_obj->label ) ) {
                                                $taxonomy_label = $tax_obj->label;
                                            }
                                        }

                                        $asenha_cfg_display_cache['taxonomies'][ $taxonomy ] = $taxonomy_label;
                                    }

                                    $formatted[] = sprintf(
                                        '%1$s (%2$s)',
                                        $asenha_cfg_display_cache['taxonomies'][ $taxonomy ],
                                        $taxonomy
                                    );
                                }

                                $values_html = esc_html( implode( ', ', $formatted ) );
                                break;

                            case 'options_pages':
                                if ( null === $asenha_cfg_display_cache['options_pages'] ) {
                                    $asenha_cfg_display_cache['options_pages'] = array();

                                    $options_page_configs = get_posts(
                                        array(
                                            'post_type'   => 'options_page_config',
                                            'post_status' => 'publish',
                                            'numberposts' => -1,
                                            'orderby'     => 'title',
                                            'order'       => 'ASC',
                                        )
                                    );

                                    if ( ! empty( $options_page_configs ) ) {
                                        foreach ( $options_page_configs as $options_page_config ) {
                                            $menu_slug = get_post_meta( $options_page_config->ID, 'options_page_menu_slug', true );
                                            if ( ! empty( $menu_slug ) ) {
                                                $asenha_cfg_display_cache['options_pages'][ $menu_slug ] = $options_page_config->post_title;
                                            }
                                        }
                                    }
                                }

                                $formatted = array();
                                foreach ( (array) $values as $val ) {
                                    $raw_slug = (string) $val;

                                    if ( '' === $raw_slug ) {
                                        continue;
                                    }

                                    $options_page_label = isset( $asenha_cfg_display_cache['options_pages'][ $raw_slug ] )
                                        ? $asenha_cfg_display_cache['options_pages'][ $raw_slug ]
                                        : $raw_slug;

                                    $formatted[] = sprintf(
                                        '%1$s (%2$s)',
                                        $options_page_label,
                                        $raw_slug
                                    );
                                }

                                $values_html = esc_html( implode( ', ', $formatted ) );
                                break;

                            case 'post_ids':
                                // Link post titles to their permalinks.
                                $links = array();
                                foreach ( (array) $values as $val ) {
                                    $pid = absint( $val );

                                    if ( 0 === $pid ) {
                                        continue;
                                    }

                                    $title = get_the_title( $pid );
                                    if ( '' === $title ) {
                                        $title = __( '(no title)' );
                                    }

                                    $url = get_permalink( $pid );
                                    if ( ! empty( $url ) ) {
                                        $links[] = sprintf(
                                            '<a href="%1$s">%2$s</a>',
                                            esc_url( $url ),
                                            esc_html( $title )
                                        );
                                    } else {
                                        $links[] = esc_html( $title );
                                    }
                                }

                                $values_html = implode( ', ', $links );
                                break;

                            default:
                                if ( is_array( $values ) ) {
                                    $values_html = esc_html( implode( ', ', $values ) );
                                } else {
                                    $values_html = esc_html( (string) $values );
                                }
                                break;
                        }

                        if ( '' === $values_html ) {
                            continue;
                            }

                        echo '<div>' . esc_html( $label ) . ' ' . esc_html( $operator ) . ' ' . $values_html . '</div>';
                    }
                }
            }
        }
    }
    
    /**
     * Make sure that in the WYSIWYG field, caption shortcode is rendered
     * 
     * @since 6.9.10
     */
    function render_image_caption_shortcode( $shcode, $html ) {
        // Render shortcode for image with caption
        // May return HTML wrapped with either <div> or <figure> depending on the theme / page builder
        $output = do_shortcode( $shcode );
        // Make sure we only return absolute URL of the image
        $output = str_replace( '..', get_site_url(), $output );
        // Remove line breaks to prevent blank paragraph issue
        $output = str_replace( array( "\r", "\n" ), '', $output );

        return $output;
    }
}

new cfgroup_init();
