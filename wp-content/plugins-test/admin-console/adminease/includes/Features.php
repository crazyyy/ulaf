<?php
namespace AdminEase;

use AdminEase\Features\AllowCustomFileExtensionUpload;
use AdminEase\Features\AllowSvgUpload;
use AdminEase\Features\AutoLogoutUser;
use AdminEase\Features\AutosaveInterval;
use AdminEase\Features\BlockAccessSensitiveFiles;
use AdminEase\Features\BlockAuthorScans;
use AdminEase\Features\BlockDirectoryBrowsing;
use AdminEase\Features\BlockSpecificCountries;
use AdminEase\Features\BlockSpecificBots;
use AdminEase\Features\BulkDeletePosts;
use AdminEase\Features\CorsHeader;
use AdminEase\Features\DisableAdminNotices;
use AdminEase\Features\DisableComments;
use AdminEase\Features\DisableEmbeds;
use AdminEase\Features\DisableEmojis;
use AdminEase\Features\DisableFileEdit;
use AdminEase\Features\DisallowFileMods;
use AdminEase\Features\DisableFrontendUserRegistration;
use AdminEase\Features\DisableGutenberg;
use AdminEase\Features\DisablePingbacksAndTrackbacks;
use AdminEase\Features\DisableRestApi;
use AdminEase\Features\DisableScriptConcatenation;
use AdminEase\Features\DisableWpCron;
use AdminEase\Features\DisableXmlRpc;
use AdminEase\Features\DragAndDropOrderingPosts;
use AdminEase\Features\DragAndDropOrderingTaxonomies;
use AdminEase\Features\EmptyTrashDays;
use AdminEase\Features\ForceStrongPasswords;
use AdminEase\Features\HideAdminBar;
use AdminEase\Features\HidePhpVersion;
use AdminEase\Features\HideWordpressVersion;
use AdminEase\Features\MaxExecutionTime;
use AdminEase\Features\MediaLibraryInfiniteScrolling;
use AdminEase\Features\NumberPostsRevisions;
use AdminEase\Features\PostsMetadataBox;
use AdminEase\Features\RedirectAfterLoginLogout;
use AdminEase\Features\TaxonomyMetaBox;
use AdminEase\Features\UpdatesAndNotifications;
use AdminEase\Features\UploadMaxFileSize;
use AdminEase\Features\WpDebug;
use AdminEase\Features\WpMemoryLimit;
use AdminEase\Features\NetworkViewer;
use AdminEase\Features\MaintenanceMode\MaintenanceMode;
use AdminEase\Features\PasswordProtectSite\PasswordProtectSite;

defined( 'ABSPATH' ) || exit;

/**
 * The Features class initializes various components to enhance the functionality,
 * security, performance, and overall behavior of the application. It groups
 * features into categories like updates, security, performance, notifications,
 * user management, debugging, and miscellaneous enhancements.
 * The class is designed to activate these features by creating instances of
 * predefined functionality modules during the construction phase.
 */
class Features {
	private static ?Features $instance = null;
	
	public function __construct() {
		// Updates and Notifications
		new UpdatesAndNotifications();
		new DisableAdminNotices();
		
		// Security
		new DisableFileEdit();
		new DisallowFileMods();
		new DisableXmlRpc();
		new DisableRestApi();
		new DisableEmbeds();
		new HideWordpressVersion();
		new BlockAccessSensitiveFiles();
		new DisablePingbacksAndTrackbacks();
		new BlockAuthorScans();
		new BlockDirectoryBrowsing();
		new DisableScriptConcatenation();
		new BlockSpecificCountries();
		new BlockSpecificBots();
		new CorsHeader();
		new PasswordProtectSite();
		new HidePhpVersion();
		
		// Performance
		new WpMemoryLimit();
		new MaxExecutionTime();
		new NumberPostsRevisions();
		new AutosaveInterval();
		new DisableWpCron();
		new DisableEmojis();
		
		// Posts
		new EmptyTrashDays();
		new DisableGutenberg();
		new DragAndDropOrderingPosts();
		new DisableComments();
		new BulkDeletePosts();
		new PostsMetadataBox();
		
		// Taxonomies
		new DragAndDropOrderingTaxonomies();
		new TaxonomyMetaBox();
		
		// Users
		new DisableFrontendUserRegistration();
		new ForceStrongPasswords();
		new AutoLogoutUser();
		new RedirectAfterLoginLogout();
		new HideAdminBar();
		
		// Debug
		new WpDebug();
		new MaintenanceMode();
		new NetworkViewer();
		
		// Media
		new UploadMaxFileSize();
		new AllowSvgUpload();
		new AllowCustomFileExtensionUpload();
		new MediaLibraryInfiniteScrolling();
	}
	
	/**
	 * Retrieves the singleton instance of the Features class.
	 * @return Features The single instance of the Features class.
	 */
	public static function get_instance(): Features {
		if( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}