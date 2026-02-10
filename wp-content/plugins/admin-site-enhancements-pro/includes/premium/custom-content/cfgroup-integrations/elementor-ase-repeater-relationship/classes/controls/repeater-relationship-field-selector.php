<?php
namespace ElementorAseRepeaterRelationship\Controls;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use ElementorAseRepeaterRelationship\Configurator;
use Elementor\Controls_Manager;
use ElementorPro\Modules\LoopBuilder\Documents\Loop;

/**
 * This is for Loop Item >> Settings
 */
class RepeaterRelationshipFieldSelector {
    private static $instance = null;
    private $configurator;
    const SETTINGS_KEY = 'RepeaterRelationshipFieldSelector';

    private function __construct() {
        $this->configurator = Configurator::instance();
        add_action( 'elementor/documents/register_controls', [ $this, 'register_controls' ] );
        add_action( 'elementor/document/before_save', [ $this, 'save_settings' ], 10, 2 );
    }

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function register_controls($document) {
        if ( ! $document instanceof Loop || ! $document::get_property( 'has_elements' ) ) {
            return;
        }

        // ASE Repeater Loop Settings Section
        $document->start_controls_section(
            'asenha_loop_settings_section',
            [
                'label'     => __( 'ASE Repeater Loop Settings', 'admin-site-enhancements' ),
                'tab'       => Controls_Manager::TAB_SETTINGS,
            ]
        );

        $repeater_fields = $this->get_repeater_fields();
        $saved_repeater_field = $this->get_saved_repeater_field( $document->get_main_id() );

        $document->add_control(
            'asenha_loop_repeater_field',
            [
                'label'         => __( 'ASE Repeater Field for Loop', 'admin-site-enhancements' ),
                'type'          => Controls_Manager::SELECT,
                'options'       => $repeater_fields,
                'default'       => $saved_repeater_field ?: '',
                'description'   => __( 'Select an ASE repeater field to use in this loop template.', 'admin-site-enhancements' ),
            ]
        );

        $document->end_controls_section();

        // ASE Relationship Loop Settings Section
        $document->start_controls_section(
            'asenha_relationship_loop_settings_section',
            [
                'label'     => __( 'ASE Relationship Loop Settings', 'admin-site-enhancements' ),
                'tab'       => Controls_Manager::TAB_SETTINGS,
            ]
        );

        $relationship_fields = $this->get_relationship_fields();
        $saved_relationship_field = $this->get_saved_relationship_field( $document->get_main_id() );

        $document->add_control(
            'asenha_loop_relationship_field',
            [
                'label'         => __( 'ASE Relationship Field for Loop', 'admin-site-enhancements' ),
                'type'          => Controls_Manager::SELECT,
                'options'       => $relationship_fields,
                'default'       => $saved_relationship_field ?: '',
                'description'   => __( 'Select an ASE relationship field to use in this loop template.', 'admin-site-enhancements' ),
            ]
        );

        $document->end_controls_section();
    }

    public function save_settings( $document, $data ) {
        // Save repeater field setting
        if ( isset( $data['settings']['asenha_loop_repeater_field'] ) ) {
            $selected_field = $data['settings']['asenha_loop_repeater_field'];
            update_post_meta( $document->get_main_id(), 'asenha_loop_repeater_field', $selected_field );
        }
        
        // Save relationship field setting
        if ( isset( $data['settings']['asenha_loop_relationship_field'] ) ) {
            $selected_field = $data['settings']['asenha_loop_relationship_field'];
            update_post_meta( $document->get_main_id(), 'asenha_loop_relationship_field', $selected_field );
        }
    }
    
    public function get_saved_repeater_field( $document_id ) {
        $field = get_post_meta( $document_id, 'asenha_loop_repeater_field', true );
        return $field;
    }

    /**
     * Get the saved relationship field for a document.
     *
     * @param int $document_id The document ID.
     * @return string The saved relationship field name.
     */
    public function get_saved_relationship_field( $document_id ) {
        $field = get_post_meta( $document_id, 'asenha_loop_relationship_field', true );
        return $field;
    }

    private function get_repeater_fields() {
        $repeater_fields = [ '' => __( 'Select...', 'admin-site-enhancements' ) ];

        $fields = find_cf(); // Get all fields in the site

        foreach ( $fields as $field ) {
            if ( 'repeater' === $field['type'] ) {
                $repeater_fields[$field['name']] = $field['label'] . ' (' . $field['name'] . ')';
            }
        }

        return $repeater_fields;
    }

    /**
     * Get all relationship fields.
     *
     * @return array Array of relationship fields with field name as key and label as value.
     */
    private function get_relationship_fields() {
        $relationship_fields = [ '' => __( 'Select...', 'admin-site-enhancements' ) ];

        $fields = find_cf(); // Get all fields in the site

        foreach ( $fields as $field ) {
            if ( 'relationship' === $field['type'] ) {
                $relationship_fields[ $field['name'] ] = $field['label'] . ' (' . $field['name'] . ')';
            }
        }

        return $relationship_fields;
    }
}
