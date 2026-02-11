<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * @var $values
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Content_Filter
 */

$values = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = '';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$values = (array) vc_param_group_parse_atts( $values );
$values_array = array();
foreach ($values as $data) {
  $new_link = $data;
  $new_link['item_label']     = isset( $data['item_label'] ) ? $data['item_label'] : '';
  $new_link['item_label_2']   = isset( $data['item_label_2'] ) ? $data['item_label_2'] : '';
  $new_link['item_link']      = isset( $data['item_link'] ) ? $data['item_link'] : '';
  $new_link['item_is_active'] = isset( $data['item_is_active'] ) ? $data['item_is_active'] : '';

  $values_array[] = $new_link;
}

?>


<!-- Content Filter -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

  <nav class="content-filter">
    <div class="container">
      <a href="#" class="content-filter__toggle"></a>
      <ul class="content-filter__list">

        <?php if (!empty($values_array)) : ?>
          <?php foreach ( $values_array as $item_line ) : ?>
            <?php if( !empty($item_line['item_label']) ) : ?>

              <?php
              $item_label   = $item_line['item_label'];
              $item_label_2 = $item_line['item_label_2'];
              $item_link    = $item_line['item_link'];
              $item_active  = $item_line['item_is_active']; ?>

              <li class="content-filter__item <?php if ( !empty( $item_active )) { echo 'content-filter__item--active'; }; ?>">
                <a href="<?php echo esc_url( $item_link ); ?>" class="content-filter__link">

                  <?php if ( !empty( $item_label_2 )) : ?>
                  <small><?php echo esc_html( $item_label_2 ); ?></small>
                  <?php endif; ?>

                  <?php echo esc_html( $item_label ); ?>
                </a>
              </li>

            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>


      </ul>
    </div>
  </nav>
</div>
<!-- Content Filter / End -->
