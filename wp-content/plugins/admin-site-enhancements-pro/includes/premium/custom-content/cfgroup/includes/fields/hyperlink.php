<?php

class cfgroup_hyperlink extends cfgroup_field
{


    function __construct() {
        $this->name = 'hyperlink';
        $this->label = __( 'Hyperlink', 'admin-site-enhancements' );
    }


    function html( $field ) {
        $field->value = [
            'url'    => isset( $field->value['url'] ) ? $field->value['url'] : '',
            'text'   => isset( $field->value['text'] ) ? $field->value['text'] : '',
            'class'  => isset( $field->value['class'] ) ? $field->value['class'] : '',
            'target' => isset( $field->value['target'] ) ? $field->value['target'] : '',
        ];

        $ui_mode = $this->get_option( $field, 'ui', 'simple' );
    ?>
        <div class="cfgroup-hyperlink">
            <?php if ( is_admin() && 'wp_link' === $ui_mode ) : ?>
                <?php
                    /*
                     * Some CFG contexts provide $field as a stdClass without an `id` property.
                     * Use a stable fallback based on `input_name` (which is always present).
                     */
                    $field_identifier = isset( $field->id ) ? absint( $field->id ) : 0;
                    if ( 0 === $field_identifier && ! empty( $field->input_name ) ) {
                        $field_identifier = substr( md5( (string) $field->input_name ), 0, 12 );
                    }

                    $editor_id = 'asenha-cfgroup-wplink-' . $field_identifier;
                ?>
                <div
                    class="cfgroup-hyperlink-wplink"
                    data-ui="wp_link"
                    data-empty-text="<?php echo esc_attr__( 'No link selected.', 'admin-site-enhancements' ); ?>"
                    data-add-text="<?php echo esc_attr__( 'Add Link', 'admin-site-enhancements' ); ?>"
                    data-update-text="<?php echo esc_attr__( 'Update Link', 'admin-site-enhancements' ); ?>"
                    data-editor-id="<?php echo esc_attr( $editor_id ); ?>"
                >
                    <textarea id="<?php echo esc_attr( $editor_id ); ?>" class="cfgroup-hyperlink-wplink-editor" aria-hidden="true"></textarea>
                    <div class="cfgroup-hyperlink-wplink-preview">
                        <?php if ( ! empty( $field->value['url'] ) ) : ?>
                            <a class="cfgroup-hyperlink-wplink-preview-link" href="<?php echo esc_url( $field->value['url'] ); ?>"<?php echo ( '_blank' === $field->value['target'] ) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                <?php echo esc_html( ! empty( $field->value['text'] ) ? $field->value['text'] : $field->value['url'] ); ?>
                            </a>
                        <?php else : ?>
                            <span class="cfgroup-hyperlink-wplink-preview-empty">
                                <?php echo esc_html__( 'No link selected.', 'admin-site-enhancements' ); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="cfgroup-hyperlink-wplink-actions">
                        <?php
                            $has_url = ! empty( $field->value['url'] );
                            $button_text = $has_url ? esc_html__( 'Edit Link', 'admin-site-enhancements' ) : esc_html__( 'Select Link', 'admin-site-enhancements' );
                        ?>
                        <button
                            type="button"
                            class="button cfgroup-hyperlink-wplink-select"
                            data-select-text="<?php echo esc_attr__( 'Select Link', 'admin-site-enhancements' ); ?>"
                            data-edit-text="<?php echo esc_attr__( 'Edit Link', 'admin-site-enhancements' ); ?>"
                        >
                            <?php echo $button_text; ?>
                        </button>
                        <button type="button" class="button cfgroup-hyperlink-wplink-clear">
                            <?php echo esc_html__( 'Clear', 'admin-site-enhancements' ); ?>
                        </button>
                    </div>

                    <input type="hidden" name="<?php echo esc_attr( $field->input_name ); ?>[url]" class="link-url" value="<?php echo esc_url( $field->value['url'] ); ?>" />
                    <input type="hidden" name="<?php echo esc_attr( $field->input_name ); ?>[text]" class="link-text" value="<?php echo esc_attr( $field->value['text'] ); ?>" />
                    <input type="hidden" name="<?php echo esc_attr( $field->input_name ); ?>[target]" class="link-target" value="<?php echo esc_attr( $field->value['target'] ); ?>" />
                </div>
            <?php else : ?>
            <div class="fields-wrapper">
                <div class="field-column-half">
                    <div class="cfgroup-hyperlink-url">
                        <div><?php _e( 'URL', 'admin-site-enhancements' ); ?></div>
                        <input type="text" name="<?php echo esc_attr( $field->input_name ); ?>[url]" class="link-url" value="<?php echo esc_url( $field->value['url'] ); ?>" placeholder="http://" />
                    </div>
                </div>
                <div class="field-column-quarter">
                    <div class="cfgroup-hyperlink-text">
                        <div><?php _e( 'Link Text', 'admin-site-enhancements' ); ?></div>
                        <input type="text" name="<?php echo esc_attr( $field->input_name ); ?>[text]" class="link-text" value="<?php echo esc_attr( $field->value['text'] ); ?>" />
                    </div>
                </div>
                <div class="field-column-quarter">
                    <div class="cfgroup-hyperlink-target">
                        <div><?php _e( 'Link Target', 'admin-site-enhancements' ); ?></div>
                        <select class="link-target widefat" name="<?php echo esc_attr( $field->input_name ); ?>[target]">
                            <option value="none" <?php selected( 'none', esc_attr( $field->value['target'] ) ); ?>>None</option>
                            <option value="_blank" <?php selected( '_blank', esc_attr( $field->value['target'] ) ); ?>>_blank</option>
                            <option value="_self" <?php selected( '_self', esc_attr( $field->value['target'] ) ); ?>>_self</option>
                            <option value="_top" <?php selected( '_top', esc_attr( $field->value['target'] ) ); ?>>_top</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Conditionally load assets for wpLink-based hyperlink UI.
     *
     * Note: This is called once per page load for the first used field of this type.
     *
     * @since 7.8.11
     *
     * @param mixed $field The field object (optional)
     */
    function input_head( $field = null ) {
        $ui_mode = $this->get_option( $field, 'ui', 'simple' );

        if ( ! is_admin() || 'wp_link' !== $ui_mode ) {
            return;
        }

        // Ensure core wpLink scripts/markup are available.
        if ( function_exists( 'wp_enqueue_editor' ) ) {
            wp_enqueue_editor();
        }

        // Load our bridge script.
        wp_enqueue_script(
            'asenha-cfgroup-hyperlink-wplink',
            CFG_URL . '/assets/js/hyperlink-wplink.js',
            [ 'jquery', 'wplink' ],
            CFG_VERSION,
            true
        );
    }


    function options_html( $key, $field = null ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Input UI', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type'          => 'select',
                        'input_name'    => "cfgroup[fields][$key][options][ui]",
                        'options'       => [
                            'choices' => [
                                'simple'  => __( 'Simple inputs (URL / Text / Target)', 'admin-site-enhancements' ),
                                'wp_link' => __( 'Use WordPress link modal (wpLink)', 'admin-site-enhancements' ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'ui', 'simple' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Output Format', 'cfgroup'); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][format]",
                        'options' => [
                            'choices' => [
                                'html' => __( 'HTML', 'admin-site-enhancements' ),
                                'php' => __( 'PHP Array', 'admin-site-enhancements' )
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'format', 'html' ),
                    ] );
                ?>
            </td>
        </tr>
    <?php
    }


    function pre_save( $value, $field = null ) {
        // convert to a proper associative array when inside a Repeater
        if ( isset( $value[0]['url'], $value[1]['text'], $value[2]['target'] ) ) {
            $value = [
                'url'    => $value[0]['url'],
                'text'   => $value[1]['text'],
                'target' => $value[2]['target'],
            ];
        }
        return serialize( $value );
    }


    function prepare_value( $value, $field = null ) {
        $raw = isset( $value[0] ) ? $value[0] : '';

        // Safely unserialize if needed. If not serialized, returns the original value.
        $maybe = maybe_unserialize( $raw );

        // Normalize legacy formats (plain URL or "url,text,target") into an array.
        if ( is_string( $maybe ) ) {
            $maybe = cfg_normalize_hyperlink_value( $maybe, $field );
        }

        return is_array( $maybe ) ? $maybe : array();
    }


    function format_value_for_api( $value, $field = null ) {
        $url    = isset( $value['url'] ) ? $value['url'] : '';
        $text   = isset( $value['text'] ) ? $value['text'] : $url;
        $target = isset( $value['target'] ) ? $value['target'] : '';
        $format = $this->get_option( $field, 'format', 'html' );
        
        // target="none" (sometimes?) opens a new tab
        if ( 'none' == $target ) {
            $target = '';
        }

        // Return an HTML string
        if ( 'html' == $format ) {
            $output = '';
            if ( ! empty( $url ) ) {
                $output = '<a class="cfgroup-hyperlink" href="' . esc_url( $url ) . '" target="' . $target . '"><span class="text">' . esc_html( $text ) . '</span></a>';
            }
        }

        // Return an associative array
        elseif ( 'php' == $format ) {
            $output = $value;
        }

        return $output;
    }

    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }
}
