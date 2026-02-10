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
// Display contextual help below each chart and table.

if ( $type == 'slugs') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p>
					<?php esc_html_e('This chart shows the execution time of all activated plugins and the current theme, in seconds and percentage, for the current profile. It can be downloaded as an image or even exported as a CSV file (comma-separated values) that you can open with your favorite spreadsheet editor.', 'code-profiler-pro') ?>
					<br />
					<?php printf(
						esc_html__('Note that the execution time excludes any kind of website optimization (caching, CDN, PHP OPcache etc). Consult the %sFAQ%s for more details about that.', 'code-profiler-pro'),
						'<a href="?page=code-profiler-pro&cptab=faq">',
						'</a>'
					) ?>
				</p>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'scripts') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p>
					<?php esc_html_e('That section shows the time it took for each PHP script to execute its code while your website was loading. The "Time" column shows their respective processing time in seconds and, with a green horizontal bar, the percentage it represents.', 'code-profiler-pro') ?>
				</p>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'functions') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p>
					<?php esc_html_e('That section shows the time it took for each class/method or function to execute its code while your website was loading. The "Time" column shows their respective processing time in seconds and, with a green horizontal bar, the percentage it represents. The "Caller" column shows the sum of all functions that called it.', 'code-profiler-pro') ?>
					<br />
					<?php
						printf(
							/* Translators: %s = Settings */
							esc_html__('The two action links below each item let you view the corresponding function, or the list of all caller functions. If you want to display a backtrace for each caller function, you can enable that feature in the %s page.', 'code-profiler-pro'),
							'<a href="?page=code-profiler-pro&cptab=settings">'. __('Settings', 'code-profiler-pro') . '</a>'
						); ?>
				</p>
				<p>
					<?php echo esc_html__('In addition to method and function names, the following labels can be found in the "Function" column:', 'code-profiler-pro') .'<br /><code>{main}</code>: '. esc_html__('It means the whole script was called, not just a specific method, using a PHP function such as require or include, which you can view by clicking on the "View caller" link.', 'code-profiler-pro') .'<br /><code>{closure}</code>: '. esc_html__('It means it is an anonymous function, i.e., a function that can be created without a specified name.', 'code-profiler-pro') ?>
				</p>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'queries') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p>
					<?php esc_html_e('That section shows the time it took to process each database query. Note that only queries from the plugins and the theme are displayed, not WordPress core queries.', 'code-profiler-pro') ?>
					<br />
					<?php esc_html_e('The "Time" column shows their respective processing time in seconds and, with a green horizontal bar, the percentage it represents. The "Order" column lets you sort them by execution order.', 'code-profiler-pro') ?>
					<br />
					<?php esc_html_e('The "View backtrace" action link below each item lets you view a list of the scripts and function calls that lead to the query, sorted by execution order.', 'code-profiler-pro') ?>
				</p>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'connections') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p>
					<?php esc_html_e('That section shows all HTTP connections originating from your WordPress website (WordPress core, its plugins and the theme).', 'code-profiler-pro') ?>
					<br />
					<?php esc_html_e('The "Code" column shows the HTTP response status code and, if you move your mouse over a value, it will display the response message.', 'code-profiler-pro') ?>
					<br />
					<?php esc_html_e('The "View backtrace" action link below each item lets you view a list of the scripts and function calls that lead to the HTTP request.', 'code-profiler-pro') ?>
				</p>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'iolist') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p>
					<?php esc_html_e('That section shows all files and directories with their respective I/O operations that occurred while your website was loading. The default sorting order is based on the control flow, i.e., the order in which the PHP code was executed while the website was loading. The following 10 operations are monitored by Code Profiler:', 'code-profiler-pro') ?>
				</p>
				<p>
					<?php esc_html_e('Open file (read & write), delete file, rename file or directory, remove directory, make directory, open directory, change mode, change group, change owner and change timestamps.', 'code-profiler-pro') ?>
				</p>
				<p>
					<?php esc_html_e('Every files will be displayed. For instance, if a plugin or theme created a temporary file or wrote to a log, it would appear in the list too.', 'code-profiler-pro'); echo ' '; esc_html_e('Regarding open file operations, Code Profiler displays the mode parameter used to specify the type of access. It may be any of the following:', 'code-profiler-pro') ?>
				</p>
				<table class="widefat" style="border:none;box-shadow:none">
					<tr>
						<td style="width:50%">
							<code>r</code>: <?php esc_html_e('Open for reading only; place the file pointer at the beginning of the file.', 'code-profiler-pro') ?>
						</td>
						<td style="width:50%">
							<code>r+</code>: <?php esc_html_e('Open for reading and writing; place the file pointer at the beginning of the file.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>w</code>: <?php esc_html_e('Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>w+</code>: <?php esc_html_e('Open for reading and writing; otherwise it has the same behavior as \'w\'.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>a</code>: <?php esc_html_e('Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek has no effect, writes are always appended.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>a+</code>: <?php esc_html_e('Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek only affects the reading position, writes are always appended.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>x</code>: <?php esc_html_e('Create and open for writing only; place the file pointer at the beginning of the file. If the file does not exist, attempt to create it.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>x+</code>: <?php esc_html_e('Create and open for reading and writing; otherwise it has the same behavior as \'x\'.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>c</code>: <?php esc_html_e('Open the file for writing only. If the file does not exist, it is created. If it exists, it is neither truncated (as opposed to \'w\'), nor the call to this function fails (as is the case with \'x\'). The file pointer is positioned on the beginning of the file.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>c+</code>: <?php esc_html_e('Open the file for reading and writing; otherwise it has the same behavior as \'c\'.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>e</code>: <?php esc_html_e('Set close-on-exec flag on the opened file descriptor. Only available in PHP compiled on POSIX.1-2008 conform systems.', 'code-profiler-pro') ?>
						</td>
						<td>&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'iostats') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p><?php esc_html_e('The chart displays all file I/O operations that occurred while your website was loading. The following operations are monitored by Code Profiler:', 'code-profiler-pro') ?></p>
				<table class="widefat" style="border:none;box-shadow:none">
					<tr>
						<td style="width:50%">
							<code>cast</code>: <?php esc_html_e('Retrieve the underlaying resource (stream_select).', 'code-profiler-pro') ?>
						</td>
						<td style="width:50%">
							<code>chgrp</code>: <?php esc_html_e('Change file group.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>chmod</code>: <?php esc_html_e('Change file mode.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>chown</code>: <?php esc_html_e('Change file owner.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>close</code>: <?php esc_html_e('Close a resource (fclose).', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>closedir</code>: <?php esc_html_e('Close directory handle.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>eof</code>: <?php esc_html_e('Test for end-of-file on a file pointer (feof).', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>flush</code>: <?php esc_html_e('Flush the output (fflush).', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>lock</code>: <?php esc_html_e('Advisory file locking (flock).', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>mkdir</code>: <?php esc_html_e('Create a directory.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>open</code>: <?php esc_html_e('Open file (e.g., fopen, file_get_contents).', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>opendir</code>: <?php esc_html_e('Open directory handle.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>read</code>: <?php esc_html_e('Read from stream (e.g., fread, fgets).', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>readdir</code>: <?php esc_html_e('Read entry from directory handle.', 'code-profiler-pro') ?>
						</td>
					<tr>
					<tr>
						<td>
							<code>rename</code>: <?php esc_html_e('Rename a file or directory.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>rewinddir</code>: <?php esc_html_e('Rewind directory handle.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>rmdir</code>: <?php esc_html_e('Remove a directory.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>seek</code>: <?php esc_html_e('Seek to specific location in a stream (fseek).', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>set_option</code>: <?php esc_html_e('Change stream options.', 'code-profiler-pro') ?>
						</td><td>
							<code>stat</code>: <?php esc_html_e('Retrieve information about a file resource (fstat).', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>tell</code>: <?php esc_html_e('Retrieve the current position of a stream. (fseek).', 'code-profiler-pro') ?>
						</td><td>
							<code>truncate</code>: <?php esc_html_e('Truncate stream (ftruncate).', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>touch</code>: <?php esc_html_e('Set access and modification time of file.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>unlink</code>: <?php esc_html_e('Delete a file.', 'code-profiler-pro') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>url_stat</code>: <?php esc_html_e('Retrieve information about a file. This method is called in response to all stat related functions, such as copy, filesize, filetype, is_dir, is_file, file_exists etc.', 'code-profiler-pro') ?>
						</td>
						<td>
							<code>write</code>: <?php esc_html_e('Write to stream (fwrite).', 'code-profiler-pro') ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'diskio') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo $section_list[ $section ] ?></h4>
				<p><?php esc_html_e('The chart shows the total amount of disk I/O read and write data, in bytes, that occurred while your website was loading. It includes all activated plugins, the current theme and also WordPress.', 'code-profiler-pro') ?></p>

			</td>
		</tr>
	</table>
	<?php
} elseif ( $type == 'license') {

	?>
	<table class="widefat">
		<tr>
			<td>
				<p><?php esc_html_e('You can use your license on the following subdomains as well, at no extra cost:', 'code-profiler-pro') ?></p>
				<ul class="license-help-view">
					<li><?php printf('https://dev.%s/', esc_html( $cp_options['domain'] ) ) ?></li>
					<li><?php printf('https://local.%s/', esc_html( $cp_options['domain'] ) ) ?></li>
					<li><?php printf('https://stage.%s/', esc_html( $cp_options['domain'] ) ) ?></li>
					<li><?php printf('https://staging.%s/', esc_html( $cp_options['domain'] ) ) ?></li>
					<li><?php printf('https://test.%s/', esc_html( $cp_options['domain'] ) ) ?></li>
					<li><?php printf('https://testing.%s/', esc_html( $cp_options['domain'] ) ) ?></li>
				</ul>
			</td>
		</tr>
	</table>
	<?php

// Profiles list
} elseif ( $type == 'profiles_list') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php esc_html_e('Profiles List', 'code-profiler-pro') ?></h4>
				<p><?php esc_html_e('This page shows all saved profiles with sortable columns:', 'code-profiler-pro') ?></p>
				<ul class="license-help-view">
					<li><?php esc_html_e('Profile: name of the saved profile.', 'code-profiler-pro') ?></li>
					<li><?php esc_html_e('Date: creation date of the profile.', 'code-profiler-pro') ?></li>
					<li><?php esc_html_e('Item: number of plugins + the current theme.', 'code-profiler-pro') ?></li>
					<li><?php esc_html_e('Time: execution time in second.', 'code-profiler-pro') ?></li>
					<li><?php esc_html_e('Memory: peak memory in megabytes.', 'code-profiler-pro') ?></li>
					<li><?php esc_html_e('File I/O: sum of all file I/O operations.', 'code-profiler-pro') ?></li>
					<li><?php esc_html_e('SQL: sum of database queries (WordPress, all plugins and the theme).', 'code-profiler-pro') ?></li>
					<li><?php esc_html_e('HTTP: sum of all HTTP connections to a remote website.', 'code-profiler-pro') ?></li>
				</ul>
			</td>
		</tr>
	</table>
	<?php
}
// =====================================================================
// EOF
