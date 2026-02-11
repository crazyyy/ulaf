<?php
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================

class CodeProfilerPro_FileView {

	private $ffile;
	private $ffunction;
	private $fline;
	private $cp_options;
	private $display_name;

	/********************************************************************
	 * Initialize.
	 */
	public function __construct( $ffile, $ffunction ='' , $fline = 0 ) {

		$this->ffile		= $ffile;
		$this->ffunction	= $ffunction;
		$this->fline		= $fline;

		$this->cp_options	= get_option('code-profiler-pro');
		if (! empty( $this->cp_options['show_paths'] ) &&
			$this->cp_options['show_paths'] == 'relative' ) {

			// Display the relative path of the file
			$this->display_name = ltrim( str_replace( rtrim( ABSPATH, '/\\'), '', $ffile ), '/\\');
		} else {
			$this->display_name = $ffile;
		}

		$this->parse_file();
	}

	/********************************************************************
	 * Parse the file to display.
	 */
	private function parse_file() {

		$content = file( $this->ffile );
		if ( $content === false ){
			wp_die( sprintf(
				esc_html__('Cannot read file: %s', 'code-profiler-pro'),
				esc_html( $this->ffile )
			) );
		}

		// We have a line number, decrement it to match the zero-based array
		if (! empty( $this->fline ) ) {
			$this->fline--;
		}

		// We have a function/method, let's find it
		if (! empty( $this->ffunction ) ) {
			$this->find_function_linenum( $content );
		}

		$this->display_content( $content );
	}

	/********************************************************************
	 * Find the caller.
	 */
	private function find_function_linenum( $content ) {

		// Unlike a method, we need to remove the namespace (if any)
		// from the function name otherwise we won't be able to locate it.
		if ( strpos( $this->ffunction, '->') === false &&
			strpos( $this->ffunction, '::') === false ) {

			$function_parts	= explode('\\', $this->ffunction );
			$this->ffunction	= end( $function_parts );
		}

		$ffunction = preg_quote( $this->ffunction );

		$count = 0;
		foreach( $content as $line ) {
			if ( preg_match("/\bfunction\s+{$ffunction}\s*\(/", $line ) ) {
				$this->fline = $count;
				return;
			}
			$count++;
		}
	}

	/********************************************************************
	 * Display the formatted content.
	 */
	private function display_content( $content ) {

		$buffer = '';
		foreach( $content as $line ) {
			$buffer .= $line;
		}

		// Get file extension
		$extension = pathinfo( $this->ffile, PATHINFO_EXTENSION );
		if ( $extension == 'php') {
			$mode = 'application/x-httpd-php';
		} elseif ( $extension == 'css') {
			$mode = 'text/css';
		} elseif ( in_array( $extension, ['js', 'json'] ) ) {
			 $mode = 'text/javascript';
		} else {
			$mode = 'text/html';
		}

		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title><?php echo esc_html( $this->display_name ) ?></title>
		<link rel="stylesheet" href="<?php echo plugins_url('/static/vendor/codemirror.min.css', dirname( __FILE__ ) ) ?>">
		<link rel="stylesheet" href="<?php echo plugins_url('/static/code-profiler-pro.css', dirname( __FILE__ ) ) ?>">
		<script src="<?php echo plugins_url('/static/vendor/codemirror.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/matchbrackets.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/xml.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/htmlmixed.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/javascript.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/css.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/clike.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/php.min.js', dirname( __FILE__ ) ) ?>"></script>
		<script src="<?php echo plugins_url('/static/vendor/lib/active-line.min.js', dirname( __FILE__ ) ) ?>"></script>
	</head>
<body class="cpview_body">
	<table class="cpview_table">
		<thead>
			<tr style="height:30px">
				<th colspan="2" style="text-align:center;"><?php printf( esc_html__('Viewing: %s', 'code-profiler-pro'), $this->display_name ) ?></th>
			</tr>
		</thead>
	</table>
	<p><textarea id="view-file" style="border:1px solid #ccc"><?php echo esc_textarea( $buffer ) ?></textarea></p>
	<h3 class="cpview_h3">Code Profiler &copy; <?php echo date('Y') ?> <a href="https://code-profiler.com/" target="_blank" rel="noopener noreferrer">Jerome Bruandet</a></h3>
	<script>
		'use strict';
		var editor = CodeMirror.fromTextArea(document.getElementById('view-file'), {
			lineNumbers: true,
			matchBrackets: true,
			mode: "<?php echo $mode ?>",
			indentUnit: 3,
			indentWithTabs: true,
			lineWrapping: true,
			lineNumbers: true,
			<?php
			if (! empty( $this->fline ) ) {
				?>
				styleActiveLine: true,
				<?php
			}
			?>
			readOnly: true
      });
		editor.setSize('100%', '100%');
		<?php
		if (! empty( $this->fline ) ) {
			// Couldn't find another (and simpler) way to center the active line
			?>
			editor.setCursor( {line: <?php echo $this->fline + 20 ?>} );
			editor.setCursor( {line: <?php echo $this->fline ?>} );
			<?php
		}
		?>
	</script>
</body>
</html>
<?php
		exit;
	}

}
// =====================================================================
// EOF
