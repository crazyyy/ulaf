<?php
/**
 * Custom fields for WordPress Menus admin section
 *
 * @package Alchemists
 * @version 1.0.0
 */


/**
 *
 * This class demonstrate the usage of Menu Item Custom Fields in plugins/themes.
 *
 * @since 0.1.0
 */
class Menu_Item_Custom_Fields_Example {

	/**
	 * Holds our custom fields
	 *
	 * @var    array
	 * @access protected
	 * @since  1.0.0
	 */
	protected static $fields = array();


	/**
	 * Initialize plugin
	 */
	public static function init() {
		add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, '_fields' ), 10, 4 );
		add_action( 'wp_update_nav_menu_item', array( __CLASS__, '_save' ), 10, 3 );

		self::$fields = array(
			array(
				'key'		      => 'megamenu',
				'type'		    => 'checkbox',
				'label' 	    => esc_html__( 'Mega Menu?', 'alchemists' ),
				'description' => ''
			),
			array(
				'key'		      => 'megamenu_widgetarea',
				'type'		    => 'widget_area',
				'label' 	    => esc_html__( 'Mega Menu Widget Area', 'alchemists' ),
				'description' => ''
			),
		);
	}


	/**
	 * Save custom field value
	 *
	 * @wp_hook action wp_update_nav_menu_item
	 *
	 * @param int   $menu_id         Nav menu ID
	 * @param int   $menu_item_db_id Menu item ID
	 * @param array $menu_item_args  Menu item data
	 */
	public static function _save( $menu_id, $menu_item_db_id, $menu_item_args ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

		foreach ( self::$fields as $field ) {
			$key = sprintf( '_menu_item_%s', $field["key"] );

			// Sanitize
			if ( ! empty( $_POST[ $key ][ $menu_item_db_id ] ) ) {
				// Do some checks here...
				$value = $_POST[ $key ][ $menu_item_db_id ];
			}
			else {
				$value = null;
			}

			// Update
			if ( ! is_null( $value ) ) {
				update_post_meta( $menu_item_db_id, $key, $value );
			}
			else {
				delete_post_meta( $menu_item_db_id, $key );
			}
		}
	}


	/**
	 * Print field
	 *
	 * @param object $item  Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args  Menu item args.
	 * @param int    $id    Nav menu ID.
	 *
	 * @return string Form fields
	 */
	public static function _fields( $id, $item, $depth, $args ) {
		foreach ( self::$fields as $field) :

			$key   			= sprintf( '_menu_item_%s', $field["key"] );
			$id    			= sprintf( 'edit%s_%s', $key, $item->ID );
			$label  		= $field["label"];
			$description  	= $field["description"];
			$name  			= sprintf( '%s[%s]', $key, $item->ID );
			$value 			= get_post_meta( $item->ID, $key, true );
			$class 			= sprintf( 'field-%s', $field["key"] );

			$field_type = '_field_'.$field["type"];

			 self::{$field_type}($id, $label, $description, $name, $value, $class);

		endforeach;
	}


	/**
	 * Checkbox Field
	 *
	 * @param string    $id  			Unique field id.
	 * @param string  	$label  		field title.
	 * @param string    $description  	field short desciption.
	 * @param string    $name   		field form element name attribute.
	 * @param string    $value    		field option value.
	 * @param string    $class    		field class.
	 *
	 * @return string Form fields
	 */

	public static function _field_checkbox($id, $label, $description, $name, $value, $class) {
		$field_value = ($value != "") ? "checked='checked'" : '';
			?>
			<p class="description description-wide <?php echo esc_attr( $class ); ?>">
				<label for="<?php echo esc_attr( $id ); ?>">
					<input type="checkbox" value="enabled" class="edit-menu-item-alchemists-megamenu-check" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php echo esc_html( $field_value ); ?> />
					<strong><?php echo esc_html( $label ); ?></strong>
				</label>
			</p>

		<?php
	}


	/**
	 * Select Widget Area
	 *
	 * @param string    $id  			Unique field id.
	 * @param string  	$label  		field title.
	 * @param string    $description  	field short desciption.
	 * @param string    $name   		field form element name attribute.
	 * @param string    $value    		field option value.
	 * @param string    $class    		field class.
	 *
	 * @return string Form fields
	 */

	public static function _field_widget_area($id, $label, $description, $name, $value, $class) {
		global $wp_registered_sidebars;
		?>

		<p class="description description-wide <?php echo esc_attr( $class ) ?>">
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php echo esc_html( $label ); ?>
				<select id="<?php echo esc_attr( $id ); ?>" class="widefat code <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<option value="0"><?php esc_html_e( 'Select Widget Area', 'alchemists' ); ?></option>
					<?php
					if( ! empty( $wp_registered_sidebars ) && is_array( $wp_registered_sidebars ) ):
					foreach( $wp_registered_sidebars as $sidebar ):
					?>
					<option value="<?php echo esc_attr( $sidebar['id'] ); ?>" <?php selected( esc_attr( $value ), $sidebar['id'] ); ?>><?php echo esc_html( $sidebar['name'] ); ?></option>
					<?php endforeach; endif; ?>
				</select>
			</label>
		</p>

		<?php
	}

}
Menu_Item_Custom_Fields_Example::init();
