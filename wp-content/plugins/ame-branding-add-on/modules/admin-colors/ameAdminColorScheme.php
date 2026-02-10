<?php

use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\ValueConverter;

class ameAdminColorScheme {
	/**
	 * @var array
	 */
	private $colors;
	private $hash = null;
	private $cachedIconColor = null;

	public function __construct($colors = array()) {
		reset($colors);
		$firstIndex = key($colors);
		if ( is_int($firstIndex) ) {
			$colors = self::colorListToAssocArray($colors);
		}

		//Discard empty strings. This can happen during live preview because blank
		//settings are not automatically removed in that context.
		$colors = array_filter($colors, function ($value) {
			return ($value !== '');
		});

		$this->colors = array_intersect_key($colors, self::getAvailableOptions());
	}

	public function getColors() {
		return $this->colors;
	}

	protected function getNonEmptyColors() {
		return array_filter($this->colors);
	}

	public function hasCustomColors() {
		foreach ($this->colors as $color) {
			if ( !empty($color) ) {
				return true;
			}
		}
		return false;
	}

	public static function getAvailableOptions() {
		return array(
			'base-color'         => 'Base',
			'text-color'         => 'Text',
			'highlight-color'    => 'Highlight',
			'icon-color'         => 'Icon',
			'notification-color' => 'Notification',

			//General UI
			'body-background'    => 'Page background',

			'link'       => 'Link',
			'link-focus' => 'Link hover',

			'button-color'    => 'Button',
			'form-checked'    => 'Check mark (✓)',

			//Admin menu & Toolbar
			'menu-text'       => 'Menu text',
			'menu-icon'       => 'Menu icon',
			'menu-background' => 'Menu background',

			'menu-highlight-text'       => 'Menu highlight text',
			'menu-highlight-icon'       => 'Menu highlight icon',
			'menu-highlight-background' => 'Menu highlight background',

			'menu-current-text'       => 'Menu current text',
			'menu-current-icon'       => 'Menu current icon',
			'menu-current-background' => 'Menu current background',

			'menu-submenu-text'           => 'Submenu text',
			'menu-submenu-background'     => 'Submenu background',
			'menu-submenu-background-alt' => 'Submenu background (alt.)',

			'menu-submenu-focus-text'   => 'Submenu highlight text',
			'menu-submenu-current-text' => 'Submenu current text',

			'menu-bubble-text'               => 'Bubble text',
			'menu-bubble-background'         => 'Bubble background',
			'menu-bubble-current-text'       => 'Bubble current text',
			'menu-bubble-current-background' => 'Bubble current background',

			'menu-collapse-text' => 'Menu collapse button text',
			/*
			 //These three variables appear to be unused, at least in WP 4.9.4.
			'menu-collapse-icon'       => 'Menu collapse button icon',
			'menu-collapse-focus-text' => 'Menu collapse highlight text',
			'menu-collapse-focus-icon' => 'Menu collapse highlight icon',
			//*/

			'adminbar-avatar-frame'     => 'Toolbar avatar border',
			'adminbar-input-background' => 'Toolbar search box background',
		);
	}

	/**
	 * @return array
	 */
	public static function getColorListOrder() {
		return array_keys(self::getAvailableOptions());
	}

	/**
	 * @param array $colorList
	 * @return array
	 */
	private static function colorListToAssocArray($colorList) {
		$colors = array();
		foreach (self::getColorListOrder() as $index => $name) {
			if ( isset($colorList[$index]) && self::isValidHexColor('#' . $colorList[$index]) ) {
				$colors[$name] = '#' . $colorList[$index];
			}
		}
		return $colors;
	}

	/**
	 * Check if the input is a CSS color in hexadecimal format, e.g. #001122.
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function isValidHexColor($value) {
		return is_string($value) && preg_match('@^#[a-f\d]{3,6}$@', $value);
	}

	/**
	 * @throws SassException
	 */
	public function compileToCss() {
		return self::compileScssToCss(
			$this->getNonEmptyColors(),
			array($this->getAdminDir() . '/css/colors'),
			'@import "_admin.scss";'
		);
	}

	/**
	 * Compile only the part of the admin color scheme which pertains to the Admin Bar/Toolbar.
	 *
	 * @return string
	 * @throws SassException
	 */
	public function compileAdminBarStylesToCss() {
		$adminScssPath = $this->getAdminDir() . '/css/colors/_admin.scss';
		if ( !is_file($adminScssPath) ) {
			return '/* Error: _admin.scss not found while trying to compile admin bar styles. */';
		}

		//Find the Admin Bar section in the SCSS file.
		$sheet = file_get_contents($adminScssPath);
		if ( !preg_match('@^\s??/\*\s+?Admin Bar\s+?\*/\s{0,20}?$@mi', $sheet, $matches, PREG_OFFSET_CAPTURE) ) {
			return '/* Error: "Admin Bar" section not found in the stylesheet. */';
		}
		/** @var int $startPosition -- Due to the PREG_OFFSET_CAPTURE flag, this is the position of the match. */
		$startPosition = $matches[0][1];
		$firstHeadingLength = strlen($matches[0][0]);

		//To find the end of admin bar styles, look for the first section that doesn't mention "admin bar".
		$endPosition = null;
		if ( preg_match_all(
			'@^\s??/\*\s+?[^{}*\r\n#]+?\s+?\*/\s{0,20}?$@m',
			$sheet,
			$headings,
			PREG_OFFSET_CAPTURE | PREG_SET_ORDER,
			$startPosition + $firstHeadingLength
		) ) {
			foreach ($headings as $heading) {
				if ( stripos($heading[0][0], 'admin bar') === false ) {
					$endPosition = $heading[0][1];
					break;
				}
			}
		}
		if ( $endPosition === null ) {
			return '/* Error: Could not find the end of the "Admin Bar" section in the stylesheet. */';
		}

		$adminBarStyles = substr($sheet, $startPosition, $endPosition - $startPosition);

		$code = '@import "_variables.scss";' . "\n";
		$code .= $adminBarStyles;

		return self::compileScssToCss(
			$this->getNonEmptyColors(),
			array($this->getAdminDir() . '/css/colors'),
			$code
		);
	}

	/**
	 * Compile SCSS to CSS.
	 *
	 * @param array $variables
	 * @param string[] $importPaths
	 * @param string $scssCode
	 * @return string
	 * @throws SassException
	 */
	private static function compileScssToCss($variables, $importPaths, $scssCode) {
		$scss = new ScssPhp\ScssPhp\Compiler();

		list($scssCode,) = self::downgradeColorScssCode($scssCode, $importPaths);

		if ( !empty($variables) ) {
			if ( method_exists($scss, 'addVariables') ) {
				//ScsPhp will trigger a silenced notice if the variables
				//are not converted to Sass values.
				if ( is_callable(array(ValueConverter::class, 'parseValue')) ) {
					$variables = array_map(function ($value) {
						//This is inefficient because it creates a new parser each
						//time. Unfortunately, the parser class is marked as internal.
						return ValueConverter::parseValue($value);
					}, $variables);
				}
				$scss->addVariables($variables);
			} else {
				/** @noinspection PhpDeprecationInspection Backwards compatibility. */
				$scss->setVariables($variables);
			}
		}

		foreach ($importPaths as $importPath) {
			$scss->addImportPath($importPath);
		}

		if ( method_exists($scss, 'compileString') ) {
			$result = $scss->compileString($scssCode);
			return $result->getCss();
		} else {
			/** @noinspection PhpDeprecationInspection Backwards compatibility. */
			return $scss->compile($scssCode);
		}
	}

	private function getAdminDir() {
		return ABSPATH . '/wp-admin';
	}

	/**
	 * Convert SCSS code to syntax compatible with our somewhat outdated ScssPhp compiler.
	 *
	 * WordPress started using modern Sass syntax in WP 6.7 and then modernized even more in WP 6.8.
	 * Modern Sass has module-scoped functions, "@use" instead of "@import", "@forward" rules, and
	 * a bunch more things. The ScssPhp compiler we currently use doesn't support all that, so we
	 * need to convert the color stylesheets to the old syntax.
	 *
	 * This method also recursively inlines any imports that use modern Sass features and so need
	 * to be converted.
	 *
	 * Returns an array with two items:
	 *   0 => The modified SCSS code.
	 *   1 => The updated list of inlined imports.
	 *
	 * @param string $scssCode
	 * @param string[] $importPaths
	 * @param array<string,boolean> $inlinedImports Lookup array of inlined imports, indexed by import file path.
	 * @return array{0: string, 1:array<string,boolean>}
	 */
	private static function downgradeColorScssCode($scssCode, $importPaths, $inlinedImports = []) {
		//Replace "color.adjust()", "color.mix()", and other simple functions.
		$scssCode = preg_replace(
			[
				'@\bcolor\.adjust\s*\(@',
				'@\bcolor\.mix\s*\(@',
				'@\bstring\.slice\s*\(@',
			],
			[
				'adjust-color(',
				'mix(',
				'str-slice(',
			],
			$scssCode
		);

		//Replace "color.channel()" calls that get the "hue" component with "hue()".
		//Other components are not handled because WP admin color scheme stylesheets
		//don't use them.
		$scssCode = preg_replace('@\bcolor\.channel\s*\(([^,()]++), "hue"[^)]*\)@', 'hue($1)', $scssCode);

		//Replace "@use 'file'" with "@import 'file'" and remove any "sass:..." imports.
		$scssCode = preg_replace_callback(
			'@^\s*?\@use\s+["\'](?P<import>[^"\']+?)["\']\s*;\s*$@m',
			function ($matches) use ($importPaths) {
				$import = $matches['import'];
				if ( strpos($import, ':') !== false ) {
					return ''; //Remove a module import.
				}
				return '@import "' . $import . '";';
			},
			$scssCode
		);

		//Remove "@forward" rules.
		$scssCode = preg_replace('@^\s*?\@forward\s+["\'](?P<import>[^"\']+?)["\'][^\r\n]*$@m', '', $scssCode);

		//Change "variables.$var" to "$var".
		$scssCode = preg_replace('@\bvariables\.\$@', '$', $scssCode);
		//Same for "mixins.func".
		$scssCode = preg_replace('@\bmixins\.@', '', $scssCode);

		//Where possible, inline any imports that also use modern Sass features.
		//ScssPhp doesn't have a way to filter the content of imported files before
		//they're compiled, so we have to use this workaround.
		$scssCode = preg_replace_callback(
			'@^\s*?\@import\s+["\'](?P<import>[^"\']+?)["\'];@m',
			function ($matches) use ($importPaths, &$inlinedImports) {
				$import = $matches['import'];

				//Find the file to import.
				$importPath = null;
				foreach ($importPaths as $path) {
					if ( is_file($path . '/_' . $import . '.scss') ) {
						$importPath = $path . '/_' . $import . '.scss';
						break;
					} else if ( is_file($path . '/' . $import) ) {
						$importPath = $path . '/' . $import;
						break;
					}
				}
				if ( $importPath === null ) {
					return $matches[0];
				}
				$importPath = wp_normalize_path($importPath);

				//Don't inline the same file twice and avoid loops.
				//(Shouldn't really come up with admin color schemes.)
				if ( isset($inlinedImports[$importPath]) ) {
					return '';
				}

				$importContent = file_get_contents($importPath);
				if ( $importContent === false ) {
					return $matches[0];
				}

				//Attempt to inline the import.
				$temp = $inlinedImports;
				$temp[$importPath] = true;
				list($convertedImport, $newInlinedImports) = self::downgradeColorScssCode(
					$importContent,
					$importPaths,
					$temp
				);

				//If the import didn't contain any code that needed conversion, let's
				//leave it unchanged instead of inlining it, just to be safer.
				if ( $convertedImport === $importContent ) {
					return $matches[0];
				} else {
					//Only update the list on success.
					$inlinedImports = $newInlinedImports;
					return '/* Inlined import "' . $import . '" */' . "\n" . $convertedImport;
				}
			},
			$scssCode
		);

		return [$scssCode, $inlinedImports];
	}

	public function getDemoColors() {
		return array(
			$this->getEffectiveBaseColor(),
			$this->getIconColor(),
			ameUtils::get($this->colors, 'notification-color', '#d54e21'),
			ameUtils::get($this->colors, 'highlight-color', '#0073aa'),
		);
	}

	public function getSvgIconColors() {
		$icons = array(
			'base'  => $this->getIconColor(),
			'focus' => ameUtils::get($this->colors, 'menu-highlight-icon', '#fff'),
		);
		$icons['current'] = $icons['focus'];
		return $icons;
	}

	protected function getIconColor() {
		$customColor = ameUtils::get($this->colors, 'icon-color');
		if ( !empty($customColor) ) {
			return $customColor;
		}
		if ( !empty($this->cachedIconColor) ) {
			return $this->cachedIconColor;
		}

		//WordPress computes the default icon color based on the base color, and
		//we'll do the same. Using an entire SCSS compiler just to compute a single
		//color is a bit overkill, but the result should usually be cached.
		$baseColor = $this->getEffectiveBaseColor();
		$scss = '
			$base-color: ' . $baseColor . '; 
			$icon-color: hsl( hue( $base-color ), 7%%, 95%% );
			#output {
				color: $icon-color;
			}';

		$result = $baseColor;
		try {
			$css = self::compileScssToCss(['base-color' => $baseColor], [], $scss);
			if ( preg_match('@#output\s*\{[^}a-z]*color:\s*([^;}\r\n]+)[};]@i', $css, $matches) ) {
				$result = trim($matches[1]);
			}
		} catch (SassException $e) {
			//Ignored. We'll just fall back to the base color.
		}

		$this->cachedIconColor = $result;
		return $result;
	}

	protected function getEffectiveBaseColor() {
		$color = ameUtils::get($this->colors, 'base-color');
		if ( !empty($color) ) {
			return $color;
		}
		return '#23282d';
	}

	public function getHash() {
		if ( !isset($this->hash) ) {
			$this->hash = substr(md5(build_query($this->colors)), 0, 16);
		}
		return $this->hash;
	}

	public function toArray() {
		return [
			'colors'          => $this->colors,
			'hash'            => $this->getHash(),
			'cachedIconColor' => $this->getIconColor(),
		];
	}

	public static function fromArray($properties) {
		$scheme = new self(ameUtils::get($properties, 'colors', []));
		/** @noinspection PhpRedundantOptionalArgumentInspection Hash must be NULL if not set */
		$scheme->hash = ameUtils::get($properties, 'hash', null);
		/** @noinspection PhpRedundantOptionalArgumentInspection */
		$scheme->cachedIconColor = ameUtils::get($properties, 'cachedIconColor', null);
		return $scheme;
	}
}