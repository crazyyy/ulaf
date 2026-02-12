<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * The AllowCustomFileExtensionUpload class enables the functionality to allow custom file extensions
 * during media uploads in WordPress. It provides options for configuration via settings and modifies
 * MIME types allowed for upload based on custom rules and inputs defined by site administrators.
 */
class AllowCustomFileExtensionUpload {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'media' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['allowed_file_extensions_enable'] ) ) {
			add_filter( 'upload_mimes', [ $this, 'upload_mimes' ] );
		}
	}
	
	/**
	 * Adds custom settings fields for managing media upload options.
	 *
	 * @param array $fields Existing settings fields array.
	 *
	 * @return array Modified settings fields array with additional options for custom file extension uploads.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['media']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'allowed-file-extensions-enable',
			'name'         => 'adminease[media][allowed_file_extensions_enable]',
			'value'        => $this->settings['allowed_file_extensions_enable'] ?? '',
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Allow Custom File Extension Upload', 'adminease' ),
			'description'  => __( 'Enable this option to allow uploading of custom file extensions that are not natively supported by WordPress. This can be useful for uploading specialized file types required by your site or applications. Be cautious when enabling this feature, as it may pose security risks if untrusted file types are allowed.', 'adminease' ),
			'child_fields' => [
				[
					'type'          => 'select',
					'id'            => 'allowed-file-extensions-mime-types',
					'name'          => 'adminease[media][allowed_file_extensions_mime_types][]',
					'value'         => $this->settings['allowed_file_extensions_mime_types'] ?? '',
					'options'       => $this->get_custom_file_extensions_options(),
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control adminease-choices',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Allowed File Extensions', 'adminease' ),
					'description'   => __( 'Select the file extensions that you want to allow for upload when custom file extension upload is enabled. You can choose multiple extensions from the list.', 'adminease' ),
					'attributes'    => [
						'data-parent'           => 'allowed-file-extensions-enable',
						'multiple'              => 'multiple',
						'data-allow_clear'      => true,
						'data-allow_select_all' => true,
					],
				],
				[
					'type'              => 'textarea',
					'id'                => 'allowed-file-extensions-mime-types-other',
					'name'              => 'adminease[media][allowed_file_extensions_mime_types_other]',
					'value'             => str_replace( ' ', PHP_EOL, ( $this->settings['allowed_file_extensions_mime_types_other'] ?? '' ) ),
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Custom File Extensions', 'adminease' ),
					'field_description' => __( 'Custom file extensions and their corresponding MIME types here in the format, e.g. <mark>application/epub+zip</mark>, one per line.', 'adminease' ),
					'attributes'        => [
						'data-parent' => 'allowed-file-extensions-enable',
						'rows'        => 5,
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Modifies the list of MIME types and file extensions that are allowed for upload.
	 * The method retrieves MIME types from settings, combines them with additional custom MIME
	 * types, and converts them into a WordPress-compatible format. It then integrates the new
	 * MIME types into the existing list.
	 *
	 * @param array $mimes An associative array of file extensions as keys and MIME types as values
	 *                      currently allowed for upload.
	 *
	 * @return array The modified array of MIME types with additional types appended based on
	 *               settings configuration.
	 */
	public function upload_mimes( array $mimes ): array {
		$mime_types = $this->settings['allowed_file_extensions_mime_types'] ?? [];
		
		if( empty( $mime_types ) ) {
			return $mimes;
		}
		
		$custom_mime_types = preg_split( '/[\s\n\r]+/', trim( $this->settings['allowed_file_extensions_mime_types_other'] ?? '' ) );
		
		$mime_types = array_merge( $mime_types, array_filter( $custom_mime_types ) );
		
		// Convert MIME types to WordPress format (extension => mime_type)
		foreach( $mime_types as $mime_type ) {
			$mime_type = trim( $mime_type );
			
			if( empty( $mime_type ) ) {
				continue;
			}
			
			// Get extension from MIME type
			$extension = $this->get_extension_from_mime( $mime_type );
			
			if( $extension ) {
				$mimes[ $extension ] = $mime_type;
			}
		}

		return $mimes;
	}
	
	/**
	 * Retrieves a list of custom file extensions and their MIME types.
	 * @return array An associative array where the keys are file extensions and the values are
	 *               corresponding MIME types.
	 */
	private function get_custom_file_extensions_options(): array {
		return [
			// Vector and Design Files
			'image/bmp'                      => 'Bitmap Image (BMP)',
			'image/tiff'                     => 'Tagged Image File Format (TIFF)',
			'application/postscript'         => 'PostScript / Adobe Illustrator / EPS',
			'application/illustrator'        => 'Adobe Illustrator (AI)',
			'image/x-xcf'                    => 'GIMP Image File (XCF)',
			'application/x-coreldraw'        => 'CorelDRAW Image (CDR)',
			'application/x-freehand'         => 'Adobe FreeHand (FH)',
			
			// Font Files
			'font/ttf'                       => 'TrueType Font (TTF)',
			'font/otf'                       => 'OpenType Font (OTF)',
			'font/woff'                      => 'Web Open Font Format (WOFF)',
			'font/woff2'                     => 'Web Open Font Format 2 (WOFF2)',
			'application/vnd.ms-fontobject'  => 'Embedded OpenType (EOT)',
			
			// Archive / Compression Formats
			'application/x-rar-compressed'   => 'RAR Archive',
			'application/x-7z-compressed'    => '7-Zip Archive',
			'application/x-tar'              => 'TAR Archive',
			'application/gzip'               => 'GZIP Archive',
			'application/x-bzip2'            => 'BZIP2 Archive',
			'application/x-iso9660-image'    => 'ISO Disk Image',
			'application/x-apple-diskimage'  => 'Apple Disk Image (DMG)',
			
			// Data Formats / Documents
			'application/json'               => 'JSON File',
			'application/x-yaml'             => 'YAML Configuration File',
			'application/sql'                => 'SQL Database File',
			'application/x-ini'              => 'INI Configuration File',
			'application/xml'                => 'XML File',
			'text/markdown'                  => 'Markdown Document (MD)',
			
			// 3D / CAD and Misc
			'model/obj'                      => '3D Object (OBJ)',
			'model/stl'                      => '3D Model (STL)',
			'application/sla'                => 'Stereolithography CAD (SLA)',
			'model/x3d+xml'                  => 'X3D Model (X3D)',
			'model/iges'                     => 'IGES CAD File',
			'application/vnd.ms-access'      => 'Microsoft Access Database (MDB)',
			
			// Multimedia
			'application/epub+zip'           => 'EPUB eBook',
			'application/x-mobipocket-ebook' => 'MOBI eBook',
			
			// Other Common Formats
			'application/x-dvi'              => 'DVI Document',
			'application/vnd.apple.pages'    => 'Apple Pages Document (PAGES)',
		];
	}
	
	/**
	 * Get file extension from MIME type
	 *
	 * @param string $mime_type MIME type to convert.
	 *
	 * @return string|null File extension or null if not found.
	 */
	private function get_extension_from_mime( string $mime_type ): ?string {
		$extensions_map = [
			'image/bmp'                      => 'bmp',
			'image/tiff'                     => 'tiff|tif',
			'application/postscript'         => 'eps|ps',
			'application/illustrator'        => 'ai',
			'image/x-xcf'                    => 'xcf',
			'application/x-coreldraw'        => 'cdr',
			'application/x-freehand'         => 'fh',
			'font/ttf'                       => 'ttf',
			'font/otf'                       => 'otf',
			'font/woff'                      => 'woff',
			'font/woff2'                     => 'woff2',
			'application/vnd.ms-fontobject'  => 'eot',
			'application/x-rar-compressed'   => 'rar',
			'application/x-7z-compressed'    => '7z',
			'application/x-tar'              => 'tar',
			'application/gzip'               => 'gz|gzip',
			'application/x-bzip2'            => 'bz2',
			'application/x-iso9660-image'    => 'iso',
			'application/x-apple-diskimage'  => 'dmg',
			'application/json'               => 'json',
			'application/x-yaml'             => 'yaml|yml',
			'application/sql'                => 'sql',
			'application/x-ini'              => 'ini',
			'application/xml'                => 'xml',
			'text/markdown'                  => 'md',
			'model/obj'                      => 'obj',
			'model/stl'                      => 'stl',
			'application/sla'                => 'sla',
			'model/x3d+xml'                  => 'x3d',
			'model/iges'                     => 'igs|iges',
			'application/vnd.ms-access'      => 'mdb',
			'application/epub+zip'           => 'epub',
			'application/x-mobipocket-ebook' => 'mobi',
			'application/x-dvi'              => 'dvi',
			'application/vnd.apple.pages'    => 'pages',
		];
		
		return $extensions_map[ $mime_type ] ?? null;
	}
}