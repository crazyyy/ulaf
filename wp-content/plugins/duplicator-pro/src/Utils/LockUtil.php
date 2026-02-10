<?php

namespace Duplicator\Utils;

use Duplicator\Core\UniqueId;
use Duplicator\Models\GlobalEntity;
use Duplicator\Utils\Logging\DupLog;
use Throwable;

/**
 * Lock utility
 */
class LockUtil
{
    const TEST_SQL_LOCK_NAME = 'duplicator_pro_test_lock';
    const LOCK_MODE_FILE     = 0;
    const LOCK_MODE_SQL      = 1;

    /** @var false|resource */
    protected static $lockingFile = false;

    /**
     * Get lock file path with unique hash based on site identifier
     *
     * This ensures the lock file is in a writable directory (backup storage)
     * and uses a unique name per WordPress installation
     *
     * @return string Lock file path
     */
    protected static function getLockFilePath(): string
    {
        static $lockFilePath = null;

        if ($lockFilePath === null) {
            // Get unique site identifier (immutable)
            $identifier = UniqueId::getInstance()->getIdentifier();

            // Generate short hash (8 chars sufficient for uniqueness)
            $hash = substr(md5($identifier), 0, 8);

            // Lock file in backup storage directory (guaranteed writable)
            $lockFilePath = DUPLICATOR_PRO_SSDIR_PATH . '/building_lock_' . $hash . '.tmp';
        }

        return $lockFilePath;
    }

    /**
     * Test if file locking is reliable on this filesystem
     *
     * Detects NFS and other distributed filesystems where flock() may not work properly.
     * On local filesystems (ext4, XFS, NTFS), flock() works correctly.
     * On NFS/CIFS, flock() may report success but not actually provide mutual exclusion.
     *
     * @return bool True if file locking is reliable
     */
    protected static function isFileLockReliable(): bool
    {
        // Cache result to avoid repeated filesystem I/O operations (5-200ms per test)
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $fp1 = false;
        $fp2 = false;

        try {
            // Ensure backup storage directory exists
            if (!wp_mkdir_p(DUPLICATOR_PRO_SSDIR_PATH)) {
                    throw new \Exception("Cannot create backup storage directory: " . DUPLICATOR_PRO_SSDIR_PATH);
            }

            // Use the actual lock file for testing (same file we'll use for real locks)
            $testFile = self::getLockFilePath();

            // Open first handle
            $fp1 = fopen($testFile, 'c+');
            if ($fp1 === false) {
                $error = error_get_last();
                throw new \Exception("Cannot open test file: " . ($error['message'] ?? 'unknown error'));
            }

            // Write test data
            fwrite($fp1, 'test_' . time());
            fflush($fp1);

            // Acquire exclusive lock
            if (!flock($fp1, LOCK_EX | LOCK_NB)) {
                throw new \Exception("Cannot acquire initial lock");
            }

            // CRITICAL TEST: Try to open same file from another handle
            // On reliable local filesystems, the second lock should FAIL
            // On NFS/CIFS, it might SUCCEED (indicating unreliable locking)
            $fp2 = fopen($testFile, 'r+');
            if ($fp2 !== false) {
                // Try to acquire exclusive lock on second handle
                $lockAcquired2 = flock($fp2, LOCK_EX | LOCK_NB);

                // If BOTH handles acquired exclusive lock → NFS/unreliable!
                if ($lockAcquired2) {
                    throw new \Exception("File lock test: UNRELIABLE (both handles got exclusive lock - likely NFS/CIFS)");
                }
            }

            DupLog::trace("File lock test: Lock is reliable");
            $cached = true;
        } catch (Throwable $e) {
            // In case of error, assume not reliable
            DupLog::trace("File lock test: ERROR - " . $e->getMessage());
            $cached = false;
        } finally {
            // Guaranteed cleanup of all resources
            if ($fp2 !== false) {
                $lockAcquired2 = $lockAcquired2 ?? false;
                if ($lockAcquired2) {
                    flock($fp2, LOCK_UN);
                }
                fclose($fp2);
            }

            if ($fp1 !== false) {
                flock($fp1, LOCK_UN);
                fclose($fp1);
            }

            // Note: We don't delete the lock file - it will be reused for actual locking
        }

        return $cached;
    }

    /**
     * Return default lock type with intelligent detection
     *
     * Strategy:
     * 1. Test if FILE lock is reliable (detects NFS/distributed filesystems)
     * 2. If reliable → use FILE (better performance, no DB impact)
     * 3. If not reliable → fallback to SQL lock
     * 4. If SQL also fails → use FILE as last resort (works on 95% of hosting)
     *
     * @return int Enum lock type (LOCK_MODE_FILE or LOCK_MODE_SQL)
     */
    public static function getDefaultLockType(): int
    {
        // Cache result to avoid repeated SQL queries (3 queries per call when file locks unreliable)
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        // Test file lock reliability first (preferred method)
        if (self::isFileLockReliable()) {
            $cached = self::LOCK_MODE_FILE;
            return $cached;
        }

        DupLog::trace("File lock unreliable (likely NFS/distributed filesystem), testing SQL lock...");

        // Fallback to SQL lock if available
        if (self::getSqlLock(self::TEST_SQL_LOCK_NAME)) {
            $sqlWorks = self::checkSqlLock(self::TEST_SQL_LOCK_NAME);
            self::releaseSqlLock(self::TEST_SQL_LOCK_NAME);

            if ($sqlWorks) {
                $cached = self::LOCK_MODE_SQL;
                return $cached;
            }
        }

        // Last resort: use FILE even if unreliable (works on most hosting)
        // Runtime fallback in getFileLock() will switch to SQL if file open fails
        DupLog::trace("Both locks have issues, defaulting to LOCK_MODE_FILE as last resort");
        $cached = self::LOCK_MODE_FILE;
        return $cached;
    }

    /**
     * Return lock mode
     *
     * @return int Lock mode ENUM self::LOCK_MODE_FILE or self::LOCK_MODE_SQL
     */
    public static function getLockMode()
    {
        return GlobalEntity::getInstance()->lock_mode;
    }

    /**
     * Lock process
     *
     * @return bool true if lock acquired
     */
    public static function lockProcess()
    {
        if (self::getLockMode() == self::LOCK_MODE_SQL) {
            return self::getSqlLock();
        } else {
            return self::getFileLock();
        }
    }

    /**
     * Unlock process
     *
     * @return bool true if lock released
     */
    public static function unlockProcess()
    {
        if (self::getLockMode() == self::LOCK_MODE_SQL) {
            return self::releaseSqlLock();
        } else {
            return self::releaseFileLock();
        }
    }

    /**
     * Get file lock
     *
     * Attempts to acquire an exclusive file lock. If the file cannot be opened,
     * automatically switches to SQL lock mode permanently.
     *
     * @return bool True if file lock acquired
     */
    protected static function getFileLock()
    {
        $global = GlobalEntity::getInstance();
        if ($global->lock_mode == self::LOCK_MODE_SQL) {
            return false;
        }

        $lockFilePath = self::getLockFilePath();

        if (
            self::$lockingFile === false &&
            (self::$lockingFile = fopen($lockFilePath, 'c+')) === false
        ) {
            // Problem opening the locking file - auto switch to SQL lock mode
            $error = error_get_last();
            DupLog::trace(
                "Problem opening lock file at {$lockFilePath}: " .
                ($error['message'] ?? 'unknown error') .
                ", auto-switching to SQL lock mode"
            );
            $global->lock_mode = self::LOCK_MODE_SQL;
            $global->save();
            return false;
        }

        $acquired_lock = flock(self::$lockingFile, LOCK_EX | LOCK_NB);

        if (!$acquired_lock) {
            DupLog::trace("File lock denied: {$lockFilePath} (another process is running)");
        }

        return $acquired_lock;
    }

    /**
     * Release file lock
     *
     * @return bool True if file lock released
     */
    protected static function releaseFileLock()
    {
        if (self::$lockingFile === false) {
            return true;
        }

        $success = true;
        if (!flock(self::$lockingFile, LOCK_UN)) {
            DupLog::trace("File lock can't release");
            $success = false;
        }

        if (fclose(self::$lockingFile) === false) {
            DupLog::trace("Can't close file lock file");
        }

        self::$lockingFile = false;

        return $success;
    }
    /**
     * Gets an SQL lock request
     *
     * @see releaseSqlLock()
     *
     * @param string $lock_name The name of the lock to check
     *
     * @return bool Returns true if an SQL lock request was successful
     */
    protected static function getSqlLock($lock_name = 'duplicator_pro_lock'): bool
    {
        global $wpdb;

        $query_string = $wpdb->prepare("SELECT GET_LOCK(%s, 0)", $lock_name);
        $ret_val      = $wpdb->get_var($query_string);

        if ($ret_val == 0) {
            return false;
        } elseif ($ret_val == null) {
            DupLog::trace("Error retrieving mysql lock {$lock_name}");
            return false;
        }

        return true;
    }

    /**
     * Rturn true if sql lock is set
     *
     * @param string $lock_name lock nam
     *
     * @return bool
     */
    protected static function checkSqlLock($lock_name = 'duplicator_pro_lock'): bool
    {
        global $wpdb;

        $query_string = $wpdb->prepare("SELECT IS_USED_LOCK(%s)", $lock_name);
        $ret_val      = $wpdb->get_var($query_string);

        return $ret_val > 0;
    }

    /**
     * Releases the SQL lock request
     *
     * @see getSqlLock()
     *
     * @param string $lock_name The name of the lock to release
     *
     * @return bool
     */
    protected static function releaseSqlLock($lock_name = 'duplicator_pro_lock'): bool
    {
        global $wpdb;

        $query_string = $wpdb->prepare("SELECT RELEASE_LOCK(%s)", $lock_name);
        $ret_val      = $wpdb->get_var($query_string);

        if ($ret_val == 0) {
            DupLog::trace("Failed releasing sql lock {$lock_name} because it wasn't established by this thread");
            return false;
        } elseif ($ret_val == null) {
            DupLog::trace("Tried to release sql lock {$lock_name} but it didn't exist");
            return false;
        }

        return true;
    }
}
