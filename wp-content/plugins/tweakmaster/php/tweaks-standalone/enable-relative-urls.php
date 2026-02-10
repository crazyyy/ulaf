<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Enable Relative URLs
 * Description:      Enable Relative URLs
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

init();

function init(): void {
	( new RelativeUrlsModule() )->handle();
}

/**
 * Make diffing with the original easy.
 *
 * Names
 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
 *
 * White Space
 * phpcs:disable WordPress.WhiteSpace
 * phpcs:disable Generic.WhiteSpace.ArbitraryParenthesesSpacing
 * phpcs:disable Squiz.Functions.FunctionDeclarationArgumentSpacing
 * phpcs:disable PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket
 * phpcs:disable PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket
 * phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie
 * phpcs:disable NormalizedArrays.Arrays.ArrayBraceSpacing
 *
 * Allow multiple assignments
 * phpcs:disable Squiz.PHP.DisallowMultipleAssignments.Found
 *
 * Class fn mix
 * phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed
 *
 * @copyright Roots Software LLC, License MIT
 * @see https://github.com/roots/acorn-prettify/blob/main/src/Modules/RelativeUrlsModule.php Original
 */
class RelativeUrlsModule {

	public function runningInConsole(): bool {
		$sapi_is_cli = (\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg');

		return (getenv( 'APP_RUNNING_IN_CONSOLE' ) || $sapi_is_cli);
	}

	/**
	 * Determine if the module is enabled.
	 */
	protected function enabled(): bool
	{
		return ! $this->runningInConsole()
			&& ! isset($_GET['sitemap']) // phpcs:ignore WordPress.Security.NonceVerification
			&& ! in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'], true);
	}

	/**
	 * Handle the module.
	 */
	public function handle(): void
	{
		if (! $this->enabled()) {
			return;
		}

		foreach ( $this->urlFilters() as $filter ) {
			add_filter( $filter, [ $this, 'relativeUrl' ] );
		}

		add_filter( 'wp_calculate_image_srcset', [ $this, 'imageSrcset' ] );

		$this->handleCompatibility();
	}

	/**
	 * Handle compatibility with third-party plugins.
	 */
	protected function handleCompatibility(): self
	{
		return $this->handleSeoFramework();
	}

	/**
	 * Handle The SEO Framework compatibility.
	 */
	protected function handleSeoFramework(): self
	{
		add_filter('the_seo_framework_do_before_output', fn () => remove_filter('wp_get_attachment_url',  [ $this, 'relativeUrl' ] ));
		add_filter('the_seo_framework_do_after_output', fn () => add_filter('wp_get_attachment_url', [ $this, 'relativeUrl' ] ));

		return $this;
	}

	/**
	 * Convert an absolute URL to a relative URL.
	 */
	public function relativeUrl(string $url): string
	{
		if (is_feed()) {
			return $url;
		}

		if ($this->compareBaseUrl(network_home_url(), $url)) {
			return wp_make_link_relative($url);
		}

		return $url;
	}

	/**
	 * Convert multiple URL sources to relative URLs.
	 *
	 * @param string|array $sources
	 *
	 * @return string|array
	 */
	public function imageSrcset($sources)
	{
		if (! is_array($sources)) {
			return $sources;
		}

		return array_map(
			function ($source) {
				$source['url'] = $this->relativeUrl($source['url']);

				return $source;
			},
			$sources
		);
	}

	/**
	 * List of URL hooks to be filtered by this module
	 *
	 * @see https://github.com/roots/acorn-prettify/blob/main/config/prettify.php#L95 Original source
	 */
	protected function urlFilters(): array
	{
		return array(
			'bloginfo_url',
			'the_permalink',
			'wp_list_pages',
			'wp_list_categories',
			'wp_get_attachment_url',
			'the_content_more_link',
			'the_tags',
			'get_pagenum_link',
			'get_comment_link',
			'month_link',
			'day_link',
			'year_link',
			'term_link',
			'the_author_posts_link',
			'script_loader_src',
			'style_loader_src',
			'theme_file_uri',
			// 'parent_theme_file_uri', this actually breaks 2025 style.css url on multisite, possibly breaks single sites and all themes (with bedrock layout).
		);
	}

	/**
	 * Determine if two URLs contain the same base URL.
	 */
	protected function compareBaseUrl(string $baseUrl, string $inputUrl, bool $strict = true): bool
	{
		$baseUrl  = trailingslashit($baseUrl);
		$inputUrl = trailingslashit($inputUrl);

		if ($baseUrl === $inputUrl) {
			return true;
		}

		$inputUrl = wp_parse_url($inputUrl);

		if (! isset($inputUrl['host'])) {
			return true;
		}

		$baseUrl = wp_parse_url($baseUrl);

		if (! isset($baseUrl['host'])) {
			return false;
		}

		if (! $strict || ! isset($inputUrl['scheme']) || ! isset($baseUrl['scheme'])) {
			$inputUrl['scheme'] = $baseUrl['scheme'] = 'soil';
		}

		if (
			! $this->str_is( $baseUrl['scheme'], $inputUrl['scheme'] ) ||
			! $this->str_is( $baseUrl['host'], $inputUrl['host'] )
		) {
			return false;
		}

		if (isset($baseUrl['port']) || isset($inputUrl['port'])) {
			return isset( $baseUrl['port'], $inputUrl['port'] ) && $this->str_is( $baseUrl['port'], $inputUrl['port'] );
		}

		return true;
	}

	/**
	 * Determine if a given string matches a given pattern.
	 * @see https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Str.php#L484
	 *
	 * @param  string|iterable<string>  $pattern
	 */
	public static function str_is($pattern, string $value): bool
	{
		if (! is_iterable($pattern)) {
			$pattern = [$pattern];
		}

		foreach ($pattern as $pattern) {
			$pattern = (string) $pattern;

			// If the given value is an exact match we can of course return true right
			// from the beginning. Otherwise, we will translate asterisks and do an
			// actual pattern match against the two strings to see if they match.
			if ('*' === $pattern || $pattern === $value) {
				return true;
			}

			$pattern = preg_quote($pattern, '#');

			// Asterisks are translated into zero-or-more regular expression wildcards
			// to make it convenient to check if the strings starts with the given
			// pattern such as "library/*", making any string check convenient.
			$pattern = str_replace('\*', '.*', $pattern);

			if (preg_match('#^'.$pattern.'\z#su', $value) === 1) {
				return true;
			}
		}

		return false;
	}
}
