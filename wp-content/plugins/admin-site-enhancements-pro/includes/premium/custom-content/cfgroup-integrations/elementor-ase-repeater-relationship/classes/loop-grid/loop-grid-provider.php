<?php

namespace ElementorAseRepeaterRelationship\LoopGrid;

use ElementorAseRepeaterRelationship\Controls\LoopGridControlsBase;
use ElementorAseRepeaterRelationship\Configurator;

class LoopGridProvider {
    protected static $instance = null;

    protected $configurator;

    protected $controls;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->configurator = \ElementorAseRepeaterRelationship\Configurator::instance();
        $this->init_controls();
        $this->register_controls();
        // Add filter for virtual post classes
        add_filter( 'post_class', [ $this, 'add_virtual_post_classes' ], 10, 3 );
        // Add filter to clean up WHERE clause
        add_filter( 'posts_where', [ $this, 'clean_posts_where' ], 10, 2 );
    }

    protected function init_controls() {
        $this->controls = new \ElementorAseRepeaterRelationship\Controls\LoopGridControlsBase( $this->configurator, $this );
    }

    protected function register_controls() {
        add_action(
            'elementor/element/loop-grid/section_query/after_section_start',
            [ $this->controls, 'register_query_controls' ],
            10,
            2
        );
    }

    // Create an array that consists of repeater field row data
    // $posts contains an array of posts data relevant to the repeater field in question, e.g. movie_scenes repeater will contain movie posts
    public function add_virtual_posts( $posts, $query ) {
        // Only run this filter for our specific post type
        if ( ! isset( $query->query_vars['asenha_virtual_posts'] ) || $query->query_vars['asenha_virtual_posts'] !== true ) {
            return $posts;
        }

        $repeater_field = $query->get( 'ase_repeater_field' ); // e.g. 'movie_scenes'

        if ( ! $repeater_field ) {
            return $posts;
        }

        $virtual_posts = [];

        foreach ( $posts as $post ) {
            $repeater_data = get_cf( $repeater_field, 'raw', $post->ID );

            if ( ! $repeater_data || is_null( $repeater_data ) || ! is_array( $repeater_data ) ) {
                continue;
            }

            foreach ( $repeater_data as $index => $row ) {
                $virtual_post = new \stdClass();
                $virtual_post->ID = -1 * ($post->ID . $this->configurator::VIRTUAL_POST_ID_SEPARATOR . $index);
                $virtual_post->post_parent = $post->ID;
                $virtual_post->post_title = $post->post_title . ' - ' . $repeater_field . ' ' . ($index + 1);
                $virtual_post->post_status = 'publish';
                $virtual_post->post_type = $post->post_type;
                // Use parent's post type instead of creating new one
                $virtual_post->filter = 'raw';
                // Add our custom data
                $virtual_post->ase_repeater_data = $row;
                $virtual_post->asenha_loop_index = $index;
                $virtual_posts[] = $virtual_post;
            }
        }

        return $virtual_posts;
    }

    public function filter_elementor_query_args( $query_args, $widget ) {
        $settings = $widget->get_settings();
        
        // Handle ASE Repeater query
        if ( isset( $settings['use_ase_repeater'] ) && $settings['use_ase_repeater'] === 'yes' ) {
            // Add our virtual posts flags
            $query_args['asenha_virtual_posts'] = true;
            $query_args['ase_repeater_field'] = $settings['ase_repeater_field'];
            $query_args['query_current_post_only'] = $settings['query_current_post_only'];
            // Only modify query if we want current post
            if ( $settings['query_current_post_only'] === 'yes' ) {
                $query_args['post__in'] = [ get_the_ID() ];
            }
        }
        
        // Handle ASE Relationship query
        if ( isset( $settings['use_ase_relationship'] ) && $settings['use_ase_relationship'] === 'yes' ) {
            $relationship_field = isset( $settings['ase_relationship_field'] ) ? $settings['ase_relationship_field'] : '';
            $query_current_post_only = isset( $settings['relationship_query_current_post_only'] ) ? $settings['relationship_query_current_post_only'] : 'yes';
            $relation_type = isset( $settings['ase_relationship_type'] ) ? $settings['ase_relationship_type'] : 'related_to';
            
            if ( ! empty( $relationship_field ) ) {
                // Get the current post ID, accounting for Elementor editor preview context
                $current_post_id = $this->get_current_context_post_id();
                
                if ( $query_current_post_only === 'yes' && $current_post_id ) {
                    // Get relationship field value (array of post IDs) from current post
                    if ( 'related_to' === $relation_type ) {
                        // Get posts that the current post is related TO (target posts)
                        $related_post_ids = get_cf_related_to( $relationship_field, 'default', 'raw', $current_post_id );
                    } else {
                        // Get posts that are related TO the current post (origin posts / reverse relationship)
                        $related_post_ids = get_cf_related_from( $relationship_field, 'default', false, 'publish', 'relationship', $current_post_id );
                    }
                    
                    if ( ! empty( $related_post_ids ) && is_array( $related_post_ids ) ) {
                        // Ensure all values are integers
                        $related_post_ids = array_map( 'intval', $related_post_ids );
                        $query_args['post__in'] = $related_post_ids;
                        $query_args['orderby'] = 'post__in'; // Preserve the order set in the relationship field
                        // Remove any post type restrictions if needed, as relationship can point to different post types
                        if ( isset( $query_args['post_type'] ) && $query_args['post_type'] === 'post' ) {
                            $query_args['post_type'] = 'any';
                        }
                    } else {
                        // No related posts found, return empty result
                        $query_args['post__in'] = [ 0 ];
                    }
                } else {
                    // Query all posts that have this relationship field with values
                    // This is a more complex query - we get all posts and collect their related IDs
                    $all_related_ids = $this->get_all_related_post_ids( $relationship_field, $relation_type );
                    
                    if ( ! empty( $all_related_ids ) ) {
                        $query_args['post__in'] = $all_related_ids;
                        $query_args['orderby'] = 'post__in';
                        if ( isset( $query_args['post_type'] ) && $query_args['post_type'] === 'post' ) {
                            $query_args['post_type'] = 'any';
                        }
                    } else {
                        $query_args['post__in'] = [ 0 ];
                    }
                }
                
                // Mark this as a relationship query for potential future filtering
                $query_args['asenha_relationship_query'] = true;
                $query_args['ase_relationship_field'] = $relationship_field;
            }
        }
        
        return $query_args;
    }

    /**
     * Get the current post ID, accounting for Elementor editor preview context.
     *
     * In the Elementor editor, when editing theme builder templates (like Single Post),
     * get_the_ID() returns the template ID. This method retrieves the preview post ID
     * from the document settings instead.
     *
     * @return int The post ID to use for querying relationship data.
     */
    protected function get_current_context_post_id() {
        $post_id = get_the_ID();
        
        // Check if we're in Elementor editor mode
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            // Try to get the preview post ID from the current document's settings
            $document = \Elementor\Plugin::$instance->documents->get_current();
            
            if ( $document ) {
                $page_settings = $document->get_meta( '_elementor_page_settings' );
                
                // If preview_id is set in page settings, use it
                if ( ! empty( $page_settings['preview_id'] ) ) {
                    $post_id = absint( $page_settings['preview_id'] );
                }
            }
        }
        
        return $post_id;
    }
    
    /**
     * Get all related post IDs from all posts that have the specified relationship field.
     *
     * @param string $relationship_field The relationship field name.
     * @param string $relation_type      The relation type: 'related_to' for target posts or 'related_from' for origin posts.
     * @return array Array of unique post IDs.
     */
    protected function get_all_related_post_ids( $relationship_field, $relation_type = 'related_to' ) {
        $all_related_ids = [];
        
        // Get the field info to determine which post types use this field
        $field_info = find_cf( array( 'field_name' => $relationship_field ) );
        
        if ( empty( $field_info ) || ! isset( $field_info[ $relationship_field ] ) ) {
            return $all_related_ids;
        }
        
        // Query all posts that have this relationship field set
        $args = array(
            'post_type'      => 'any',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => $relationship_field,
                    'compare' => 'EXISTS',
                ),
            ),
        );
        
        $posts_with_field = get_posts( $args );
        
        // Get related post IDs from each post based on relation type
        foreach ( $posts_with_field as $post_id ) {
            if ( 'related_from' === $relation_type ) {
                // Get posts that are related TO this post (origin posts / reverse relationship)
                $related_ids = get_cf_related_from( $relationship_field, 'default', false, 'publish', 'relationship', $post_id );
            } else {
                // Get posts that this post is related TO (target posts)
                $related_ids = get_cf_related_to( $relationship_field, 'default', 'raw', $post_id );
            }
            
            if ( ! empty( $related_ids ) && is_array( $related_ids ) ) {
                $all_related_ids = array_merge( $all_related_ids, array_map( 'intval', $related_ids ) );
            }
        }
        
        // Remove duplicates and filter out zeros
        $all_related_ids = array_unique( array_filter( $all_related_ids ) );
        
        return $all_related_ids;
    }

    public function get_ase_repeater_fields() {
        $repeater_fields = [];

        try {
            $repeater_fields_raw = find_cf( array( 'field_type' => 'repeater' ) );
            if ( ! empty( $repeater_fields_raw ) && is_array( $repeater_fields_raw ) ) {
                foreach ( $repeater_fields_raw as $field_name => $field_info ) {
                    $repeater_fields[$field_name] = $field_info['label'] . ' (' . $field_name . ')';
                }
            }
        } catch ( \Exception $e ) {
            // Silently handle any exceptions
        }

        return $repeater_fields;
    }

    /**
     * Get all ASE relationship fields.
     *
     * @return array Array of relationship fields with field name as key and label as value.
     */
    public function get_ase_relationship_fields() {
        $relationship_fields = [];

        try {
            $relationship_fields_raw = find_cf( array( 'field_type' => 'relationship' ) );
            if ( ! empty( $relationship_fields_raw ) && is_array( $relationship_fields_raw ) ) {
                foreach ( $relationship_fields_raw as $field_name => $field_info ) {
                    $relationship_fields[ $field_name ] = $field_info['label'] . ' (' . $field_name . ')';
                }
            }
        } catch ( \Exception $e ) {
            // Silently handle any exceptions
        }

        return $relationship_fields;
    }

    // private function process_fields( $fields, &$result, $parent = '' ) {
    //     foreach ( $fields as $field ) {
    //         if ( $field['type'] === 'repeater' ) {
    //             $key = ( $parent ? $parent . '_' . $field['name'] : $field['name'] );
    //             $result[$key] = $field['label'];
    //         } elseif ( $field['type'] === 'group' && !empty( $field['sub_fields'] ) ) {
    //             $this->process_fields( $field['sub_fields'], $result, $field['name'] );
    //         }
    //     }
    // }

    public function get_original_post_title( $post_id ) {
        if ( $post_id < 0 ) {
            // This is a virtual post
            $original_post_id = abs( $post_id );
            $original_post_id = explode( $this->configurator::VIRTUAL_POST_ID_SEPARATOR, $original_post_id )[0];
            $post = get_post( $original_post_id );
        } else {
            $post = get_post( $post_id );
        }
        if ( !$post ) {
            return '';
        }
        return get_the_title( $post->ID );
    }

    public function add_virtual_post_classes( $classes, $class, $post_id ) {
        if ( is_string( $post_id ) && strpos( $post_id, '-' ) === 0 ) {
            // Add standard WordPress classes
            $classes[] = 'post-' . abs( $post_id );
            $classes[] = 'type-repeater-field-post';
            $classes[] = 'status-publish';
            $classes[] = 'hentry';
        }
        return $classes;
    }

    public function clean_posts_where( $where, $query ) {
        // Only clean WHERE clause if we're not in current post only mode
        if ( ! isset( $query->query_vars['query_current_post_only'] ) || $query->query_vars['query_current_post_only'] !== 'yes' ) {
            // Remove any NOT IN clauses
            $where = preg_replace( '/AND\\s+wp_posts\\.ID\\s+NOT\\s+IN\\s*\\([^)]+\\)/', '', $where );
        }
        return $where;
    }

}
