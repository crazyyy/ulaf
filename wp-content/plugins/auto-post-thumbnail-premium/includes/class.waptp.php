<?php

namespace WBCR\APT;

use Exception;
use WBCR\APT\Processing;

/**
 * Core plugin class
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020, Creative Motion
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAPT_Premium {

	/**
	 * @var WAPT_Premium
	 */
	private static $app;

	/**
	 * @var AutoPostThumbnails
	 */
	private $WAPT;

	/**
	 * @var \WAPT_Plugin
	 */
	private $plugin;

	/**
	 * @var Processing
	 */
	public $processing;

	/**
	 * @var bool
	 */
	public $doing_processing;

	/**
	 * WAPT_Premium constructor.
	 */
	public function __construct() {
		self::$app    = $this;
		$this->plugin = \WAPT_Plugin::app();

		if ( $this->doing_rest_api() || is_admin() ) {
			add_filter( 'wapt/generate/image', [ $this, 'generate_image_with_text_pro' ], 10, 6 );
		}

		if ( is_admin() ) {
			$this->admin_scripts();
			$this->WAPT = AutoPostThumbnails::instance();
		}

		if ( class_exists( 'Processing' ) ) {
			require_once WAPTP_PLUGIN_DIR . '/includes/class-processing.php';
			$this->processing = new Processing( $this->plugin );
		} else {
			$this->processing = new ProcessingBase();
		}

		$this->doing_processing = $this->plugin->getPopulateOption( 'process_running', false );

		if ( ( defined( 'DOING_CRON' ) && DOING_CRON ) || $this->doing_rest_api() ) {
			$this->WAPT = AutoPostThumbnails::instance();
			require_once WAPT_PLUGIN_DIR . '/includes/class.generate-result.php';
		}

		if ( $this->plugin->is_premium() && $this->plugin->getPopulateOption( 'scheduled_generation', false ) ) {
			$this->init_cron();
		} else {
			$this->deinit_cron();
		}

	}

	/**
	 * Статический метод для быстрого доступа к информации о плагине, а также часто использумых методах.
	 *
	 * @return WAPT_Premium
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @author matzeeable https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist
	 * @since  2.1.0
	 * @return boolean
	 */
	public static function doing_rest_api() {
		$prefix     = rest_get_url_prefix();
		$rest_route = \WAPT_Plugin::app()->request->get( 'rest_route', null );
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
		     || ! is_null( $rest_route ) // (#2)
		        && strpos( trim( $rest_route, '\\/' ), $prefix, 0 ) === 0 ) {
			return true;
		}

		// (#3)
		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( [] ) );

		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}

	/**
	 * Регистрируем страницы плагина
	 */
	private function register_pages() {

	}

	/**
	 * Подключаем функции бэкенда
	 */
	private function admin_scripts() {
		$this->register_pages();
		add_action( 'admin_enqueue_scripts', [
				$this,
				'enqueue_assets',
		] );


		add_filter( 'wapt/settings/form_options', [ $this, 'setOptions' ], 10, 1 );
		add_filter( 'wapt/sources', [ $this, 'setSources' ], 10, 2 );
		add_filter( 'wapt/get-thumbnails/action', [ $this, 'apt_get_thumbnail_action' ], 10, 1 );
		add_filter( 'wapt/generate_post_thumb', [ $this, 'apt_generate_post_thumb' ], 10, 2 );
		add_filter( 'wapt/download_from_google', [ $this, 'apt_download_from_google' ], 10, 3 );

		if ( \WAPT_Plugin::app()->is_premium() ) {
			add_filter( 'wapt/filter_form_print', [ $this, 'aptp_filter_form' ], 10 );

			add_filter( 'wapt/upload_and_replace_post_images', [
					$this,
					'upload_and_replace_post_images',
			], 10, 1 );
		}

		add_action( 'wp_ajax_apt_get_thumbnail_pro', [ $this, 'apt_get_thumbnail_pro' ] );

		//APIs
		add_action( 'wp_ajax_apt_api_watson', [ $this, 'apt_api_watson' ] );
		add_action( 'wp_ajax_apt_api_pixabay', [ $this, 'apt_api_pixabay' ] );
		add_action( 'wp_ajax_apt_api_unsplash', [ $this, 'apt_api_unsplash' ] );
		add_action( 'wp_ajax_aptp_check_api_key', [ $this, 'aptp_check_api_key' ] );

		add_action( 'wp_ajax_remove_post_watson_categories', [ $this, 'remove_post_watson_categories' ] );
	}

	/**
	 * Enqueue assets.
	 *
	 * @param $hook_suffix
	 *
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		//Подключаем стили и скрипты всегда в админке
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'apt-admin-column-thumbnail', WAPTP_PLUGIN_URL . '/admin/assets/js/column-thumbnail.js', [], false, true );
		wp_enqueue_script( 'aptp-admin-check_api', WAPTP_PLUGIN_URL . '/admin/assets/js/check-api.js', [], false, true );
	}

	/**
	 * Назначение обработчика AJAX запроса на вывод картинок
	 *
	 * @return string
	 */
	public function apt_get_thumbnail_action( $action ) {
		return "apt_get_thumbnail_pro";
	}

	/**
	 * Фильтр добавляет картинку по ссылке в медиабиблиотеку и возвращает ID
	 *
	 * @param array $image
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function apt_generate_post_thumb( $image, $post_id ) {
		$thumb_id = $this->WAPT->generate_post_thumb( $image['url'], $image['title'], $post_id );

		return $thumb_id;
	}

	/**
	 * Фильтр ищет изображение в Google по заголовку поста и возвращает ID
	 *
	 * @param $thumb_id
	 * @param $images
	 * @param $post_id
	 *
	 * @return int
	 */
	public function apt_download_from_google( $thumb_id, $images, $post_id ) {
		$thumb_id = $this->download_and_attachment( $post_id, $images );

		return $thumb_id;
	}

	/**
	 * Фильтр ищет изображение в Google по заголовку поста и возвращает ID
	 *
	 * @param array|int $post_data
	 *
	 * @return array|void
	 */
	public function upload_and_replace_post_images( $post_data ) {
		if ( is_numeric( $post_data ) ) {
			$images = new PostImagesPro( $post_data );
			$images->upload_and_replace_images();
			wp_update_post( $images->get_post() );
		} else if ( isset( $post_data['post_content'] ) && ! empty( $post_data['post_content'] ) ) {
			$images  = new PostImagesPro( wp_unslash( $post_data['post_content'] ) );
			$content = $images->upload_and_replace_images();

			$post_data['post_content'] = wp_slash( $content );
		}

		return $post_data;
	}

	/**
	 * Генерация изображения
	 *
	 * @param integer $post_id
	 * @param GoogleFoundedImage $images
	 *
	 * @return integer $thumb_id
	 */
	public function download_and_attachment( $post_id, $images ) {
		$thumb_id = 0;
		$post     = get_post( $post_id, 'OBJECT' );
		$uploads  = wp_upload_dir( current_time( 'mysql' ) );

		foreach ( $images as $image ) {
			$extension = $image->file->ext;
			$mime_type = $image->image->mime;

			// Generate unique file name
			$slug      = wp_unique_post_slug( $post->post_title, $post->ID, $post->post_status, $post->post_type, $post->post_parent );
			$file_path = wp_unique_filename( $uploads['path'], "{$slug}_{$post_id}.{$extension}" );
			$file_path = "{$uploads['path']}/{$file_path}";

			// Move the file to the uploads dir
			if ( $image->download( $file_path ) ) {
				$thumb_id = $this->WAPT::insert_attachment( $post, $file_path, $mime_type );
			} else {
				continue;
			}

			if ( is_wp_error( $thumb_id ) ) {
				@unlink( $file_path );
			} else {
				break;
			}
		}

		return $thumb_id;
	}


	/**
	 * Используется для динамической загрузки изображений поста в окно выбора
	 *
	 * @return array|bool
	 * @uses apt_thumb
	 */
	public function apt_get_thumbnail_pro() {
		if ( isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) {
			$post_id = intval( $_POST['post_id'] );
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_die( - 1 );
			} else {
				check_ajax_referer( 'set_post_thumbnail-' . $post_id );
				$nonce = $_POST['_ajax_nonce'];

				$images = new \WBCR\APT\PostImages( $post_id );
				if ( $images->is_images() && $images->count_images() ) {
					echo "<div>" . __( "These images we found in the text of the post.", "aptp" ) . "</div>";
				} else {
					echo "<div>" . __( "There are no images in the post text", "aptp" ) . "</div>";
				}
				?>
				<div class='apt_thumbs' id='wapt_thumbs'>
					<?php foreach ( $images->get_images() as $image ) {
						$thumb_id = $this->WAPT->get_thumbnail_id( $image ); ?>
						<div class='wapt-grid-item'>
							<div class="wapt-image-box"
							     data-thumbid='<?php echo $thumb_id; ?>'
							     data-postid='<?php echo $post_id; ?>'
							     data-nonce='<?php echo $nonce; ?>'
							     data-src='<?php echo $image['url']; ?>'
							     data-feature=""
							     style="background: url('<?php echo $image['url']; ?>') center center no-repeat; background-size: cover;">
							</div>
						</div>
						<?php
					}
					?>
					<div class='wapt-grid-item'>
						<div class="wapt-image-box-library"
						     data-choose='<?php echo __( 'Choose featured image', 'apt' ); ?>'
						     data-update='<?php echo __( 'Select image', 'apt' ); ?>'
						     data-postid='<?php echo $post_id; ?>'
						     data-nonce='<?php echo $nonce; ?>'
						     style="background-color: #a3d2ff;">
							<div class="wapt-item-generated"><?php echo __( 'Set featured image from medialibrary', 'apt' ); ?></div>
						</div>
					</div>

					<div class='wapt-grid-item'>
						<div class="wapt-image-box"
						     data-thumbid='-1'
						     data-postid='<?php echo $post_id; ?>'
						     data-nonce='<?php echo $nonce; ?>'
						     data-feature="from_title"
						     style="background-color: #ccc;">
							<div class="wapt-item-generated"><?php echo __( 'Generate featured image from title', 'apt' ) ?></div>
						</div>
					</div>

				</div>

				<div class="wapt-toolbar">
					<div></div>
					<div class="wapt-toolbar-primary">
						<button type="button" id='doit' class='button button-primary button-large'
						        disabled><?php echo __( 'Set featured image', 'aptp' ); ?></button>
					</div>
				</div>
				<?php
			}
		}
		die();
	}

	/**
	 * Генерирует изображение из заголовка поста
	 *
	 * @param string $text
	 * @param string $pathToSave
	 * @param string $format
	 * @param int $width
	 * @param int $height
	 *
	 * @return \WBCR\APT\Image
	 */
	public function generate_image_with_text_pro( $image, $text, $pathToSave = '', $format = 'jpg', $width = 0, $height = 0 ) {
		$font = WAPT_PLUGIN_DIR . "/fonts/" . \WAPT_Plugin::app()->getOption( 'font', "Arial.ttf" );
		if ( ! file_exists( $font ) ) {
			$upload_dir       = wp_upload_dir();
			$upload_dir_fonts = $upload_dir['basedir'] . "/apt_fonts";
			$font             = $upload_dir_fonts . "/" . \WAPT_Plugin::app()->getOption( 'font', "Arial.ttf" );
		}
		$font_size  = \WAPT_Plugin::app()->getOption( 'font-size', 25 );
		$font_color = \WAPT_Plugin::app()->getOption( 'font-color', "#ffffff" );
		if ( $width == 0 ) {
			$width = (int) \WAPT_Plugin::app()->getOption( 'image-width', 800 );
		}
		if ( $height == 0 ) {
			$height = (int) \WAPT_Plugin::app()->getOption( 'image-height', 600 );
		}
		$before_text = \WAPT_Plugin::app()->getOption( 'before-text', '' );
		$after_text  = \WAPT_Plugin::app()->getOption( 'after-text', '' );
		$shadow      = \WAPT_Plugin::app()->getOption( 'shadow', 0 );
		if ( ! $shadow ) {
			$shadow_color = '';
		} else {
			$shadow_color = \WAPT_Plugin::app()->getOption( 'shadow-color', "#ffffff" );
		}

		$background_type = \WAPT_Plugin::app()->getOption( 'background-type', "color" );
		switch ( $background_type ) {
			case 'color':
				$background = \WAPT_Plugin::app()->getOption( 'background-color', "#ff6262" );
				break;
			case 'image':
				$background = \WAPT_Plugin::app()->getOption( 'background-image', '' );
				break;
		}

		$text_transform = \WAPT_Plugin::app()->getOption( 'text-transform', "no" );
		switch ( $text_transform ) {
			case 'upper':
				$text = strtoupper( $text );
				break;
			case 'lower':
				$text = strtolower( $text );
				break;
		}

		$text_crop = \WAPT_Plugin::app()->getOption( 'text-crop', 100 );
		if ( $text_crop > 0 ) {
			if ( strlen( $text ) > $text_crop ) {
				$temp = substr( $text, 0, $text_crop );
				$text = substr( $temp, 0, strrpos( $temp, ' ' ) );
			}

		}

		$align        = \WAPT_Plugin::app()->getOption( 'text-align-horizontal', 'center' );
		$valign       = \WAPT_Plugin::app()->getOption( 'text-align-vertical', 'center' );
		$padding_tb   = \WAPT_Plugin::app()->getOption( 'text-padding-tb', 15 );
		$padding_lr   = \WAPT_Plugin::app()->getOption( 'text-padding-lr', 15 );
		$line_spacing = \WAPT_Plugin::app()->getOption( 'text-line-spacing', 1.5 );


		$image = new \WBCR\APT\Image( $width, $height, $background, $font, $font_size, $font_color );
		$image->setPadding( $padding_lr, $padding_tb );
		$image->write_text( $before_text . $text . $after_text, '', '', '', $align, $valign, $line_spacing, $shadow_color );
		if ( ! empty( $pathToSave ) ) {
			$image->save( $pathToSave, 100, $format );
		}

		return $image;
	}

	/**
	 * Добавляем опции в настройки
	 */
	public function setOptions( $options ) {
		$opt = $options;
		/* PIXABAY */
		$opt[] = [
				'type' => 'html',
				'html' => \WAPT_Page::group_header( __( 'Pixabay API', 'apt' ), __( 'Settings connecting to the Pixabay API service', 'apt' ) ),
		];

		// Текстовое поле
		$opt[] = [
				'type'    => 'textbox',
				'name'    => 'pixabay-apikey',
				'title'   => __( 'API key for Pixabay', 'aptp' ),
				'hint'    => __( 'You can get API key after registration on the site', 'aptp' ) . ' <a href="https://pixabay.com/api/docs/#api_search_images">https://pixabay.com/api/docs</a>',
				'default' => '',
		];

		/* Unsplash */
		$opt[] = [
				'type' => 'html',
				'html' => \WAPT_Page::group_header( __( 'Unsplash API', 'apt' ), __( 'Settings connecting to the Unsplash API service', 'apt' ) ),
		];

		// Текстовое поле
		$opt[] = [
				'type'    => 'textbox',
				'name'    => 'unsplash-apikey',
				'title'   => __( 'Access key for Unsplash Application', 'aptp' ),
				'hint'    => __( 'You can get Access key on the site', 'aptp' ) . ' <a href="https://unsplash.com/developers">https://unsplash.com/developers</a>',
				'default' => '',
		];

		$opt[] = [
				'type' => 'html',
				'html' => \WAPT_Page::group_header( __( 'IBM Watson', 'apt' ), __( 'Settings connecting to the IBM Watson API service', 'apt' ) ),
		];

		$opt[] = [
				'type'    => 'textbox',
				'name'    => 'ibm-watson-apikey',
				'title'   => __( 'API key for IBM Watson', 'aptp' ),
//            'hint' => __( '', 'aptp' ),
				'default' => '',
		];

		$opt[] = [
				'type'    => 'textbox',
				'name'    => 'ibm-watson-endpoint',
				'title'   => __( 'API endpoint for IBM Watson', 'aptp' ),
				'hint'    => __( 'You can get API key and API endpoint on the site', 'aptp' ) . ' <a href="https://cloud.ibm.com/registration?target=/catalog/services/natural-language-understanding%3FhideTours%3Dtrue%26&cm_sp=WatsonPlatform-WatsonPlatform-_-OnPageNavCTA-IBMWatson_NaturalLanguageUnderstanding-_-Watson_Developer_Website">here</a>',
				'default' => '',
		];

		return $opt;
	}

	/**
	 * Добавляем источники
	 *
	 * @param array $sources = [
	 *     'recommend' => '',
	 *     'google' => '',
	 *     'pixabay' => '',
	 *     'unsplash' => '',
	 * ]
	 * @params string $page
	 *
	 * @see AutoPostThumbnails::__construct
	 */
	public function setSources( $sources, $page ) {
		$src             = $sources;
		$src['pixabay']  = WAPTP_PLUGIN_SLUG;
		$src['unsplash'] = WAPTP_PLUGIN_SLUG;

		if ( $page === 'add_to_media_from_apt' ) {
			$src['recommend'] = '_skip';
		} else {
			$src['recommend'] = WAPTP_PLUGIN_SLUG;
		}

		return $src;
	}

	public function remove_post_watson_categories() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'remove_post_watson_categories' ) ) {
			die( 'Error: Invalid request.' );
		}

		$post = get_post( $_POST['post_id'] );
		delete_post_meta( $post->ID, \WAPT_Plugin::app()->getPrefix() . 'watson_categories' );

		wp_send_json_success();
	}

	/**
	 * AJAX accessing IBM Watson to analyze text categories
	 */
	public function apt_api_watson() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'apt_api_watson' ) ) {
			die( 'Error: Invalid request.' );
		}

		$apikey   = \WAPT_Plugin::app()->getPopulateOption( 'ibm-watson-apikey' );
		$endpoint = \WAPT_Plugin::app()->getPopulateOption( 'ibm-watson-endpoint' );

		if ( ! $apikey || ! $endpoint ) {
			wp_send_json_error( [
					'message' => __( 'To use this functionality you must configure access to IBM Watson', 'aptp' ),
			] );
		}

		/** @var WP_Post $post */
		$post = get_post( $_POST['postId'] );

		$response = ( new WAPT_IBMWatson( strip_tags( $post->post_content ) ) )->categories()->analyze();

		wp_send_json_success( [
				'categories' => isset( $response['categories'] ) && is_array( $response['categories'] ) ? $response['categories'] : [],
		] );
	}


	/**
	 * AJAX загрузка выбраного изображения из Pixabay
	 *
	 */
	public function apt_api_pixabay() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'apt_api' ) ) {
			die( 'Error: Invalid request.' );
		}
		if ( isset( $_POST['query'] ) ) {
			if ( isset( $_POST['page'] ) ) {
				$page = $_POST['page'];
			} else {
				$page = 1;
			}

			$post_title = '';
			if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
				$post = get_post( (int) $_POST['post_id'] );
				if ( is_object( $post ) ) {
					$post_title = $post->post_title;
				}
			}

			$query      = isset( $_POST['query'] ) && ! empty( $_POST['query'] ) ? $_POST['query'] : $post_title;
			$image_type = $_POST['image_type'] ?? '';
			$orient     = $_POST['orient'] ?? '';

			try {
				$response = ( new WAPT_Pixabay() )->set_per_page( 20 )->set_image_type( $image_type )->set_orientation( $orient )->search( $query, $query == $post_title ? $page + 1 : $page );

				if ( $response->is_error() ) {
					wp_send_json_error( $response );
				}

				if ( isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
					$response->limit( (int) $_POST['limit'] );
				}

				if ( ! $response->is_error() && isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
					$post = get_post( (int) $_POST['post_id'] );
					if ( $post ) {
						$response2 = ( new WAPT_Pixabay() )->set_per_page( 20 )->set_image_type( $image_type )->set_orientation( $orient )->search( $post->post_title, $page );

						if ( isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
							$response2->limit( (int) $_POST['limit'] );
						}

						$response->images = array_merge( $response2->images, $response->images );
					}
				}
			} catch ( Exception $e ) {
				die( $e->getMessage() );
			}

			if ( $response->is_error() ) {
				wp_send_json_error( $response );
			}
			wp_send_json_success( $response );
		}
	}

	/**
	 * AJAX загрузка выбранного изображения из Unsplash
	 *
	 */
	public function apt_api_unsplash() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'apt_api' ) ) {
			die( 'Error: Invalid request.' );
		}
		if ( isset( $_POST['query'] ) ) {
			$page = $_POST['page'] ?? 1;

			$post_title = '';
			if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
				$post = get_post( (int) $_POST['post_id'] );
				if ( is_object( $post ) ) {
					$post_title = $post->post_title;
				}
			}

			$query  = isset( $_POST['query'] ) && ! empty( $_POST['query'] ) ? $_POST['query'] : $post_title;
			$orient = $_POST['orient'] ?? 'landscape';

			try {
				$response = ( new WAPT_Unsplash() )->set_per_page( 20 )->set_orientation( $orient )->search( $query, $query == $post_title ? $page + 1 : $page );

				if ( isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
					$response->limit( (int) $_POST['limit'] );
				}

				if ( ! $response->is_error() && isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
					$post = get_post( (int) $_POST['post_id'] );
					if ( $post ) {
						$response2 = ( new WAPT_Unsplash() )->set_per_page( 20 )->set_orientation( $orient )->search( $post->post_title, $page );

						if ( isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
							$response2->limit( (int) $_POST['limit'] );
						}

						$response->images = array_merge( $response2->images, $response->images );
					}
				}
			} catch ( Exception $e ) {
				die( $e->getMessage() );
			}

			if ( $response->is_error() ) {
				wp_send_json_error( $response );
			}

			wp_send_json_success( $response );
		}
	}

	/**
	 * Проверка API ключей
	 *
	 */
	public function aptp_check_api_key() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'check-api-key' ) ) {
			die( 'Error: Invalid request.' );
		}
		if ( isset( $_POST['provider'] ) && isset( $_POST['key'] ) ) {
			$provider = $_POST['provider'];
			$key      = $_POST['key'];
			switch ( $provider ) {
				case "pixabay":
					$url = "https://pixabay.com/api/?key={$key}";

					$response = wp_remote_get( $url, [ 'timeout' => 100 ] );
					if ( is_wp_error( $response ) ) {
						die( 'Error: ' . $response->get_error_message() );
					}
					$result = json_decode( $response['body'] );
					echo $result !== null ? true : false;
					break;
				case "unsplash":
					$url = "https://api.unsplash.com/photos?client_id={$key}";

					$response = wp_remote_get( $url, [ 'timeout' => 100 ] );
					if ( is_wp_error( $response ) ) {
						die( 'Error: ' . $response->get_error_message() );
					}
					$result = json_decode( $response['body'] );
					echo ! isset( $result->errors ) ? true : false;
					break;
			}
			exit;
		}
	}

	public function datepicker_js() {

		if ( is_admin() ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null );

			add_action( 'admin_footer', [ $this, 'init_datepicker' ], 99 ); // для админки
		}
	}

	public function init_datepicker() {
		$dateformat = get_option( 'date_format ' );
		$dateformat = str_replace( 'y', 'yy', $dateformat );
		$dateformat = str_replace( 'Y', 'yy', $dateformat );
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				'use strict';
				$('input[name*="filter_startdate"], .datepicker').datepicker({dateFormat: 'dd.mm.yy'});
				$('input[name*="filter_enddate"], .datepicker').datepicker({dateFormat: 'dd.mm.yy'});
				//$('input').datetimepicker();
			});
		</script>
		<?php
	}

	/**
	 * Print the advanced filter form
	 */
	public function aptp_filter_form() {

		$this->datepicker_js();
		$stati = get_post_stati( [ '_builtin' => true, "show_in_admin_status_list" => true ], 'objects' );

		$post_types = get_post_types( [ 'public' => true, 'publicly_queryable' => 1 ], 'objects', 'or' );
		unset( $post_types['attachment'] ); // удалим attachment

		$categories = get_categories( [
				'taxonomy' => 'category',
				'type'     => 'post',
				'orderby'  => 'name',
				'order'    => 'ASC',
		] );
		?>
		<div class="row wapt-filter-row">
			<div class="col-md-2">
				<label for="filter_poststatus"
				       class="apt-filter-label"><?php esc_html_e( 'Post status', 'aptp' ) ?></label>
			</div>
			<div class="col-md-10">
				<select name="filter_poststatus" id="filter_poststatus" class="apt-filter-input">
					<option value=""><?php esc_html_e( 'All', 'aptp' ); ?></option>
					<?php
					foreach ( $stati as $status ) {
						echo '<option value="' . $status->name . '">' . $status->label . '</option>';
					}
					?>
				</select>
			</div>
		</div>

		<div class="row wapt-filter-row">
			<div class="col-md-2">
				<label for="filter_posttype" class="apt-filter-label"><?php esc_html_e( 'Post type', 'aptp' ) ?></label>
			</div>
			<div class="col-md-10">
				<select name="filter_posttype" id="filter_posttype" class="apt-filter-input">
					<?php
					foreach ( $post_types as $type ) {
						echo '<option value="' . $type->name . '">' . $type->label . '</option>';
					}
					?>
				</select>
			</div>
		</div>


		<div class="row wapt-filter-row">
			<div class="col-md-2">
				<label for="filter_postcategory"
				       class="apt-filter-label"><?php esc_html_e( 'Post category', 'aptp' ) ?></label>
			</div>
			<div class="col-md-10">
				<select name="filter_postcategory" id="filter_postcategory" class="apt-filter-input">
					<option value=""><?php esc_html_e( 'All', 'aptp' ); ?></option>
					<?php
					foreach ( $categories as $cat ) {
						echo '<option value="' . $cat->term_id . '">' . $cat->name . ' (' . $cat->count . ')</option>';
					}
					?>
				</select>
			</div>
		</div>

		<div class="row wapt-filter-row">
			<div class="col-md-2">
				<label for="filter_startdate"
				       class="apt-filter-label"><?php esc_html_e( 'Date from', 'aptp' ) ?></label>
			</div>
			<div class="col-md-10">
				<input type="text" name="filter_startdate" id="filter_startdate" class="apt-filter-input datepicker">
				<label for="filter_enddate" class="apt-filter-label"><?php esc_html_e( 'to', 'aptp' ) ?></label>
				<input type="text" name="filter_enddate" id="filter_enddate" class="apt-filter-input datepicker">
			</div>
		</div>
		<?php
	}

	/**
	 * Init CRON schedules.
	 */
	private function init_cron() {
		add_filter( 'cron_schedules', function ( $schedules ) {
			$schedules['weekly']  = [
					'interval' => DAY_IN_SECONDS * 7,
					'display'  => __( 'Weekly', 'apt' ),
			];
			$schedules['monthly'] = [
					'interval' => DAY_IN_SECONDS * 30,
					'display'  => __( 'Monthly', 'apt' ),
			];

			return $schedules;
		} );

		$interval = $this->plugin->getPopulateOption( 'auto_generation_schedule', 'daily' );

		/**
		 * Filters the cron schedule interval for generation via function wp_schedule_event().
		 *
		 * @param string $interval Cron schedule name.
		 *
		 * @since 3.9.12
		 */
		$interval = apply_filters( 'wysc/generation_schedule_interval', $interval );

		add_action( 'wysc/generation_schedule', [ $this, 'cron_schedule' ] );

		if ( ! wp_next_scheduled( 'wysc/generation_schedule' ) ) {
			wp_schedule_event( time(), $interval, 'wysc/generation_schedule' );
		}
	}

	/**
	 * Deinit CRON schedules.
	 */
	private function deinit_cron() {
		$timestamp = wp_next_scheduled( 'wysc/generation_schedule' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'wysc/generation_schedule' );
		}
	}

	/**
	 * Do CRON schedule.
	 */
	public function cron_schedule() {
		if ( ! $this->doing_processing ) {
			if ( $this->processing->push_items() ) {
				$this->processing->save()->dispatch();
				$this->plugin->updatePopulateOption( 'process_running', true );
			} else {
				$this->plugin->updatePopulateOption( 'process_running', false );
			}
		}
	}

}
