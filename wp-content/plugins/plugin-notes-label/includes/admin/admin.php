<?php
/*
 * WPGear. 
 * Plugin Notes Label
 * admin.php
 */
 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	/* Create plugin SubMenu
	----------------------------------------------------------------- */		
	add_action('admin_menu', 'PluginNotesLabel_Action_admin_menu');	
	function PluginNotesLabel_Action_admin_menu() {
		add_options_page(
			'Plugin Notes Label',
			__('Plugin Notes Label', 'plugin-notes-label'),
			'publish_posts',
			'plugin-notes-label/includes/admin/options.php',
			''
		);	
	}
	
	/* Admin Console - Styles.
	----------------------------------------------------------------- */	
	add_action ('admin_enqueue_scripts', 'PluginNotesLabel_Action_admin_style' );	
	function PluginNotesLabel_Action_admin_style ($hook) {
		$PluginNotesLabel_Nonce = 'AJAX_Processing Plugin Notes Label';
		$nonce = wp_create_nonce ($PluginNotesLabel_Nonce);		
		
		$screen = get_current_screen();
		$screen_base = $screen -> base;			
	
		if ($screen_base == 'plugins') {
			// Страница Плагины.
			global $PluginNotesLabel_plugin_url;
			global $PluginNotesLabel_Setup_ShowAuthor, $PluginNotesLabel_Setup_ShowDate;
			
			$current_user = wp_get_current_user();	
			$User_Name = $current_user -> user_login;			
			
			wp_enqueue_style ('plugin_note_label_style', $PluginNotesLabel_plugin_url .'includes/admin/admin-style.css'); // phpcs:ignore

			wp_enqueue_script ('plugin_note_label', $PluginNotesLabel_plugin_url .'includes/plugin_note_label.js'); // phpcs:ignore		
			wp_localize_script ('plugin_note_label', 'PluginNotesLabel_VarObject', array( 
				'user' => $User_Name,
				'show_author' => $PluginNotesLabel_Setup_ShowAuthor,
				'show_date' => $PluginNotesLabel_Setup_ShowDate,
				'_wpnonce' => $nonce,
			)); // phpcs:ignore	
		}
		
		if ($screen_base == 'plugin-notes-label/includes/admin/options') {
			// Страница Настрока "Plugin Notes Label"
			global $PluginNotesLabel_plugin_url;
			
			wp_enqueue_style ('plugin_note_label_option_style', $PluginNotesLabel_plugin_url .'includes/admin/option-style.css'); // phpcs:ignore
		}

		if ($screen_base == 'update-core') {	
			// Страница Обновлений.			
			$NoAction = isset($_REQUEST['action']) ? false : true; // phpcs:ignore	

			if ($NoAction) {				
				// Не запускаемся, если запущен процесс Обновления.
				global $PluginNotesLabel_plugin_url;
				global $PluginNotesLabel_Setup_ShowAuthor, $PluginNotesLabel_Setup_ShowDate;

				$current_user = wp_get_current_user();	
				$User_Name = $current_user->user_login;

				wp_enqueue_style ('plugin_note_label_style', $PluginNotesLabel_plugin_url .'includes/admin/admin-style.css'); // phpcs:ignore

				wp_enqueue_script ('plugin_note_label_updatecore', $PluginNotesLabel_plugin_url .'includes/plugin_note_label_updatecore.js'); // phpcs:ignore				
				wp_localize_script ('plugin_note_label_updatecore', 'PluginNotesLabel_VarObject', array( 
					'user' => $User_Name,
					'show_author' => $PluginNotesLabel_Setup_ShowAuthor,
					'show_date' => $PluginNotesLabel_Setup_ShowDate,
					'_wpnonce' => $nonce,
				)); // phpcs:ignore	

				wp_enqueue_script ('plugin_note_label', $PluginNotesLabel_plugin_url .'includes/plugin_note_label.js'); // phpcs:ignore
				wp_localize_script ('plugin_note_label', 'PluginNotesLabel_VarObject', array( 
					'user' => $User_Name,
					'show_author' => $PluginNotesLabel_Setup_ShowAuthor,
					'show_date' => $PluginNotesLabel_Setup_ShowDate,
					'_wpnonce' => $nonce,
				)); // phpcs:ignore
			}
		}
	}	
	
	/* Admin Console - Plugins page.
	----------------------------------------------------------------- */		
	add_action('after_plugin_row', 'PluginNotesLabel_Action_after_plugin_row', 999999, 3);
	function PluginNotesLabel_Action_after_plugin_row ($plugin_file, $plugin_data, $status) {
		global $PluginNotesLabel_Setup_ShowAuthor, $PluginNotesLabel_Setup_ShowDate;
		
		$PluginData_Name = $plugin_data['Name'];
		$PluginData_Slug = isset ($plugin_data['slug']) ? $plugin_data['slug'] : sanitize_title ($PluginData_Name);
		
		$Plugin_Note = get_option("plugin-note-label_$PluginData_Slug", '');
		
		$Plugin_Note_Title = "";		

		if (is_array($Plugin_Note)) {
			$Plugin_Note_Content 	= $Plugin_Note['content'];
			$Plugin_Note_User 		= $Plugin_Note['user'];
			$Plugin_Note_Date 		= $Plugin_Note['date'];
			
			$Plugin_Note_Label = "Note";
			
			if ($Plugin_Note_Content) {
				if ($PluginNotesLabel_Setup_ShowAuthor) {
					$Plugin_Note_Label .= " [" .esc_html( $Plugin_Note_User ) ."]";
				}

				if ($PluginNotesLabel_Setup_ShowDate) {
					$Plugin_Note_Label .= " <span class='pluginnotelabel-label-date'>" .esc_html( $Plugin_Note_Date ) ."</span>";
				}	
			}

			$Plugin_Note_Label .= ":";
			
			if (!$PluginNotesLabel_Setup_ShowAuthor && !$PluginNotesLabel_Setup_ShowDate) {
				$Plugin_Note_Title = "Note by [$Plugin_Note_User] $Plugin_Note_Date";
			}
		} else {
			// нет данных или самая ранняя версия.
			$Plugin_Note_Content = $Plugin_Note;
			
			$Plugin_Note_Label = "Note:";
		}		
		
		$is_active = intval( is_plugin_active( $plugin_file ) );		
		
		if ($PluginData_Slug) {			
			if ($is_active) {
				$PluginNote2_Box_Class = "pluginnotelabel-box-active";
			} else {
				$PluginNote2_Box_Class = "pluginnotelabel-box-inactive";
			}
		
			ob_start();
				?>
				<tr id='pluginnotelabel-box_<?php echo esc_html( $PluginData_Slug ); ?>'>
					<td colspan='100%' class='pluginnotelabel-box <?php echo esc_html( $PluginNote2_Box_Class ); ?>'>
						<span id='pluginnotelabel_control_<?php echo esc_html( $PluginData_Slug ); ?>' class='pluginnotelabel-label' title='Click to Edit Note' onclick='plugin_note_label_edit("<?php echo esc_html( $PluginData_Slug ); ?>", "<?php echo esc_html( $Plugin_Note_Content ); ?>")'>
							<?php echo $Plugin_Note_Label; // phpcs:ignore	?>
						</span>
						<span id='pluginnotelabel_<?php echo esc_html( $PluginData_Slug ); ?>' class='pluginnotelabel-content' title='<?php echo esc_html( $Plugin_Note_Title ); ?>'>
							<?php echo esc_html( $Plugin_Note_Content ); ?>
						</span>
					</td>
				</tr>
				<?php
			ob_end_flush();
		}
	}		