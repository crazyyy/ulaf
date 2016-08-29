<?php

class acf_qtranslate_acf_5_file extends acf_field_file {

	/**
	 * The plugin instance.
	 * @var \acf_qtranslate_plugin
	 */
	protected $plugin;


	/*
	 *  __construct
	 *
	 *  This function will setup the field type data
	 *
	 *  @type	function
	 *  @date	5/03/2014
	 *  @since	5.0.0
	 *
	 *  @param	n/a
	 *  @return	n/a
	 */
	function __construct($plugin) {
		$this->plugin = $plugin;

		$this->name = 'qtranslate_file';
		$this->label = __("File", 'acf');
		$this->category = __("qTranslate", 'acf');
		$this->defaults = array(
			'return_format' => 'array',
			'library'       => 'all',
			'min_size'      => 0,
			'max_size'      => 0,
			'mime_types'    => ''
		);
		$this->l10n  = array(
			'select'     => __("Select File",'acf'),
			'edit'       => __("Edit File",'acf'),
			'update'     => __("Update File",'acf'),
			'uploadedTo' => __("uploaded to this post",'acf'),
		);


		// filters
		add_filter('get_media_item_args',			array($this, 'get_media_item_args'));
		add_filter('wp_prepare_attachment_for_js',	array($this, 'wp_prepare_attachment_for_js'), 10, 3);

		acf_field::__construct();
	}

	/*
	 *  render_field()
	 *
	 *  Create the HTML interface for your field
	 *
	 *  @param	$field - an array holding all the field's data
	 *
	 *  @type	action
	 *  @since	3.6
	 *  @date	23/01/13
	 */
	function render_field($field) {
		global $q_config;
		$languages = qtrans_getSortedLanguages(true);
		$values = qtrans_split($field['value'], $quicktags = true);
		$currentLanguage = $this->plugin->get_active_language();

		// enqueue
		acf_enqueue_uploader();

		// vars
		$o = array(
			'icon'		=> '',
			'title'		=> '',
			'size'		=> '',
			'url'		=> '',
			'name'		=> '',
		);

		$div = array(
			'class'				=> 'acf-file-uploader acf-cf',
			'data-library' 		=> $field['library'],
			'data-mime_types'	=> $field['mime_types']
		);

		$input_atts = array(
			'type'					=> 'hidden',
			'name'					=> $field['name'],
			'value'					=> $field['value'],
			'data-name'				=> 'value-id'
		);

		$url = '';

		echo '<div class="multi-language-field multi-language-field-image">';

		foreach ($languages as $language) {
			$class = 'wp-switch-editor';
			if ($language === $currentLanguage) {
				$class .= ' current-language';
			}
			echo '<a class="' . $class . '" data-language="' . $language . '">' . $q_config['language_name'][$language] . '</a>';
		}

		foreach ($languages as $language):

			$input_atts['name'] = $field['name'] . '[' . $language . ']';
			$field['value'] = $values[$language];
			$div['data-language'] = $language;
			$div['class'] = 'acf-file-uploader acf-cf';

			// has value?
			if( $field['value'] && is_numeric($field['value']) ) {
				$file = get_post( $field['value'] );
				if( $file ) {
					$div['class'] .= ' has-value';

					$o['icon'] = wp_mime_type_icon( $file->ID );
					$o['title']	= $file->post_title;
					$o['size'] = @size_format(filesize( get_attached_file( $file->ID ) ));
					$o['url'] = wp_get_attachment_url( $file->ID );

					$explode = explode('/', $o['url']);
					$o['name'] = end( $explode );
				}
			}

			// basic?
			$basic = !current_user_can('upload_files');
			if ($basic) {
				$div['class'] .= ' basic';
			}

			if ($language === $currentLanguage) {
				$div['class'] .= ' current-language';
			}

			?>
			<div <?php acf_esc_attr_e($div); ?>>
				<div class="acf-hidden">
					<?php acf_hidden_input(array( 'name' => $input_atts['name'], 'value' => $field['value'], 'data-name' => 'id' )); ?>
				</div>
				<div class="show-if-value file-wrap acf-soh">
					<div class="file-icon">
						<img data-name="icon" src="<?php echo $o['icon']; ?>" alt=""/>
					</div>
					<div class="file-info">
						<p>
							<strong data-name="title"><?php echo $o['title']; ?></strong>
						</p>
						<p>
							<strong><?php _e('File Name', 'acf'); ?>:</strong>
							<a data-name="name" href="<?php echo $o['url']; ?>" target="_blank"><?php echo $o['name']; ?></a>
						</p>
						<p>
							<strong><?php _e('File Size', 'acf'); ?>:</strong>
							<span data-name="size"><?php echo $o['size']; ?></span>
						</p>

						<ul class="acf-hl acf-soh-target">
							<?php if( !$basic ): ?>
								<li><a class="acf-icon dark" data-name="edit" href="#"><i class="acf-sprite-edit"></i></a></li>
							<?php endif; ?>
							<li><a class="acf-icon dark" data-name="remove" href="#"><i class="acf-sprite-delete"></i></a></li>
						</ul>
					</div>
				</div>
				<div class="hide-if-value">
					<?php if( $basic ): ?>

						<?php if( $field['value'] && !is_numeric($field['value']) ): ?>
							<div class="acf-error-message"><p><?php echo $field['value']; ?></p></div>
						<?php endif; ?>

						<input type="file" name="<?php echo $field['name']; ?>" id="<?php echo $field['id']; ?>" />

					<?php else: ?>

						<p style="margin:0;"><?php _e('No File selected','acf'); ?> <a data-name="add" class="acf-button" href="#"><?php _e('Add File','acf'); ?></a></p>

					<?php endif; ?>

				</div>
			</div>

		<?php endforeach;

		echo '</div>';
	}

	/*
	 *  update_value()
	 *
	 *  This filter is appied to the $value before it is updated in the db
	 *
	 *  @type	filter
	 *  @since	3.6
	 *  @date	23/01/13
	 *
	 *  @param	$value - the value which will be saved in the database
	 *  @param	$post_id - the $post_id of which the value will be saved
	 *  @param	$field - the field array holding all the field options
	 *
	 *  @return	$value - the modified value
	 */
	function update_value($value, $post_id, $field) {
		$value = parent::update_value($value, $post_id, $field);
		return qtrans_join($value);
	}

}
