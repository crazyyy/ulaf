<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Upgrade;

use Duplicator\Models\GlobalEntity;
use Duplicator\Utils\Logging\DupLog;
use Duplicator\Package\DupPackage;
use Duplicator\Models\TemplateEntity;
use Duplicator\Models\ScheduleEntity;
use Duplicator\Models\SecureGlobalEntity;
use Duplicator\Core\Models\AbstractEntity;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Models\ActivityLog\AbstractLogEvent;
use Duplicator\Models\DynamicGlobalEntity;
use Duplicator\Models\StaticGlobal;
use Duplicator\Models\Storages\AbstractStorageEntity;
use Duplicator\Models\Storages\StoragesUtil;
use Duplicator\Package\AbstractPackage;
use Duplicator\Utils\Crypt\CryptBlowfish;
use Duplicator\Core\UniqueId;
use Duplicator\Utils\UsageStatistics\PluginData;
use Throwable;

/**
 * Utility class managing when the plugin is updated
 */
class UpgradeFunctions
{
    const FIRST_VERSION_WITH_NEW_TABLES                     = '4.5.14-beta2';
    const FIRST_VERSION_DEFAULT_PURGE                       = '4.5.20-beta1';
    const FIRST_VERSION_WITH_MULTISITE_DRM                  = '4.5.21-beta1';
    const FIRST_VERSION_WITH_PACKAGE_TYPE                   = '4.5.22-beta3';
    const FIRST_VERSION_WITH_DYNAMIC_GLOBAL_ENTITY_IMPROVED = '4.5.22-beta8';
    const FIRST_VERSION_WITH_STATIC_GLOBAL_ENTITY_IMPROVED  = '4.5.23-beta1';
    const FIRST_VERSION_WITH_LOGS_SUBFOLDER                 = '4.5.23-beta2';
    const FIRST_VERSION_WITH_WEBSITE_IDENTIFIER_CORE        = '4.5.23-RC3';

    const LEGACY_DB_PACKAGES_TABLE_NAME = 'duplicator_pro_packages';
    const LEGACY_DB_ENTITIES_TABLE_NAME = 'duplicator_pro_entities';

    /**
     * List of deprecated WordPress options to be removed during upgrade
     *
     * @var string[]
     */
    const DEPRECATED_OPTIONS = ['duplicator_pro_package_active'];

    /**
     * This function is executed when the plugin is activated and
     * every time the version saved in the wp_options is different from the plugin version both in upgrade and downgrade.
     *
     * @param false|string $currentVersion current Duplicator version, false if is first installation
     * @param string       $newVersion     new Duplicator Version
     *
     * @return void
     */
    public static function performUpgrade($currentVersion, $newVersion): void
    {
        // *** BEFORE EVERYTHING, INIT DATABASE ***
        self::updateDatabase($currentVersion);

        if ($currentVersion === false) {
            DupLog::trace("PERFORM UPGRADE FROM FIRST INSTALLATION VERSION: " . $newVersion);
        } else {
            DupLog::trace("PERFORM UPGRADE FROM VERSION: " . $currentVersion . " TO " . $newVersion);
        }

        self::storeDupSecureKey($currentVersion);
        self::initUniqueId($currentVersion);

        // Update Global settings
        self::updateStaticGlobalEntity($currentVersion);
        self::moveDataToDynamicGlobalEntity($currentVersion);
        // Setup All Directories
        self::updatePackageType($currentVersion);
        self::migrateLogsToSubfolder($currentVersion);
        self::fixDoubleDefaultStorages();
        //important to run after updateStorages
        self::updateBackupRecordPurgeSettings($currentVersion);
        self::cleanupDeprecatedOptions($currentVersion);
    }

    /**
     * Update database.
     *
     * @param false|string $currentVersion current Duplicator version, false if is first installation
     *
     * @return void
     */
    public static function updateDatabase($currentVersion): void
    {
        self::renameLegacyTablesName($currentVersion);

        // Init tables
        AbstractEntity::initTable();
        AbstractLogEvent::initTable();
        AbstractPackage::initTable();

        // Init entities
        GlobalEntity::getInstance()->save();
        DynamicGlobalEntity::getInstance()->save();
        StoragesUtil::initDefaultStorage();
        TemplateEntity::createDefault();
        TemplateEntity::getManualTemplate(); // If not exists, create it
    }

    /**
     * Save DUP SECURE KEY
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function storeDupSecureKey($currentVersion): void
    {
        if ($currentVersion !== false && SnapUtil::versionCompare($currentVersion, '4.5.0', '<=', 3)) {
            CryptBlowfish::createWpConfigSecureKey(true, true);
        } else {
            CryptBlowfish::createWpConfigSecureKey(false, false);
        }
    }

    /**
     * Init Unique identifier
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function initUniqueId($currentVersion): void
    {
        self::migrateWebsiteIdentifier($currentVersion);
        // Inzialize the identifier
        UniqueId::getInstance();
    }


    /**
     * Rename legacy tables
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    public static function renameLegacyTablesName($currentVersion): void
    {
        if (
            version_compare($currentVersion, self::FIRST_VERSION_WITH_NEW_TABLES, '>=')
        ) {
            return;
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        // RENAME OLD TABLES BEFORE 4.5.14
        $mapping = [
            $wpdb->base_prefix . self::LEGACY_DB_PACKAGES_TABLE_NAME => DupPackage::getTableName(),
            $wpdb->base_prefix . self::LEGACY_DB_ENTITIES_TABLE_NAME => AbstractEntity::getTableName(),
        ];

        foreach ($mapping as $oldTable => $newTable) {
            $oldTableQuery = $wpdb->prepare("SHOW TABLES LIKE %s", $oldTable);
            $newTableQuery = $wpdb->prepare("SHOW TABLES LIKE %s", $newTable);
            if (
                $wpdb->get_var($oldTableQuery) === $oldTable &&
                $wpdb->get_var($newTableQuery) !== $newTable
            ) {
                $wpdb->query("RENAME TABLE `" . esc_sql($oldTable) . "` TO `" . esc_sql($newTable) . "`");
            }
        }
    }

    /**
     * Update legacy package adding type column if empty
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function updatePackageType($currentVersion)
    {
        if ($currentVersion === false || version_compare($currentVersion, self::FIRST_VERSION_WITH_PACKAGE_TYPE, '>=')) {
            return;
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        $table = DupPackage::getTableName();
        $wpdb->query($wpdb->prepare("UPDATE `{$table}` SET type = %s WHERE type IS NULL OR type = ''", DupPackage::getBackupType()));
    }

    /**
     * Update static global entity
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function updateStaticGlobalEntity($currentVersion)
    {
        if ($currentVersion === false || version_compare($currentVersion, self::FIRST_VERSION_WITH_STATIC_GLOBAL_ENTITY_IMPROVED, '>=')) {
            return;
        }

        $global = GlobalEntity::getInstance();
        StaticGlobal::setUninstallPackageOption((bool) $global->uninstall_packages);
        StaticGlobal::setUninstallSettingsOption((bool) $global->uninstall_settings);
        StaticGlobal::setCryptOption((bool) $global->crypt);
        StaticGlobal::setTraceLogEnabledOption(get_option('duplicator_pro_trace_log_enabled', false));
        StaticGlobal::setSendTraceToErrorLogOption(get_option('duplicator_pro_send_trace_to_error_log', false));
    }

    /**
     * Move data to dynamic global entity from secure global entity and global entity
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function moveDataToDynamicGlobalEntity($currentVersion): void
    {
        if (
            $currentVersion === false ||
            version_compare($currentVersion, self::FIRST_VERSION_WITH_DYNAMIC_GLOBAL_ENTITY_IMPROVED, '>=')
        ) {
            return;
        }

        $global  = GlobalEntity::getInstance();
        $sGlobal = SecureGlobalEntity::getInstance();
        $dGlobal = DynamicGlobalEntity::getInstance();

        if (!is_null($global->basic_auth_enabled) && $global->basic_auth_enabled) {
            $dGlobal->setValBool('basic_auth_enabled', $global->basic_auth_enabled);
            $global->basic_auth_enabled = null;
        }

        if (!is_null($global->basic_auth_user) && strlen($global->basic_auth_user) > 0) {
            $dGlobal->setValString('basic_auth_user', $global->basic_auth_user);
            $global->basic_auth_user = null;
        }

        if (!is_null($global->license_key_visible) && $global->license_key_visible) {
            $dGlobal->setValInt('license_key_visible', $global->license_key_visible);
            $global->license_key_visible = null;
        }

        if (!is_null($sGlobal->lkp) && strlen($sGlobal->lkp) > 0) {
            $dGlobal->setValString('license_key_visible_pwd', $sGlobal->lkp);
            $sGlobal->lkp = null;
        }

        if (!is_null($sGlobal->basic_auth_password) && strlen($sGlobal->basic_auth_password) > 0) {
            $dGlobal->setValString('basic_auth_password', $sGlobal->basic_auth_password);
            $sGlobal->basic_auth_password = null;
        }

        $global->save();
        $sGlobal->save();
        $dGlobal->save();
    }


    /**
     * Removed double default storages
     *
     * @return void
     */
    protected static function fixDoubleDefaultStorages()
    {
        try {
            $defaultStorageId = StoragesUtil::getDefaultStorageId();
            $doubleStorageIds = StoragesUtil::removeDoubleDefaultStorages();

            if ($doubleStorageIds === []) {
                return;
            }

            // Auto assign references to the correct default storage
            ScheduleEntity::listCallback(
                function (ScheduleEntity $schedule) use ($defaultStorageId, $doubleStorageIds): void {
                    $save = false;
                    foreach ($schedule->storage_ids as $key => $storageId) {
                        if (!in_array($storageId, $doubleStorageIds)) {
                            continue;
                        }
                        $schedule->storage_ids[$key] = $defaultStorageId;
                        $save                        = true;
                    }
                    $schedule->storage_ids = array_values(array_unique($schedule->storage_ids));
                    if ($save) {
                        $schedule->save();
                    }
                }
            );

            DupPackage::dbSelectByStatusCallback(
                function (DupPackage $package) use ($defaultStorageId, $doubleStorageIds): void {
                    $save = false;
                    if (in_array($package->active_storage_id, $doubleStorageIds)) {
                        $package->active_storage_id = $defaultStorageId;
                        $save                       = true;
                    }
                    foreach ($package->upload_infos as $key => $info) {
                        if (!in_array($info->getStorageId(), $doubleStorageIds)) {
                            continue;
                        }
                        $info->setStorageId($defaultStorageId);
                        $save = true;
                    }
                    if ($save) {
                        $package->save();
                    }
                },
                [
                    [
                        'op'     => '>=',
                        'status' => AbstractPackage::STATUS_COMPLETE,
                    ],
                ]
            );
        } catch (Throwable $e) {
            DupLog::trace("Error fixing remove double storage: " . $e->getMessage());
            return;
        }
    }

    /**
     * Sets the correct backup purge setting based on previous default local storage settings
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    public static function updateBackupRecordPurgeSettings($currentVersion): void
    {
        if ($currentVersion === false || version_compare($currentVersion, self::FIRST_VERSION_DEFAULT_PURGE, '>=')) {
            return;
        }

        $global = GlobalEntity::getInstance();
        if (StoragesUtil::getDefaultStorage()->isPurgeEnabled()) {
            $global->setPurgeBackupRecords(AbstractStorageEntity::BACKUP_RECORDS_REMOVE_DEFAULT);
        } else {
            $global->setPurgeBackupRecords(AbstractStorageEntity::BACKUP_RECORDS_REMOVE_NEVER);
        }

        $global->save();
    }

    /**
     * Migrate existing log files from root directory to logs subfolder
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function migrateLogsToSubfolder($currentVersion): void
    {
        if ($currentVersion === false || version_compare($currentVersion, self::FIRST_VERSION_WITH_LOGS_SUBFOLDER, '>=')) {
            return;
        }

        try {
            DupLog::trace("MIGRATION: Moving log files to logs subfolder");

            // Ensure logs directory exists
            if (!file_exists(DUPLICATOR_PRO_PATH_LOGS)) {
                SnapIO::mkdirP(DUPLICATOR_PRO_PATH_LOGS);
                SnapIO::chmod(DUPLICATOR_PRO_PATH_LOGS, 'u+rwx');
                SnapIO::createSilenceIndex(DUPLICATOR_PRO_PATH_LOGS);
            }

            // Use SnapIO::regexGlob for more robust file discovery
            $logFiles = SnapIO::regexGlob(DUPLICATOR_PRO_SSDIR_PATH, [
                'regexFile'   => [
                    '/.*_log\.txt$/',
                    '/.*_log_bak\.txt$/',
                    '/.*\.log$/',
                ],
                'regexFolder' => false,
                'recursive'   => false,
            ]);

            $migratedCount = 0;
            foreach ($logFiles as $oldPath) {
                $filename = basename($oldPath);
                $newPath  = DUPLICATOR_PRO_PATH_LOGS . '/' . $filename;

                if (SnapIO::rename($oldPath, $newPath)) {
                    $migratedCount++;
                }
            }

            DupLog::trace("MIGRATION: Moved {$migratedCount} log files to logs subfolder - old location is now clean");
        } catch (Throwable $e) {
            DupLog::trace("MIGRATION: Error moving log files: " . $e->getMessage());
        }
    }

    /**
     * Migrate website identifier from PluginData to WebsiteIdentifier core class
     *
     * Copies the identifier from the old location (duplicator_pro_plugin_data_stats JSON)
     * to the new location option and removes it from the old location.
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function migrateWebsiteIdentifier($currentVersion): void
    {
        if ($currentVersion === false || version_compare($currentVersion, self::FIRST_VERSION_WITH_WEBSITE_IDENTIFIER_CORE, '>=')) {
            return;
        }

        try {
            $pluginDataOption = get_option(PluginData::PLUGIN_DATA_OPTION_KEY, false);
            if ($pluginDataOption === false) {
                return;
            }

            $pluginDataArray = json_decode($pluginDataOption, true);
            if (!is_array($pluginDataArray) || !isset($pluginDataArray['identifier']) || strlen($pluginDataArray['identifier']) === 0) {
                return;
            }

            $oldIdentifier = $pluginDataArray['identifier'];
            update_option(UniqueId::OPTION_KEY, $oldIdentifier, true);
            DupLog::trace("MIGRATION: Copied identifier to new location: " . UniqueId::OPTION_KEY);
        } catch (Throwable $e) {
            DupLog::trace("MIGRATION: Error migrating website identifier: " . $e->getMessage());
        }
    }

    /**
     * Remove deprecated WordPress options that are no longer used
     *
     * This function cleans up old options from the wp_options table that are
     * no longer needed by the plugin to prevent database bloat.
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function cleanupDeprecatedOptions($currentVersion): void
    {
        try {
            foreach (self::DEPRECATED_OPTIONS as $optionName) {
                if (delete_option($optionName)) {
                    DupLog::trace("CLEANUP: Removed deprecated option: {$optionName}");
                }
            }
        } catch (Throwable $e) {
            DupLog::trace("CLEANUP: Error removing deprecated options: " . $e->getMessage());
        }
    }
}
