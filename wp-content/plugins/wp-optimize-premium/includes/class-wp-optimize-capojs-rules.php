<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WP_Optimize_CapoJS_Rules')) :
/**
 * Class used to optimize the <head> tag sequence for optimal page loading, according to Capo.js rules
 * (https://rviscomi.github.io/capo.js/user/rules/).
 */
class WP_Optimize_CapoJS_Rules {

	/**
	 * Checks if we should apply Capo.js rules.
	 *
	 * @param string $html
	 * @return bool
	 */
	public static function should_apply_capojs_rules($html) {

		if (defined('WPO_DISABLE_CAPOJS_RULES') && WPO_DISABLE_CAPOJS_RULES) return false;

		if (is_admin()) return false;

		if (!WP_Optimize_Utils::is_valid_html($html)) return false;

		return true;
	}

	/**
	 * Sort head tags according to Capo.js rules
	 *
	 * @param string $html
	 * @return string
	 */
	public function optimize($html) {

		$html_dom = WP_Optimize_Utils::get_simple_html_dom_object($html);

		if (empty($html_dom)) {
			return $html . (defined('WP_DEBUG') && WP_DEBUG ? "\n<!-- Unable to parse the HTML. -->" : "");
		}

		$head = $html_dom->find('head', 0);

		if (!$head) {
			return $html . (defined('WP_DEBUG') && WP_DEBUG ? "\n<!-- The header loading sequence could not be optimized because the <head> tag was missing. -->" : "");
		}

		$elements = $head->nodes;

		$new_head_innertext = '';

		// Add <meta charset="UTF-8"> tag if missed
		if (!$this->is_elements_has_meta_charset_tag($elements)) {
			$new_head_innertext .= '<meta charset="UTF-8">'."\n";
		}

		$new_head_innertext .= $this->create_html_from_elements_and_priorities($elements, $this->get_elements_priorities($elements));

		$head->nodes = array();
		$head->innertext = $new_head_innertext;

		return $html_dom->save();
	}

	/**
	 * Get the array of element priorities according to CapoJS rules.
	 *
	 * @param array $elements
	 * @return array
	 */
	private function get_elements_priorities($elements) {
		$el_priorities = array();

		foreach ($elements as $key => $el) {
			$el_priority = $this->get_element_priority($el);
			if (!array_key_exists($el_priority, $el_priorities)) $el_priorities[$el_priority] = array();
			$el_priorities[$el_priority][] = $key;
		}

		ksort($el_priorities);

		return $el_priorities;
	}

	/**
	 * Generate HTML based on elements and their assigned priorities.
	 *
	 * @param array $elements
	 * @param array $el_priorities
	 * @return string
	 */
	private function create_html_from_elements_and_priorities($elements, $el_priorities) {

		$html = '';

		foreach ($el_priorities as $keys) {
			foreach ($keys as $key) {
				$outertext = $elements[$key]->outertext;
				if ('' !== trim($outertext)) {
					$html .= $outertext . "\n";
				}
			}
		}
		
		return $html;
	}

	/**
	 * Verify whether a `<meta charset="...">` element is present in the element list.
	 *
	 * @param array $elements
	 * @return boolean
	 */
	private function is_elements_has_meta_charset_tag($elements) {
		foreach ($elements as $el) {
			if ('meta' === strtolower($el->tag) && $el->hasAttribute('charset')) return true;
		}
		return false;
	}

	/**
	 * Get HTML element priority according to Capo.js rules. 0 - highest 10 - lowest.
	 *
	 * @param simplehtmldom\HtmlNode $el
	 * @return int
	 */
	private function get_element_priority($el) {
		$tag = strtolower($el->tag);
		$el_attribute = (string) $el->getAttribute('rel');
		$rel = strtolower($el_attribute);

		if ($this->is_critical_meta($el, $tag)) return 0;
		if ('base' === $tag) return 0;

		if ('title' === $tag) return 1;
		
		if ('link' === $tag && 'preconnect' === $rel) return 2;
		
		if ($this->is_async_script($el, $tag)) return 3;
		
		if ($this->is_import_style($el, $tag)) return 4;
		
		if ($this->is_blocking_script($el, $tag)) return 5;
		
		if ($this->is_stylesheet($tag, $rel)) return 6;
		
		if ($this->is_preload_link($tag, $rel)) return 7;
		
		if ($this->is_deferred_script($el, $tag)) return 8;
		
		if ($this->is_prefetch_link($tag, $rel)) return 9;

		return 10;
	}

	/**
	 * Check whether it is a supported http-equiv attribute.
	 *
	 * @param string $http_equiv_attr
	 * @return bool
	 */
	private function is_supported_http_equiv($http_equiv_attr) {
		$http_equiv_values_supported = array(
			'default-style',
			'x-dns-prefetch-control',
			'accept-ch',
			'delegate-ch',
			'content-security-policy',
			'origin-trial',
			'content-type',
		);

		$http_equiv = strtolower($http_equiv_attr);
		return $http_equiv && in_array($http_equiv, $http_equiv_values_supported);
	}

	/**
	 * Decides whether the passed element is a meta tag, and whether rules should be applied or not
	 *
	 * @param simplehtmldom\HtmlNode $element
	 * @param string $tag
	 * @return bool
	 */
	private function is_critical_meta($element, $tag) {
		$name_attr = (string) $element->getAttribute('name');
		$name = strtolower($name_attr);
		return 'meta' === $tag && (($element->hasAttribute('charset') && 'utf-8' === strtolower($element->getAttribute('charset'))) || ($element->hasAttribute('http-equiv') && $this->is_supported_http_equiv($element->getAttribute('http-equiv'))) || 'viewport' === $name);
	}

	/**
	 * Determine whether it is an async script tag.
	 *
	 * @param simplehtmldom\HtmlNode $element
	 * @param string $tag
	 * @return bool
	 */
	private function is_async_script($element, $tag) {
		return 'script' === $tag && $element->hasAttribute('src') && $element->hasAttribute('async');
	}

	/**
	 * Determine if it is an import style tag.
	 *
	 * @param simplehtmldom\HtmlNode $element
	 * @param string $tag
	 * @return bool
	 */
	private function is_import_style($element, $tag) {
		return 'style' === $tag && preg_match('/^@import/', trim($element->innertext));
	}

	/**
	 * Determine if a <script> element is a blocking script.
	 *
	 * @param simplehtmldom\HtmlNode $element
	 * @param string $tag
	 * @return bool
	 */
	private function is_blocking_script($element, $tag) {
		return 'script' === $tag && !$element->hasAttribute('defer') && !$element->hasAttribute('async');
	}

	/**
	 * Check if the element is a <style> tag or a <link> tag with rel="stylesheet".
	 *
	 * @param string $tag
	 * @param string $rel
	 * @return bool
	 */
	private function is_stylesheet($tag, $rel) {
		return 'style' === $tag || ('link' === $tag && 'stylesheet' === $rel);
	}

	/**
	 * Check if the tag is a <link> with a rel attribute indicating a preload-type relationship.
	 *
	 * @param string $tag
	 * @param string $rel
	 * @return bool
	 */
	private function is_preload_link($tag, $rel) {
		return 'link' === $tag && in_array($rel, array('preload', 'modulepreload'));
	}

	/**
	 * Check if the given element is a <script> tag with the "defer" attribute.
	 *
	 * @param simplehtmldom\HtmlNode $element
	 * @param string $tag
	 * @return bool
	 */
	private function is_deferred_script($element, $tag) {
		return 'script' === $tag && $element->hasAttribute('defer');
	}

	/**
	 * Check if the tag is a <link> with a rel attribute indicating a prefetch-type relationship.
	 *
	 * @param string $tag
	 * @param string $rel
	 * @return bool
	 */
	private function is_prefetch_link($tag, $rel) {
		return 'link' === $tag && in_array($rel, array('prefetch', 'dns-prefetch', 'prerender'));
	}
}
endif;
