<?php
namespace AdminEase;

defined( 'ABSPATH' ) || exit;

/**
 * Class Field
 * This class represents an HTML form field with support for various types of inputs,
 * labels, descriptions, and attributes.
 */
class Field {
	protected $type;
	protected $id;
	protected $wrapper_class;
	protected $input_class;
	protected $label_class;
	protected $label;
	protected $name;
	protected $value;
	protected $description;
	protected $field_description;
	protected $placeholder;
	protected $options;
	protected $has_optgroups;
	protected $attributes;
	protected array $allowed_html;
	
	/**
	 * Constructor to initialize the class with specified arguments.
	 *
	 * @param array $args {
	 *     Optional. Array of arguments to define the properties.
	 *
	 * @type string $type The type of the input field. Default 'text'.
	 * @type string $id The ID attribute of the input field. Default ''.
	 * @type string $wrapper_class CSS class for the wrapper element. Default ''.
	 * @type string $input_class CSS class for the input field. Default ''.
	 * @type string $label_class CSS class for the label element. Default ''.
	 * @type string $label The label text for the input field. Default ''.
	 * @type string $name The name attribute of the input field. Default ''.
	 * @type mixed  $value The value of the input field. Default ''.
	 * @type string $description Long descriptive text for the input field. Default ''.
	 * @type string $field_description Descriptive text for the input field. Default ''.
	 * @type array  $options The options of the input field. Default [].
	 * @type bool   $has_optgroups Whether the select field has optgroups. Default false.
	 * @type array  $attributes Additional attributes for the input field. Default [].
	 * }
	 * @return void
	 */
	public function __construct( array $args = [] ) {
		$defaults = [
			'type'              => 'text',
			'id'                => '',
			'wrapper_class'     => '',
			'input_class'       => '',
			'label_class'       => '',
			'label'             => '',
			'name'              => '',
			'value'             => '',
			'placeholder'       => '',
			'description'       => '',
			'field_description' => '',
			'options'           => [],
			'has_optgroups'     => false,
			'attributes'        => [],
		];
		
		$args = wp_parse_args( $args, $defaults );
		
		$this->type              = $args['type'];
		$this->id                = $args['id'];
		$this->wrapper_class     = $args['wrapper_class'];
		$this->input_class       = $args['input_class'];
		$this->label             = $args['label'];
		$this->label_class       = $args['label_class'];
		$this->name              = $args['name'];
		$this->description       = $args['description'];
		$this->field_description = $args['field_description'];
		$this->value             = $args['value'];
		$this->placeholder       = $args['placeholder'];
		$this->options           = $args['options'];
		$this->has_optgroups     = $args['has_optgroups'];
		$this->attributes        = $args['attributes'];
		$this->allowed_html      = [
			'a'      => [
				'id'     => [],
				'class'  => [],
				'href'   => [
					'javascript:void(0);' => [],
				],
				'title'  => [],
				'target' => [],
				'rel'    => [],
			],
			'span'   => [
				'class' => [],
			],
			'p'      => [],
			'br'     => [],
			'em'     => [],
			'strong' => [],
			'small'  => [],
			'ul'     => [],
			'ol'     => [],
			'li'     => [],
		];
	}
	
	/**
	 * Renders an HTML form field based on the specified type and attributes.
	 * This method dynamically generates the appropriate form field (e.g., switch, textarea, number, text)
	 * including its label, input elements, and additional descriptive text if provided.
	 * @return void Outputs the generated HTML form field directly.
	 */
	public function render(): void {
		$attr = $this->build_attributes();
		?>
	<div class="form-group<?php echo !empty( $this->wrapper_class ) ? ' ' . esc_attr( $this->wrapper_class ) : ''; ?>">
		<?php
		switch( $this->type ) {
			case 'switch':
			{
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->label_class ); ?>">
					<input type="checkbox" class="<?php echo esc_attr( $this->input_class ); ?>" id="<?php echo esc_attr( $this->id ); ?>" <?php checked( $this->value, 1 ); ?> <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>>
					<input type="hidden" name="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_attr( intval( $this->value ) ); ?>">
					<span class="adminease-slider"></span>
					<span class="adminease-switch-label"><?php echo wp_kses( $this->label, $this->allowed_html ); ?></span>
				</label>
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				
				break;
			}
			case 'textarea':
			{
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->label_class ); ?>"><?php echo wp_kses( $this->label, $this->allowed_html ); ?></label>
				<textarea id="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>" rows="5" cols="50" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>><?php echo esc_textarea( $this->value ); ?></textarea>
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				break;
			}
			case 'number':
			{
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->label_class ); ?>"><?php echo wp_kses( $this->label, $this->allowed_html ); ?></label>
				<input type="number" id="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_attr( $this->value ); ?>" placeholder="<?php echo esc_attr( $this->placeholder ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?> />
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				break;
			}
			case 'text':
			{
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->label_class ); ?>"><?php echo wp_kses( $this->label, $this->allowed_html ); ?></label>
				<input type="text" id="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_attr( $this->value ); ?>" placeholder="<?php echo esc_attr( $this->placeholder ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?> />
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				break;
			}
			case 'date':
			{
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->label_class ); ?>"><?php echo wp_kses( $this->label, $this->allowed_html ); ?></label>
				<input type="date" id="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_attr( $this->value ); ?>" placeholder="<?php echo esc_attr( $this->placeholder ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?> />
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				break;
			}
			case 'colorpicker':
			{
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->label_class ); ?>"><?php echo wp_kses( $this->label, $this->allowed_html ); ?></label>
				<input type="color" id="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_attr( $this->value ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?> />
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				break;
			}
			case 'select':
			{
				?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->label_class ); ?>"><?php
					echo wp_kses( $this->label, $this->allowed_html );
					
					if( !empty( $this->attributes['data-allow_clear'] ) && !empty( $this->attributes['multiple'] ) ) {
						?>
						<a class="clear-selected-choices"><?php esc_html_e( 'Clear all', 'adminease' ); ?></a>
						<?php
					}
					
					if( !empty( $this->attributes['data-allow_select_all'] ) ) {
						?>
						<a class="select-all-choices"><?php esc_html_e( 'Select all', 'adminease' ); ?></a>
						<?php
					}
					?></label>
				<select id="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>" placeholder="<?php echo esc_attr( $this->placeholder ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>>
					<?php
					if( $this->has_optgroups ) {
						// Render with optgroups
						foreach( $this->options as $optgroup_label => $optgroup_options ) {
							?>
							<optgroup label="<?php echo esc_attr( $optgroup_label ); ?>">
								<?php
								foreach( $optgroup_options as $option_value => $option_label ) {
									$is_selected = is_array( $this->value ) ? in_array( $option_value, $this->value ) : $this->value == $option_value;
									?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php echo $is_selected ? 'selected' : ''; ?>><?php echo esc_html( $option_label ); ?></option>
									<?php
								}
								?>
							</optgroup>
							<?php
						}
					} else {
						// Render regular options
						foreach( $this->options as $key => $value ) {
							$is_selected = is_array( $this->value ) ? in_array( $key, $this->value ) : $this->value == $key;
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php echo $is_selected ? 'selected' : ''; ?>><?php echo esc_html( $value ); ?></option>
							<?php
						}
					}
					?>
				</select>
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				
				break;
			}
			case 'button':
			{
				?>
				<button type="button" id="<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $this->input_class ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>><?php echo wp_kses( $this->label, $this->allowed_html ); ?></button>
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				
				break;
			}
			case 'date_range':
			{
				?>
				<label class="<?php echo esc_attr( $this->label_class ); ?>"><?php echo wp_kses( $this->label, $this->allowed_html ); ?></label>
				
				<div class="flex justify-space-between align-center m-b-5">
					<div class="col">
						<label for="<?php echo esc_attr( $this->id ); ?>_from" class="m-b-0"><?php esc_html_e( 'From', 'adminease' ); ?></label>
						<input type="date" id="<?php echo esc_attr( $this->id ); ?>_from" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>_from" value="<?php echo esc_attr( $this->value['from'] ?? '' ); ?>" placeholder="<?php echo esc_attr( 'From' ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?> />
					</div>
					
					<div class="col">
						<label for="<?php echo esc_attr( $this->id ); ?>_to" class="m-b-0"><?php esc_html_e( 'To', 'adminease' ); ?></label>
					<input type="date" id="<?php echo esc_attr( $this->id ); ?>_to" class="<?php echo esc_attr( $this->input_class ); ?>" name="<?php echo esc_attr( $this->name ); ?>_to" value="<?php echo esc_attr( $this->value['to'] ?? '' ); ?>" placeholder="<?php echo esc_attr( 'To' ); ?>" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?> />
					</div>
				</div>
				<?php
				if( !empty( $this->field_description ) ) {
					?>
					<p class="field-description"><?php echo wp_kses( $this->field_description, $this->allowed_html ); ?></p>
					<?php
				}
				break;
			}
		}
		?>
		</div>
		<?php
	}
	
	/**
	 * Build additional HTML attributes.
	 * @return string
	 */
	protected function build_attributes(): string {
		$attributes = '';
		
		if( !empty( $this->attributes ) && is_array( $this->attributes ) ) {
			foreach( $this->attributes as $key => $value ) {
				if( 'disabled' === $key && false === $value ) {
					continue;
				}
				
				$attributes .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}
		}
		
		return $attributes;
	}
}