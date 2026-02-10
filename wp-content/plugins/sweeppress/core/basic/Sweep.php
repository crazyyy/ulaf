<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\Pro\Sweeper\GravityFormsEntriesSpam;
use Dev4Press\Plugin\SweepPress\Pro\Sweeper\GravityFormsEntriesTrash;
use Dev4Press\Plugin\SweepPress\Pro\Sweeper\GravityFormsFormsTrash;
use Dev4Press\Plugin\SweepPress\Pro\Sweeper\GravityFormsOrphanedEntries;
use Dev4Press\Plugin\SweepPress\Pro\Sweeper\GravityFormsOrphanedViews;
use Dev4Press\Plugin\SweepPress\Sweeper\ActionSchedulerActionsCanceled;
use Dev4Press\Plugin\SweepPress\Sweeper\ActionSchedulerActionsComplete;
use Dev4Press\Plugin\SweepPress\Sweeper\ActionSchedulerActionsFailed;
use Dev4Press\Plugin\SweepPress\Sweeper\ActionSchedulerLog;
use Dev4Press\Plugin\SweepPress\Sweeper\ActionSchedulerLogOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\AkismetMeta;
use Dev4Press\Plugin\SweepPress\Sweeper\AllNetworkTransients;
use Dev4Press\Plugin\SweepPress\Sweeper\AllTransients;
use Dev4Press\Plugin\SweepPress\Sweeper\bbPressOrphanedReplies;
use Dev4Press\Plugin\SweepPress\Sweeper\BuddyPressActivityMetaOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\BuddyPressGroupsMetaOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\BuddyPressMessagesMetaOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\BuddyPressNotificationsMetaOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\CommentmetaDuplicates;
use Dev4Press\Plugin\SweepPress\Sweeper\CommentmetaOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\CommentsOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\CommentsSpam;
use Dev4Press\Plugin\SweepPress\Sweeper\CommentsTrash;
use Dev4Press\Plugin\SweepPress\Sweeper\CommentsUnapproved;
use Dev4Press\Plugin\SweepPress\Sweeper\CommentsUserAgent;
use Dev4Press\Plugin\SweepPress\Sweeper\CronJobs;
use Dev4Press\Plugin\SweepPress\Sweeper\ElementorTrashedSubmissions;
use Dev4Press\Plugin\SweepPress\Sweeper\ExpiredTransients;
use Dev4Press\Plugin\SweepPress\Sweeper\InactiveSignups;
use Dev4Press\Plugin\SweepPress\Sweeper\NetworkExpiredTransients;
use Dev4Press\Plugin\SweepPress\Sweeper\OptimizeTables;
use Dev4Press\Plugin\SweepPress\Sweeper\PingbacksCleanup;
use Dev4Press\Plugin\SweepPress\Sweeper\PostmetaDuplicates;
use Dev4Press\Plugin\SweepPress\Sweeper\PostmetaEdits;
use Dev4Press\Plugin\SweepPress\Sweeper\PostmetaLocks;
use Dev4Press\Plugin\SweepPress\Sweeper\PostmetaOembeds;
use Dev4Press\Plugin\SweepPress\Sweeper\PostmetaOld;
use Dev4Press\Plugin\SweepPress\Sweeper\PostmetaOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\PostsAutoDraft;
use Dev4Press\Plugin\SweepPress\Sweeper\PostsDraftRevisions;
use Dev4Press\Plugin\SweepPress\Sweeper\PostsOrphanedRevisions;
use Dev4Press\Plugin\SweepPress\Sweeper\PostsRevisions;
use Dev4Press\Plugin\SweepPress\Sweeper\PostsSpam;
use Dev4Press\Plugin\SweepPress\Sweeper\PostsTrash;
use Dev4Press\Plugin\SweepPress\Sweeper\RepairTables;
use Dev4Press\Plugin\SweepPress\Sweeper\RSSFeeds;
use Dev4Press\Plugin\SweepPress\Sweeper\TermmetaDuplicates;
use Dev4Press\Plugin\SweepPress\Sweeper\TermmetaOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\TermRelationshipsObjectOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\TermRelationshipsTaxonomyOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\TermsAMPErrors;
use Dev4Press\Plugin\SweepPress\Sweeper\TermsOrphans;
use Dev4Press\Plugin\SweepPress\Sweeper\TermsUnused;
use Dev4Press\Plugin\SweepPress\Sweeper\TrackbacksCleanup;
use Dev4Press\Plugin\SweepPress\Sweeper\UnhookedCronJobs;
use Dev4Press\Plugin\SweepPress\Sweeper\UsermetaDuplicates;
use Dev4Press\Plugin\SweepPress\Sweeper\UsermetaOrphans;
use Dev4Press\v54\Core\Quick\BBP;
use Dev4Press\v54\Core\Quick\BP;
use Dev4Press\v54\Core\Scope;
class Sweep {
    /** @var array */
    protected $_scopes;

    /** @var array */
    protected $_categories;

    /** @var \Dev4Press\Plugin\SweepPress\Base\Sweeper[] */
    protected $_sweepers;

    protected $_active = '';

    protected $_counts = array(
        'total'     => 0,
        'blog'      => 0,
        'network'   => 0,
        'disabled'  => 0,
        'auto'      => 0,
        'quick'     => 0,
        'scheduled' => 0,
        'monitor'   => 0,
        'bulk'      => 0,
    );

    public function __construct( $force_load = false ) {
        $this->_scopes = array(
            'blog'    => __( 'Blog', 'sweeppress' ),
            'network' => __( 'Network', 'sweeppress' ),
        );
        $this->_categories = array(
            'posts'    => __( 'Posts', 'sweeppress' ),
            'comments' => __( 'Comments', 'sweeppress' ),
            'terms'    => __( 'Terms', 'sweeppress' ),
            'options'  => __( 'Options', 'sweeppress' ),
        );
        $this->_sweepers = array(
            'posts-auto-draft'                    => PostsAutoDraft::instance(),
            'posts-spam'                          => PostsSpam::instance(),
            'posts-trash'                         => PostsTrash::instance(),
            'posts-revisions'                     => PostsRevisions::instance(),
            'posts-draft-revisions'               => PostsDraftRevisions::instance(),
            'posts-orphaned-revisions'            => PostsOrphanedRevisions::instance(),
            'postmeta-locks'                      => PostmetaLocks::instance(),
            'postmeta-edits'                      => PostmetaEdits::instance(),
            'postmeta-old'                        => PostmetaOld::instance(),
            'postmeta-oembeds'                    => PostmetaOembeds::instance(),
            'postmeta-orphans'                    => PostmetaOrphans::instance(),
            'postmeta-duplicates'                 => PostmetaDuplicates::instance(),
            'comments-spam'                       => CommentsSpam::instance(),
            'comments-trash'                      => CommentsTrash::instance(),
            'comments-unapproved'                 => CommentsUnapproved::instance(),
            'comments-orphans'                    => CommentsOrphans::instance(),
            'comments-user-agent'                 => CommentsUserAgent::instance(),
            'commentmeta-orphans'                 => CommentmetaOrphans::instance(),
            'commentmeta-duplicates'              => CommentmetaDuplicates::instance(),
            'pingbacks-cleanup'                   => PingbacksCleanup::instance(),
            'trackbacks-cleanup'                  => TrackbacksCleanup::instance(),
            'akismet-meta'                        => AkismetMeta::instance(),
            'terms-orphans'                       => TermsOrphans::instance(),
            'terms-unused'                        => TermsUnused::instance(),
            'terms-amp-errors'                    => TermsAMPErrors::instance(),
            'termmeta-orphans'                    => TermmetaOrphans::instance(),
            'termmeta-duplicates'                 => TermmetaDuplicates::instance(),
            'term-relationships-object-orphans'   => TermRelationshipsObjectOrphans::instance(),
            'term-relationships-taxonomy-orphans' => TermRelationshipsTaxonomyOrphans::instance(),
            'expired-transients'                  => ExpiredTransients::instance(),
            'rss-feeds'                           => RSSFeeds::instance(),
            'all-transients'                      => AllTransients::instance(),
            'cron-jobs'                           => CronJobs::instance(),
            'unhooked-cron-jobs'                  => UnhookedCronJobs::instance(),
        );
        if ( Scope::instance()->is_main_blog() ) {
            $this->_categories['users'] = __( 'Users', 'sweeppress' );
            $this->_sweepers['usermeta-orphans'] = UsermetaOrphans::instance();
            $this->_sweepers['usermeta-duplicates'] = UsermetaDuplicates::instance();
        } else {
            $this->_counts['disabled'] += 2;
        }
        if ( $force_load || Scope::instance()->is_multisite() && Scope::instance()->is_main_blog() ) {
            $this->_categories['network'] = __( 'Network', 'sweeppress' );
            $this->_sweepers['inactive-signups'] = InactiveSignups::instance();
            $this->_sweepers['all-network-transients'] = AllNetworkTransients::instance();
            $this->_sweepers['network-expired-transients'] = NetworkExpiredTransients::instance();
        } else {
            $this->_counts['disabled'] += 3;
        }
        if ( $force_load || sweeppress_is_actionscheduler_active() ) {
            $this->_categories['actionscheduler'] = __( 'ActionScheduler', 'sweeppress' );
            $this->_sweepers['actionscheduler-log-orphans'] = ActionSchedulerLogOrphans::instance();
            $this->_sweepers['actionscheduler-log'] = ActionSchedulerLog::instance();
            $this->_sweepers['actionscheduler-actions-canceled'] = ActionSchedulerActionsCanceled::instance();
            $this->_sweepers['actionscheduler-actions-complete'] = ActionSchedulerActionsComplete::instance();
            $this->_sweepers['actionscheduler-actions-failed'] = ActionSchedulerActionsFailed::instance();
        } else {
            $this->_counts['disabled'] += 5;
        }
        if ( $force_load || BBP::is_active() ) {
            $this->_categories['bbpress'] = __( 'bbPress', 'sweeppress' );
            $this->_sweepers['bbpress-orphaned-replies'] = bbPressOrphanedReplies::instance();
        } else {
            $this->_counts['disabled'] += 1;
        }
        if ( $force_load || BP::is_active() ) {
            $this->_categories['buddypress'] = __( 'BuddyPress', 'sweeppress' );
            $this->_sweepers['buddypress-activity-meta-orphans'] = BuddyPressActivityMetaOrphans::instance();
            $this->_sweepers['buddypress-groups-meta-orphans'] = BuddyPressGroupsMetaOrphans::instance();
            $this->_sweepers['buddypress-messages-meta-orphans'] = BuddyPressMessagesMetaOrphans::instance();
            $this->_sweepers['buddypress-notifications-meta-orphans'] = BuddyPressNotificationsMetaOrphans::instance();
        } else {
            $this->_counts['disabled'] += 4;
        }
        if ( $force_load || defined( 'ELEMENTOR_PRO_VERSION' ) ) {
            $this->_categories['elementor'] = __( 'Elementor', 'sweeppress' );
            $this->_sweepers['elementor-trashed-submissions'] = ElementorTrashedSubmissions::instance();
        } else {
            $this->_counts['disabled'] += 1;
        }
        if ( $force_load || apply_filters( 'sweeppress_sweepers_allow_db', SWEEPPRESS_SWEEPERS_ALLOW_DB ) ) {
            $this->_categories['database'] = __( 'Database', 'sweeppress' );
            $this->_sweepers['optimize-tables'] = OptimizeTables::instance();
            $this->_sweepers['repair-tables'] = RepairTables::instance();
        } else {
            $this->_counts['disabled'] += 2;
        }
        foreach ( $this->_sweepers as $sweeper ) {
            $scope = $sweeper->get_scope();
            $this->_counts['total']++;
            $this->_counts[$scope]++;
            if ( $sweeper->for_auto_cleanup() ) {
                $this->_counts['auto']++;
            }
            if ( $sweeper->for_quick_cleanup() ) {
                $this->_counts['quick']++;
            }
            if ( $sweeper->for_scheduled_cleanup() ) {
                $this->_counts['scheduled']++;
            }
            if ( $sweeper->for_monitor_task() ) {
                $this->_counts['monitor']++;
            }
            if ( $sweeper->for_bulk_network() ) {
                $this->_counts['bulk']++;
            }
        }
    }

    public static function instance() : Sweep {
        static $instance = null;
        if ( is_null( $instance ) ) {
            $instance = new Sweep();
        }
        return $instance;
    }

    public function get_sweepers_count( string $what = 'total' ) : int {
        return $this->_counts[$what] ?? 0;
    }

    public function get_category_label( string $category ) : string {
        return $this->_categories[$category] ?? __( 'Unknown', 'sweeppress' );
    }

    public function get_sweeper_title( string $sweeper ) : string {
        if ( $this->is_sweeper_valid( $sweeper ) ) {
            return $this->_sweepers[$sweeper]->title();
        }
        return __( 'Unknown Sweeper', 'sweeppress' );
    }

    public function is_sweeper_task_valid( string $sweeper, string $task ) : bool {
        if ( $this->is_sweeper_valid( $sweeper ) ) {
            return $this->sweeper( $sweeper )->is_task_valid( $task );
        }
        return false;
    }

    public function is_sweeper_valid( string $sweeper ) : bool {
        return isset( $this->_sweepers[$sweeper] );
    }

    public function is_sweeper_single_task( string $sweeper ) : ?bool {
        if ( $this->is_sweeper_valid( $sweeper ) ) {
            return $this->_sweepers[$sweeper]->for_single_task();
        }
        return null;
    }

    /** @return \Dev4Press\Plugin\SweepPress\Base\Sweeper[][] */
    public function available() : array {
        $list = array();
        foreach ( $this->_sweepers as $sweeper ) {
            if ( $sweeper->is_available() ) {
                if ( !isset( $list[$sweeper->get_category()] ) ) {
                    $list[$sweeper->get_category()] = array();
                }
                $list[$sweeper->get_category()][] = $sweeper;
            }
        }
        return $list;
    }

    /** @return \Dev4Press\Plugin\SweepPress\Base\Sweeper[][] */
    public function for_scheduler() : array {
        $list = array();
        foreach ( $this->_sweepers as $sweeper ) {
            if ( $sweeper->for_scheduled_cleanup() ) {
                if ( !isset( $list[$sweeper->get_category()] ) ) {
                    $list[$sweeper->get_category()] = array();
                }
                $list[$sweeper->get_category()][] = $sweeper;
            }
        }
        return $list;
    }

    public function categories() : array {
        return $this->_categories;
    }

    public function all( $all = false ) : array {
        $list = array();
        foreach ( $this->_sweepers as $sweeper ) {
            if ( $all || $sweeper->is_available() ) {
                $list[] = array(
                    'cat'      => $sweeper->get_category(),
                    'category' => $this->get_category_label( $sweeper->get_category() ),
                    'name'     => $sweeper->title(),
                    'code'     => $sweeper->get_code(),
                    'scope'    => $sweeper->get_scope(),
                    'version'  => $sweeper->get_version(),
                    'info'     => $sweeper->description(),
                    'backup'   => $sweeper->for_backup(),
                    'auto'     => $sweeper->for_auto_cleanup(),
                    'quick'    => $sweeper->for_quick_cleanup(),
                    'cron'     => $sweeper->for_scheduled_cleanup(),
                    'monitor'  => $sweeper->for_monitor_task(),
                    'high'     => $sweeper->has_high_system_requirements(),
                    'pro'      => $sweeper->is_pro_only(),
                );
            }
        }
        return $list;
    }

    public function sweeper( string $code ) : ?Sweeper {
        if ( !empty( $code ) && isset( $this->_sweepers[$code] ) ) {
            return $this->_sweepers[$code];
        }
        return null;
    }

    public function active_sweeper() : ?Sweeper {
        return $this->sweeper( $this->_active );
    }

    public function auto_sweep( string $source ) : array {
        $list = $this->available();
        $args = array();
        foreach ( $list as $sweepers ) {
            foreach ( $sweepers as $sweeper ) {
                if ( $sweeper->for_auto_cleanup() && $sweeper->is_sweepable() ) {
                    $args[$sweeper->get_code()] = true;
                }
            }
        }
        return $this->sweep( $args, $source );
    }

    public function sweep( array $items, string $source ) : array {
        $results = array(
            'timer'    => array(
                'started' => microtime( true ),
            ),
            'stats'    => array(
                'source'   => $source,
                'records'  => 0,
                'size'     => 0,
                'jobs'     => 0,
                'tasks'    => 0,
                'sweepers' => array(),
            ),
            'sweepers' => array(),
        );
        sweeppress()->log( 'SWEEPER-MODE::' . strtoupper( $source ) . ' # BEGIN' );
        foreach ( $items as $sweeper => $list ) {
            $obj = $this->sweeper( $sweeper );
            if ( $obj && $obj->is_allowed() && $obj->is_sweepable() ) {
                $_started = microtime( true );
                $tasks = ( $list === true ? $obj->list_sweepable_tasks_keys() : $list );
                $results['sweepers'][$sweeper] = $obj->sweep( $tasks );
                sweeppress_settings()->delete_sweeper_cache( $obj->get_code() );
                $_ended = microtime( true );
                $results['stats']['jobs'] += 1;
                $results['stats']['tasks'] += count( $tasks );
                $results['stats']['records'] += $results['sweepers'][$sweeper]['records'];
                $results['stats']['size'] += $results['sweepers'][$sweeper]['size'];
                $results['stats']['sweepers'][$sweeper] = array(
                    'records' => $results['sweepers'][$sweeper]['records'],
                    'size'    => $results['sweepers'][$sweeper]['size'],
                    'time'    => $_ended - $_started,
                );
                $results['sweepers'][$sweeper]['time'] = $_ended - $_started;
            } else {
                $results['sweepers'][$sweeper] = false;
            }
        }
        $results['timer']['ended'] = microtime( true );
        $results['stats']['time'] = $results['timer']['ended'] - $results['timer']['started'];
        sweeppress_settings()->log_statistics( $results['stats'] );
        sweeppress()->log( 'SWEEPER-MODE::' . strtoupper( $source ) . ' # END' );
        sweeppress()->log( '------------------------------------------------------' );
        do_action( 'sweeppress_sweep_completed', $results );
        return $results;
    }

    public function active( string $name = '' ) {
        $this->_active = $name;
    }

    public function run_dashboard() {
        $method = sweeppress_settings()->get_method();
        $list = $this->available();
        foreach ( $list as $sweepers ) {
            foreach ( $sweepers as $sweeper ) {
                $run = $method == 'normal' || $method == 'partial' && !$sweeper->has_high_system_requirements();
                if ( !$run && $sweeper->has_cached_data() ) {
                    $run = true;
                }
                if ( $run ) {
                    if ( $sweeper->for_auto_cleanup() || $sweeper->for_quick_cleanup() ) {
                        $sweeper->get_tasks();
                    }
                }
            }
        }
    }

}
