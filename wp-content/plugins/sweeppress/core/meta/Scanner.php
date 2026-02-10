<?php

namespace Dev4Press\Plugin\SweepPress\Meta;

use Dev4Press\v54\Core\Quick\File;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scanner {
	protected string $path = '';
	protected array $files = array();
	protected array $exclude_dir_names = array(
		'.git',
		'node_modules',
		'vendor',
	);
	protected array $allowed_extensions = array(
		'php',
		'php5',
		'php4',
		'php3',
		'phtml',
		'inc',
	);
	protected array $regex = array(
		'options'     => array(
			'/\supdate_option\(\s*(\'|")(.+?)\1\s*\,/im',
			'/\sadd_option\(\s*(\'|")(.+?)\1\s*\,/im',
			'/\sregister_setting\(.+?,\s*(\'|")(.+?)\1\s*[,|)]/ims',
		),
		'postmeta'    => array(
			'/(add_post_meta|update_post_meta)\(.+?,\s*(\'|")(.+?)\2\s*\,/im',
			'/(add_metadata|update_metadata)\(.+?(\'post\'|"post")\s*,.+?,\s*(\'|")(.+?)\3\s*\,/im',
			'/\sregister_post_meta\(.+?,\s*(\'|")(.+?)\1\s*\,/ims',
		),
		'termmeta'    => array(
			'/(add_term_meta|update_term_meta)\(.+?,\s*(\'|")(.+?)\2\s*\,/im',
			'/(add_metadata|update_metadata)\(.+?(\'term\'|"term")\s*,.+?,\s*(\'|")(.+?)\3\s*\,/im',
			'/\sregister_term_meta\(.+?,\s*(\'|")(.+?)\1\s*\,/ims',
		),
		'usermeta'    => array(
			'/(add_user_meta|update_user_meta|update_user_option)\(.+?,\s?(\'|")(.+?)\2\s*\,/im',
			'/(add_metadata|update_metadata)\(.+?(\'user\'|"user")\s*,.+?,\s*(\'|")(.+?)\3\s*\,/im',
			'/\sregister_user_meta\(.+?,\s*(\'|")(.+?)\1\s*\,/ims',
		),
		'commentmeta' => array(
			'/(add_comment_meta|update_comment_meta)\(.+?,\s?(\'|")(.+?)\2\s*\,/im',
			'/(add_metadata|update_metadata)\(.+?(\'comment\'|"comment")\s*,.+?,\s*(\'|")(.+?)\3\s*\,/im',
			'/\sregister_comment_meta\(.+?,\s*(\'|")(.+?)\1\s*\,/ims',
		),
		'sitemeta'    => array(
			'/update_site_option\(\s*(\'|")(.+?)\1\s*\,/im',
		),
	);
	protected array $regex_match_id = array(
		'options'     => array(
			2,
			2,
			2,
		),
		'postmeta'    => array(
			3,
			4,
			2,
		),
		'termmeta'    => array(
			3,
			4,
			2,
		),
		'usermeta'    => array(
			3,
			4,
			2,
		),
		'commentmeta' => array(
			3,
			4,
			2,
		),
		'sitemeta'    => array(
			2,
		),
	);
	protected array $values_to_exclude = array(
		'options'  => array( '_children', 'widget_', 'dismissed-', 'dismissed_', 'activated', 'enabled', 'version' ),
		'postmeta' => array( '_wp_page_template', '_wp_attachment_image_alt' ),
	);
	protected array $skip = array(
		'-',
		'_',
		'__',
		'.',
		',',
		'log',
		'on',
		'no',
		'yes',
		'data',
		'post',
		'user',
		'true',
		'false',
	);

	public function __construct() {
	}

	public static function instance() : Scanner {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Scanner();
		}

		return $instance;
	}

	public function scan( string $dir ) : array {
		$start = microtime( true );

		$this->path  = $dir;
		$this->files = array();

		$this->process( $dir );

		$results = array(
			'options'     => array(),
			'sitemeta'    => array(),
			'postmeta'    => array(),
			'termmeta'    => array(),
			'usermeta'    => array(),
			'commentmeta' => array(),
		);

		foreach ( $this->files as $file ) {
			$content = File::get_contents( $file );
			$matches = array();

			foreach ( $this->regex as $scope => $regexes ) {
				$regexes = (array) $regexes;
				$match   = (array) $this->regex_match_id[ $scope ];

				foreach ( $regexes as $idx => $regex ) {
					preg_match_all( $regex, $content, $matches );

					if ( ! empty( $matches[ $match[ $idx ] ] ) ) {
						foreach ( $matches[ $match[ $idx ] ] as $key ) {
							$value   = preg_replace( '/(.?){\$.+}(.?)/i', '$1$2', $key );
							$exclude = $this->values_to_exclude[ $scope ] ?? array();

							if ( in_array( $value, $this->skip ) || in_array( $value, $exclude ) || is_numeric( $value ) || preg_match( '/\R/', $value ) ) {
								$value = '';
							}

							if ( ! empty( $value ) && ! in_array( $value, $results[ $scope ] ) && ! Storage::instance()->is_wordpress_core( $scope, $value ) ) {
								$results[ $scope ][] = $value;
							}
						}
					}
				}
			}
		}

		$results['scanned'] = time();
		$results['timer']   = microtime( true ) - $start;

		return $results;
	}

	private function process( string $dir ) {
		if ( ! file_exists( $dir ) ) {
			return;
		}

		$dh = opendir( $dir );

		if ( ! $dh ) {
			return;
		}

		while ( ( $file = readdir( $dh ) ) !== false ) {
			if ( $file == '.' || $file == '..' ) {
				continue;
			}

			$path = $dir . $file;

			if ( is_dir( $path ) ) {
				$path = $path . '/';

				if ( in_array( $file, $this->exclude_dir_names ) ) {
					continue;
				}

				$this->process( $path );
			} else if ( is_file( $path ) ) {
				$ext = pathinfo( $path, PATHINFO_EXTENSION );

				if ( in_array( $ext, $this->allowed_extensions ) ) {
					$this->files[] = $path;
				}
			}
		}
	}
}
