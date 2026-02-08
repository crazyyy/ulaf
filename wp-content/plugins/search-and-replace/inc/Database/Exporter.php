<?php
declare(strict_types=1);

namespace Inpsyde\SearchReplace\Database;

use WP_Error;

class Exporter {

	private WP_Error $errors;
	private string $backup_dir;
	private Replace $replace;
	private Manager $dbm;

	private int $page_size = 100;
	private string $backup_filename = '';

	/** @var resource|null */
	private $fp = null;

	private array $csv_data = [];

	public function __construct( Replace $replace, Manager $dbm, WP_Error $wp_error ) {
		$this->errors     = $wp_error;
		$this->backup_dir = get_temp_dir();
		$this->replace    = $replace;
		$this->dbm        = $dbm;
	}

	public function db_backup(
		string $search = '',
		string $replace = '',
		array $tables = [],
		bool $domain_replace = false,
		string $new_table_prefix = '',
		?string $csv = null
	): array {

		if (empty($tables)) {
			$tables = $this->dbm->get_tables();
		}

		$report = [
			'errors'        => null,
			'changes'       => [],
			'tables'        => 0,
			'changes_count' => 0,
			'filename'      => '',
		];

		$table_prefix   = $this->dbm->get_base_prefix();
		$wp_blogs_table = $table_prefix . 'blogs';

		$this->backup_filename = DB_NAME . '_' . ($new_table_prefix ?: $table_prefix) . '.sql';

		if (!is_writable($this->backup_dir)) {
			$this->errors->add(9, __('The backup directory is not writable!', 'search-and-replace'));
			return $report;
		}

		$this->fp = $this->open($this->backup_dir . $this->backup_filename);
		if (!$this->fp) {
			$this->errors->add(8, __('Could not open the backup file for writing!', 'search-and-replace'));
			return $report;
		}

		$charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

		$this->stow("# WordPress MySQL database backup\n");
		$this->stow('# Generated: ' . date('Y-m-d H:i:s') . "\n");
		$this->stow("# Database: `" . DB_NAME . "`\n");
		$this->stow("/*!40101 SET NAMES {$charset} */;\n\n");

		foreach ($tables as $table) {
			$report['tables']++;

			if ($table === $wp_blogs_table && $domain_replace && is_multisite()) {
				$search  = preg_replace('~^https?://~', '', $search);
				$replace = preg_replace('~^https?://~', '', $replace);
			}

			$table_report = $this->backup_table(
				$search,
				$replace,
				$table,
				$new_table_prefix,
				$csv
			);

			if ($table_report['change'] > 0) {
				$report['changes'][$table] = $table_report;
				$report['changes_count'] += $table_report['change'];
			}
		}

		$this->close();

		if ($this->errors->has_errors()) {
			$report['errors'] = $this->errors;
		}

		$report['filename'] = $this->backup_filename;
		return $report;
	}

	private function open(string $filename, string $mode = 'wb') {
		return fopen($filename, $mode) ?: null;
	}

	private function stow(string $line): void {
		if ($this->fp && fwrite($this->fp, $line) === false) {
			$this->errors->add(4, __('Error writing to backup file.', 'search-and-replace'));
		}
	}

	private function close(): void {
		if (is_resource($this->fp)) {
			fclose($this->fp);
			$this->fp = null;
		}
	}

	public function backup_table(
		string $search,
		string $replace,
		string $table,
		string $new_table_prefix = '',
		?string $csv = null
	): array {

		$table_report = [
			'table_name' => $table,
			'rows'       => 0,
			'change'     => 0,
			'changes'    => [],
		];

		$table_prefix = $this->dbm->get_base_prefix();
		$new_table    = $new_table_prefix
			? $this->get_new_table_name($table, $new_table_prefix)
			: $table;

		$this->stow("\n# Table: `{$new_table}`\n");
		$this->stow("DROP TABLE IF EXISTS `{$new_table}`;\n");

		$create = $this->dbm->get_create_table_statement($table);
		if (!$create) {
			$this->errors->add(2, "SHOW CREATE TABLE failed for {$table}");
			return $table_report;
		}

		$sql = $new_table !== $table
			? str_replace($table, $new_table, $create[0][1])
			: $create[0][1];

		$this->stow($sql . ";\n\n");

		$rows = $this->dbm->get_rows($table);
		$pages = (int) ceil($rows / $this->page_size);

		for ($p = 0; $p < $pages; $p++) {
			$data = $this->dbm->get_table_content(
				$table,
				$p * $this->page_size,
				$this->page_size
			);

			foreach ($data as $row) {
				$table_report['rows']++;
				$values = [];

				foreach ($row as $value) {
					if (is_string($value)) {
						$value = str_replace($search, $replace, $value);
						$value = "'" . esc_sql($value) . "'";
					} elseif ($value === null) {
						$value = 'NULL';
					}
					$values[] = $value;
				}

				$this->stow(
					"INSERT INTO `{$new_table}` VALUES (" . implode(', ', $values) . ");\n"
				);
			}
		}

		return $table_report;
	}

	private function get_new_table_name(string $table, string $new_prefix): string {
		$old = $this->dbm->get_base_prefix();
		return $new_prefix . substr($table, strlen($old));
	}

	public function deliver_backup(string $filename, bool $compress = false): bool {

		$path = $this->backup_dir . $filename;
		if (!file_exists($path)) {
			wp_die(__('Backup file not found.', 'search-and-replace'));
		}

		if ($compress) {
			$path = $this->gzip($path);
		}

		header('Content-Type: application/octet-stream');
		header('Content-Length: ' . filesize($path));
		header('Content-Disposition: attachment; filename=' . basename($path));

		readfile($path);
		exit;
	}

	private function gzip(string $file): string {

		$gz = $file . '.gz';
		if (file_exists($gz)) {
			unlink($gz);
		}

		if (function_exists('gzencode')) {
			$data = file_get_contents($file);
			file_put_contents($gz, gzencode($data, 9));
			unlink($file);
			return $gz;
		}

		return $file;
	}
}
