<?php
if (!defined('ABSPATH')) die('No direct access allowed');


/**
 * Reduce CSS asset size by removing unused rules.
 * Some of the code and implementation steps were motivated by the earlier works in the
 * https://github.com/momentum81/php-remove-unused-css package
 */
if (!class_exists('WP_Optimize_Minify_Unused_Css')) :
class WP_Optimize_Minify_Unused_Css {

	private static $_instance = null;

	/**
	 * Maintain array of found css elements in html buffer
	 *
	 * @var array
	 */
	private $used_css_elements_found;

	/**
	 * Maintain array of css structure parsed from css file(s)
	 *
	 * @var array
	 *
	 * Structure:
	 *  [
	 *      'file_name' => [
	 *          [
	 *              'css_selector' => 'css declarations'
	 *          ]
	 *      ]
	 *  ]
	 */
	private $css_structure_found;

	/**
	 * Maintain array of data ready to be saved to file
	 *
	 * @var array
	 */
	private $ready_to_save_data;

	/**
	 * Maintain array of css classes to whitelist
	 *
	 * @var array
	 */
	private $whitelist;

	/**
	 * Hash Key for checking uniqueness of html buffer and css file list for each request
	 *
	 * @var string
	 */
	private $hash_key;

	/**
	 * WP_Optimize_Minify_Unused_Css constructor.
	 */
	private function __construct() {
		$this->used_css_elements_found = array();
		$this->css_structure_found = array();
		$this->ready_to_save_data = array();

		// Potentially further extend this by adding an exclusion list on the UI that users can update
		$default_whitelist = array('.fab', '.far', '.fal', '.dashicons', ':root');
		$additional_whitelist = apply_filters('wpo_unused_css_exclusion_list', array());
		$this->whitelist = array_merge($default_whitelist,
			is_array($additional_whitelist) ? $additional_whitelist : array());
	}

	/**
	 * Parse html buffer, collects used css selectors and compare against those referenced in css files,
	 * removing those deemed unused
	 *
	 * @param string $buffer The current output content being sent to the user
	 * @return string
	 */
	public function remove_unused_css($buffer) {

		if (!WP_Optimize_Utils::is_valid_html($buffer)) {
			return $buffer;
		}

		defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 600000);

		$html_dom = WP_Optimize_Utils::get_simple_html_dom_object($buffer);

		if (false === $html_dom) {
			if (strlen($buffer) > MAX_FILE_SIZE) {
				return $buffer . "\n" . "<!-- remove_unused_css skipped: HTML too large (limit is " . MAX_FILE_SIZE . " bytes) -->";
			}
			return $buffer . "\n" . "<!-- remove_unused_css was skipped because the helper library refused to process the html -->";
		}

		$link_tags = $html_dom->getElementsByTagName('link');
		$stylesheets_tags = array();
		$stylesheets_src = array();
		foreach ($link_tags as $tag) {
			if ($tag->hasAttribute('rel') && 'stylesheet' == $tag->getAttribute('rel')) {
				$file_path = WP_Optimize_Utils::get_file_path($tag->getAttribute('href'));
				if ($this->is_excluded_file($file_path)) continue;
				$stylesheets_tags[] = $tag;
				$stylesheets_src[] = $file_path;
			}
		}

		if (empty($stylesheets_src)) return $buffer;

		// Calculate hash using current page identifier and extracted used selectors
		$this->hash_key = hash('adler32', $this->get_unique_page_identifier() . implode('', $stylesheets_src));

		// If hash files exist skip all this
		if (!$this->is_page_already_reduced($stylesheets_src)) {
			$this->scan_css_files_for_all_elements($stylesheets_src);

			$this->filter_css($buffer);

			$this->prepare_for_saving();

			$this->maybe_create_files();
		}

		// Update the html buffer with the new stylesheet file paths
		foreach ($stylesheets_tags as $tag) {
			$src = $tag->getAttribute('href');
			$new_src = str_replace(".min", "-".$this->hash_key.".reduced.min", $src);
			$new_src_path = WP_Optimize_Utils::get_file_path($new_src);
			if (is_file($new_src_path)) $tag->setAttribute('href', $new_src);
		}

		return $html_dom->save();
	}

	/**
	 * Scan the html buffer and extract used CSS selectors
	 *
	 * @param string $html_buffer
	 *
	 * @return void
	 */
	private function extract_used_selectors($html_buffer) {
		$html_regex = $this->regex_for_html_buffer();
		foreach ($html_regex as $regex) {
			preg_match_all($regex['regex'], $html_buffer, $matches, PREG_PATTERN_ORDER);
			$prepend_string = $regex['string_place_before'];
			$append_string = $regex['string_place_after'];

			if (isset($matches[1])) {
				foreach ($matches[1] as $match) {
					foreach (preg_split('/\s+/', trim($match)) as $exploded_match) {
						$this->populate_used_css_element_array($exploded_match, $prepend_string, $append_string);
					}
				}
			}
		}
	}

	/**
	 * Adds found used css element to the `used_css_elements_found` array
	 *
	 * @param string $found_element
	 * @param string $before_string
	 * @param string $after_string
	 *
	 * @return void
	 */
	private function populate_used_css_element_array($found_element, $before_string, $after_string) {
		if ('' === trim($found_element)) return;
		$formatted_match = $before_string.trim($found_element).$after_string;
		if (!in_array($formatted_match, $this->used_css_elements_found)) {
			$this->used_css_elements_found[] = $formatted_match;
		}
	}

	/**
	 * Get regex for html buffer
	 *
	 * @return array
	 */
	private function regex_for_html_buffer() {
		return array(
			'tags' => array(
				'regex' => '/<\s*\/?\s*([[:alnum:]]+)[^>]*>/',
				'string_place_before' => '',
				'string_place_after'  => '',
			),
			'classes' => array(
				'regex' => '/\bclass\s*=\s*["\']([^"\']+)["\']/i',
				'string_place_before' => '.',
				'string_place_after'  => '',
			),
			'ids' => array(
				'regex' => '/\bid\s*=\s*["\']([^"\']+)["\']/i',
				'string_place_before' => '#',
				'string_place_after'  => '',
			),
			'Data Tags' => array(
				'regex' => '/\b(data-[[:alnum:]_-]+)(?=\s*=|\s|>)/',
				'string_place_before' => '',
				'string_place_after'  => '',
			),
		);
	}

	/**
	 * Scan the CSS files for all main elements
	 *
	 * @param array $css_files
	 *
	 * @return void
	 */
	private function scan_css_files_for_all_elements($css_files) {
		$regex_for_css = '/([^{]+)\{((?:[^{}]+|\{(?:[^{}]+|\{[^{}]*\})*\})*)\}/';
		foreach ($css_files as $file) {
			if (!file_exists($file)) continue;

			preg_match_all($regex_for_css, file_get_contents($file), $matches, PREG_SET_ORDER);
			if (!empty($matches)) {
				foreach ($matches as $match) {
					$element_key = trim(preg_replace('/\s+/', ' ', $match[1]));
					$this->css_structure_found[$file][][$element_key] = trim(preg_replace('/\s+/', ' ', $match[2]));
				}
			}
		}
	}

	/**
	 * Strip out the unused element
	 *
	 * @param string $buffer
	 *
	 * @return  void
	 */
	private function filter_css($buffer) {
		if (empty($this->css_structure_found)) return;

		$this->extract_used_selectors($buffer);

		$merged_array = array_merge($this->whitelist, $this->used_css_elements_found);

		foreach ($this->css_structure_found as &$blocks) {
			foreach ($blocks as $index => $block) {
				$selectors = key($block);
				$keep = false;

				if ($this->is_css_at_rule($selectors)
					|| $this->has_universal_selector($selectors)
					|| $this->has_non_data_attribute_selector($selectors)
				) {
					continue;
				}

				foreach (explode(',', $selectors) as $selector) {
					// Captures class names, data attributes and pseudo-elements from selector
					// Might just check the whole array, to be less aggressive
					$pattern_pseudo = '/[@#\.]?[a-zA-Z0-9_-]+|:[a-zA-Z-]+|(data-[a-zA-Z0-9_-]+)(?:[~|^$*]?="[^"]*")?/';
					preg_match_all($pattern_pseudo, $selector, $matches);

					if (array_intersect($matches[0], $merged_array)) {
						$keep = true;
						// if one of the rules match no need to check others
						break;
					}
				}
				if (!$keep) {
					unset($blocks[$index]);
				}
			}

		}
	}

	/**
	 * Get the source ready to be saved in files or returned
	 *
	 * @return  void
	 */
	private function prepare_for_saving() {
		foreach ($this->css_structure_found as $file => $blocks) {
			$source = '';
			if (!empty($blocks)) {
				foreach ($blocks as $block) {
					foreach ($block as $selector => $values) {
						$values = trim($values);

						// Expect at-rule values to have trailing } only do this augmentation for non at-rule selectors
						if (!$this->is_css_at_rule($selector) && false !== strpos($values, '{')) {
							$values .= '}';
						}

						$source .= $selector."{".$values."}";
					}
				}
			}

			$new_file_name = str_replace(".min", "-".$this->hash_key.".reduced.min", $file);

			$this->ready_to_save_data[] = array(
				'filename'    => $file,
				'new_filename' => $new_file_name,
				'source'      => WP_Optimize_Minify_Functions::minify_css_string($source)
			);
		}
	}

	/**
	 * Create the stripped down CSS files
	 *
	 * @return  void
	 */
	private function maybe_create_files() {
		foreach ($this->ready_to_save_data as $file_data) {
			$this->maybe_create_file($file_data['new_filename'], $file_data['source']);

			// Create json log for new file by copying content from previous minified file
			$previous_file_json = $file_data['filename'].'.json';
			$json_content = @file_get_contents($previous_file_json); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Silenced to suppress errors
			if (file_exists($previous_file_json) && false !== $json_content) {
				$this->maybe_create_file($file_data['new_filename'].'.json', $json_content);
			}
		}
	}

	/**
	 * Create and fill a new file if it does not already exist
	 *
	 * @param string $filename
	 * @param string $source
	 */
	private function maybe_create_file($filename, $source) {
		if (file_exists($filename)) return;
		@file_put_contents($filename, $source); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Silenced to suppress errors when the directory is not writable.
	}

	/**
	 * Determine if css file should be excluded from unused rule cleanup
	 *
	 * @param string $file
	 *
	 * @return  boolean
	 */
	private function is_excluded_file($file) {
		// Exclude css file loaded from other theme/plugin folders, only process css files from cache/wpo-minify folder
		$minify_directory = 'cache/wpo-minify';
		if (strpos($file, $minify_directory) === false) return true;

		// Exclude already reduced css files
		$hash_suffix = ".reduced.min";
		if (strpos($file, $hash_suffix) !== false) return true;

		return false;
	}

	/**
	 * Determine if the page is already processed by checking existence of a reduced css file
	 *
	 * @param array $files
	 *
	 * @return  boolean
	 */
	private function is_page_already_reduced($files) {
		$is_page_already_reduced = false;
		foreach ($files as $file) {
			$new_src = str_replace(".min", "-".$this->hash_key.".reduced.min", $file);
			if (file_exists($new_src)) {
				$is_page_already_reduced = true;
			} else {
				// If one of the files is missing, return false
				return false;
			}
		}
		return $is_page_already_reduced;
	}

	/**
	 * Determine if string has a format of css at-rules (@*)
	 *
	 * @param string $selector
	 *
	 * @return  boolean
	 */
	private function is_css_at_rule($selector) {
		return 0 === strpos(trim($selector), '@');
	}

	/**
	 * Determine if selector string contains universal selector
	 *
	 * @param string $selector
	 *
	 * @return  boolean
	 */
	private function has_universal_selector($selector) {
		return false !== strpos(trim($selector), '*');
	}

	/**
	 * Determine if selector string contains non-data attribute selector
	 *
	 * @param string $selector
	 *
	 * @return  boolean
	 */
	private function has_non_data_attribute_selector($selector) {
		// Match all attribute selectors like [attr], [attr="value"], [attr^="prefix"]
		preg_match_all('/\[(?<attr>[a-zA-Z0-9_-]+)(?:[~|^$*]?="[^"]*")?\]/', $selector, $matches);

		// Filter out data-* attributes
		foreach ($matches['attr'] as $attr) {
			if (strpos($attr, 'data-') !== 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the unique page identifier
	 *
	 * @return string
	 */
	private function get_unique_page_identifier() {
		if (get_the_ID()) {
			return (string) get_the_ID();
		}

		if (is_search()) {
			return 'search';
		}
	
		if (is_404()) {
			return '404';
		}

		if (empty($_SERVER['REQUEST_URI'])) {
			return '/';
		}

		$request_uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));

		$permalink_structure = get_option('permalink_structure');

		// If plain permalink structure is used, return full URI
		if (empty($permalink_structure)) {
			return $request_uri;
		}

		// Otherwise, strip query parameters
		$path = (string) wp_parse_url($request_uri, PHP_URL_PATH);

		return $path ? trailingslashit($path) : '/';
	}

	/**
	 * Singleton instance
	 *
	 * @return WP_Optimize_Minify_Unused_Css
	 */
	public static function get_instance() {
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

endif;
