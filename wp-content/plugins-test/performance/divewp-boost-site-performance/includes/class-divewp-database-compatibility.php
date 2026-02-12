<?php
/**
 * Database Compatibility Layer
 *
 * Provides cross-database compatibility for WordPress plugins.
 * Supports MySQL, MariaDB, PostgreSQL, SQLite, and SQL Server.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     2.0.1
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database Compatibility Class
 * 
 * Handles database engine detection, function mapping, and reserved keyword management
 * across different database systems.
 */
class DiveWP_Database_Compatibility {

    /**
     * Current database engine
     * @var string
     */
    private static $db_engine = null;

    /**
     * Database version
     * @var string  
     */
    private static $db_version = null;

    /**
     * Reserved keywords by database engine
     * @var array
     */
    private static $reserved_keywords = array(
        'mysql' => array(
            'current_time', 'current_date', 'current_timestamp', 'position', 'user', 'table', 
            'index', 'key', 'order', 'group', 'match', 'check', 'trigger', 'window', 'with',
            'rank', 'lead', 'lag', 'over', 'partition', 'rows', 'range', 'cube', 'rollup'
        ),
        'mariadb' => array(
            'current_time', 'current_date', 'current_timestamp', 'position', 'user', 'table',
            'index', 'key', 'order', 'group', 'match', 'check', 'trigger', 'window', 'with',
            'rank', 'lead', 'lag', 'over', 'partition', 'rows', 'range', 'cube', 'rollup'
        ),
        'postgresql' => array(
            'user', 'current_user', 'session_user', 'table', 'index', 'order', 'group',
            'limit', 'offset', 'window', 'with', 'case', 'when', 'then', 'else', 'end',
            'array', 'row', 'column', 'constraint', 'primary', 'foreign', 'references'
        ),
        'sqlite' => array(
            'table', 'index', 'order', 'group', 'limit', 'offset', 'case', 'when', 'then',
            'else', 'end', 'primary', 'foreign', 'references', 'constraint', 'unique'
        ),
        'sqlserver' => array(
            'user', 'table', 'index', 'order', 'group', 'key', 'check', 'constraint',
            'primary', 'foreign', 'references', 'unique', 'with', 'over', 'partition'
        )
    );

    /**
     * Function mapping across database engines
     * @var array
     */
    private static $function_mapping = array(
        // Crypto functions
        'md5' => array(
            'mysql' => 'MD5(%s)',
            'mariadb' => 'MD5(%s)', 
            'postgresql' => 'MD5(%s)',
            'sqlite' => 'NULL', // Not supported
            'sqlserver' => 'HASHBYTES(\'MD5\', %s)'
        ),
        'sha1' => array(
            'mysql' => 'SHA1(%s)',
            'mariadb' => 'SHA1(%s)',
            'postgresql' => 'DIGEST(%s, \'sha1\')',
            'sqlite' => 'NULL', // Not supported
            'sqlserver' => 'HASHBYTES(\'SHA1\', %s)'
        ),
        // Math functions
        'ceiling' => array(
            'mysql' => 'CEILING(%s)',
            'mariadb' => 'CEILING(%s)',
            'postgresql' => 'CEIL(%s)',
            'sqlite' => 'CEIL(%s)', 
            'sqlserver' => 'CEILING(%s)'
        ),
        'floor' => array(
            'mysql' => 'FLOOR(%s)',
            'mariadb' => 'FLOOR(%s)',
            'postgresql' => 'FLOOR(%s)',
            'sqlite' => 'FLOOR(%s)',
            'sqlserver' => 'FLOOR(%s)'
        ),
        // String functions
        'locate' => array(
            'mysql' => 'LOCATE(%s, %s)',
            'mariadb' => 'LOCATE(%s, %s)',
            'postgresql' => 'POSITION(%s IN %s)',
            'sqlite' => 'INSTR(%s, %s)',
            'sqlserver' => 'CHARINDEX(%s, %s)'
        ),
        'length' => array(
            'mysql' => 'LENGTH(%s)',
            'mariadb' => 'LENGTH(%s)',
            'postgresql' => 'LENGTH(%s)',
            'sqlite' => 'LENGTH(%s)',
            'sqlserver' => 'LEN(%s)'
        ),
        // DateTime functions
        'now' => array(
            'mysql' => 'NOW()',
            'mariadb' => 'NOW()',
            'postgresql' => 'NOW()',
            'sqlite' => 'datetime(\'now\')',
            'sqlserver' => 'GETDATE()'
        ),
        'current_date' => array(
            'mysql' => 'CURDATE()',
            'mariadb' => 'CURDATE()',
            'postgresql' => 'CURRENT_DATE',
            'sqlite' => 'date(\'now\')',
            'sqlserver' => 'CAST(GETDATE() AS DATE)'
        ),
        'current_time' => array(
            'mysql' => 'CURTIME()',
            'mariadb' => 'CURTIME()',
            'postgresql' => 'CURRENT_TIME',
            'sqlite' => 'time(\'now\')',
            'sqlserver' => 'CAST(GETDATE() AS TIME)'
        )
    );

    /**
     * Detect database engine and version
     *
     * @return array Database info
     */
    public static function detect_database() {
        global $wpdb;

        if (self::$db_engine !== null) {
            return array(
                'engine' => self::$db_engine,
                'version' => self::$db_version
            );
        }

        // Get database version with caching (static per session)
        $cache_key = 'divewp_db_version';
        $version = wp_cache_get($cache_key, 'divewp_db_compat');
        
        if (false === $version) {
            // DATABASE COMPATIBILITY - Direct query required for cross-database engine detection
            // WordPress has no equivalent function for detecting database engine (MySQL/MariaDB/PostgreSQL/SQLite)
            // This is essential infrastructure for plugin compatibility across different database systems
            $version = $wpdb->get_var("SELECT VERSION()"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- BENCHMARK/COMPAT REQUIREMENT: engine/version detection requires direct query
            // Cache for the entire session since database version is static
            wp_cache_set($cache_key, $version, 'divewp_db_compat', 0);
        }
        
        // Detect engine based on version string
        if (stripos($version, 'mariadb') !== false) {
            self::$db_engine = 'mariadb';
        } elseif (stripos($version, 'mysql') !== false) {
            self::$db_engine = 'mysql';
        } elseif (stripos($version, 'postgresql') !== false) {
            self::$db_engine = 'postgresql';
        } elseif (stripos($version, 'sqlite') !== false) {
            self::$db_engine = 'sqlite';
        } elseif (stripos($version, 'microsoft') !== false || stripos($version, 'sql server') !== false) {
            self::$db_engine = 'sqlserver';
        } else {
            // Default to MySQL for compatibility
            self::$db_engine = 'mysql';
        }

        self::$db_version = $version;

        return array(
            'engine' => self::$db_engine,
            'version' => self::$db_version
        );
    }

    /**
     * Check if identifier is a reserved keyword
     *
     * @param string $identifier The identifier to check
     * @param string $engine Database engine (optional)
     * @return bool True if reserved
     */
    public static function is_reserved_keyword($identifier, $engine = null) {
        if ($engine === null) {
            $db_info = self::detect_database();
            $engine = $db_info['engine'];
        }

        $keywords = isset(self::$reserved_keywords[$engine]) ? self::$reserved_keywords[$engine] : array();
        return in_array(strtolower($identifier), $keywords);
    }

    /**
     * Escape identifier if it's a reserved keyword
     *
     * @param string $identifier The identifier to escape
     * @param string $engine Database engine (optional)
     * @return string Escaped identifier
     */
    public static function escape_identifier($identifier, $engine = null) {
        if ($engine === null) {
            $db_info = self::detect_database();
            $engine = $db_info['engine'];
        }

        if (self::is_reserved_keyword($identifier, $engine)) {
            switch ($engine) {
                case 'mysql':
                case 'mariadb':
                    return "`{$identifier}`";
                case 'postgresql':
                case 'sqlite':
                    return "\"{$identifier}\"";
                case 'sqlserver':
                    return "[{$identifier}]";
                default:
                    return "`{$identifier}`"; // Default MySQL style
            }
        }

        return $identifier;
    }

    /**
     * Get database-specific function
     *
     * @param string $function Function name
     * @param array $params Function parameters
     * @param string $engine Database engine (optional)
     * @return string Database-specific function call
     */
    public static function get_function($function, $params = array(), $engine = null) {
        if ($engine === null) {
            $db_info = self::detect_database();
            $engine = $db_info['engine'];
        }

        $function = strtolower($function);

        if (!isset(self::$function_mapping[$function])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("DiveWP: Unsupported function '{$function}' for database engine '{$engine}'"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- COMPAT/DEBUG: minimal logging gated by WP_DEBUG
            }
            return 'NULL'; // Fallback
        }

        $mapping = self::$function_mapping[$function];
        
        if (!isset($mapping[$engine])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("DiveWP: Function '{$function}' not supported in '{$engine}'"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- COMPAT/DEBUG: minimal logging gated by WP_DEBUG
            }
            return 'NULL'; // Fallback
        }

        $template = $mapping[$engine];
        
        // Replace placeholders with parameters
        if (!empty($params)) {
            return vsprintf($template, $params);
        }

        return $template;
    }

    /**
     * Check if database supports specific features
     *
     * @param string $feature Feature to check
     * @param string $engine Database engine (optional)
     * @return bool True if supported
     */
    public static function supports_feature($feature, $engine = null) {
        if ($engine === null) {
            $db_info = self::detect_database();
            $engine = $db_info['engine'];
        }

        $features = array(
            'fulltext_indexes' => array('mysql', 'mariadb'),
            'window_functions' => array('mysql', 'mariadb', 'postgresql', 'sqlserver'),
            'cte' => array('mysql', 'mariadb', 'postgresql', 'sqlserver'), // Common Table Expressions
            'json_functions' => array('mysql', 'mariadb', 'postgresql'),
            'crypto_functions' => array('mysql', 'mariadb', 'postgresql', 'sqlserver'),
            'temp_tables' => array('mysql', 'mariadb', 'postgresql', 'sqlite', 'sqlserver'),
            'regexp' => array('mysql', 'mariadb', 'postgresql')
        );

        return isset($features[$feature]) && in_array($engine, $features[$feature]);
    }

    /**
     * Get safe column aliases that avoid reserved keywords
     *
     * @param array $columns Array of column names
     * @param string $engine Database engine (optional)
     * @return array Safe column aliases
     */
    public static function get_safe_aliases($columns, $engine = null) {
        if ($engine === null) {
            $db_info = self::detect_database();
            $engine = $db_info['engine'];
        }

        $safe_aliases = array();
        
        foreach ($columns as $column) {
            if (self::is_reserved_keyword($column, $engine)) {
                $safe_aliases[$column] = $column . '_value';
            } else {
                $safe_aliases[$column] = $column;
            }
        }

        return $safe_aliases;
    }

    /**
     * Build a cross-database compatible query
     *
     * @param string $query_template Query template
     * @param array $replacements Replacements for functions/keywords
     * @param string $engine Database engine (optional)
     * @return string Compatible query
     */
    public static function build_compatible_query($query_template, $replacements = array(), $engine = null) {
        if ($engine === null) {
            $db_info = self::detect_database();
            $engine = $db_info['engine'];
        }

        $query = $query_template;

        // Replace function calls
        foreach ($replacements as $placeholder => $function_info) {
            if (is_array($function_info) && isset($function_info['function'])) {
                $function_call = self::get_function(
                    $function_info['function'],
                    isset($function_info['params']) ? $function_info['params'] : array(),
                    $engine
                );
                $query = str_replace($placeholder, $function_call, $query);
            }
        }

        return $query;
    }

    /**
     * Get database engine capabilities summary
     *
     * @return array Capabilities summary
     */
    public static function get_capabilities() {
        $db_info = self::detect_database();
        $engine = $db_info['engine'];
        
        return array(
            'engine' => $engine,
            'version' => $db_info['version'],
            'features' => array(
                'crypto_functions' => self::supports_feature('crypto_functions', $engine),
                'window_functions' => self::supports_feature('window_functions', $engine),
                'fulltext_indexes' => self::supports_feature('fulltext_indexes', $engine),
                'temp_tables' => self::supports_feature('temp_tables', $engine),
                'json_functions' => self::supports_feature('json_functions', $engine),
                'cte' => self::supports_feature('cte', $engine),
                'regexp' => self::supports_feature('regexp', $engine)
            ),
            'reserved_keywords_count' => count(self::$reserved_keywords[$engine]),
            'supported_functions' => array_keys(self::$function_mapping)
        );
    }
} 