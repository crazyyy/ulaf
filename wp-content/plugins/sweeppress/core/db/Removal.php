<?php

namespace Dev4Press\Plugin\SweepPress\DB;

use Dev4Press\Plugin\SweepPress\Base\DB as CoreDB;
use Dev4Press\Plugin\SweepPress\Basic\Sweep;
use Dev4Press\Plugin\SweepPress\Pro\Expand\Backup;
use Dev4Press\v54\Core\Quick\WPR;
use WP_Error;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Removal extends CoreDB {
    protected string $plugin_instance = 'removal';

    private bool $run = true;

    public function __construct() {
        parent::__construct();
        if ( SWEEPPRESS_SIMULATION ) {
            $this->run = false;
        }
    }

    public function go( string $sql, bool $return_error = true, $simulation_return = 1 ) {
        sweeppress()->log( '  ' . $sql );
        if ( $this->run ) {
            $start = microtime( true );
            $result = $this->query( $sql );
            $end = microtime( true );
            sweeppress()->log( '   TIME: ' . ($end - $start) . ' s' );
            if ( $result === false ) {
                sweeppress()->log( '   ERROR: ' . $this->wpdb()->last_error );
                return ( $return_error ? new WP_Error('wpdb', $this->wpdb()->last_error) : 0 );
            } else {
                return absint( $result );
            }
        } else {
            return $simulation_return;
        }
    }

    public function cron_jobs() {
        if ( $this->run ) {
            _set_cron_array( array() );
        }
    }

    public function cron_jobs_by_job( array $jobs ) {
        foreach ( $jobs as $job ) {
            WPR::remove_cron( $job );
        }
    }

    public function commentmeta_orphans() {
        $sql = "FROM {$this->commentmeta} m ";
        $sql .= "LEFT JOIN {$this->comments} p ON p.`comment_ID` = m.`comment_id` ";
        $sql .= "WHERE p.`comment_ID` IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->commentmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function comments_orphans() {
        $sql = "FROM {$this->comments} c ";
        $sql .= "LEFT JOIN {$this->commentmeta} m ON m.`comment_id` = c.`comment_ID` ";
        $sql .= "LEFT JOIN {$this->posts} p ON p.`ID` = c.`comment_post_ID` ";
        $sql .= "WHERE p.`ID` IS NULL";
        $query = array(
            'delete' => "DELETE c, m",
            'shared' => $sql,
            'tables' => array(
                $this->comments    => array(
                    'key'    => 'comment_ID',
                    'select' => 'SELECT DISTINCT c.`comment_ID`',
                ),
                $this->commentmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function comments_user_agent( int $keep_days = 0 ) {
        $keep_days--;
        $sql = "UPDATE " . $this->comments . " SET `comment_agent` = '' ";
        $sql .= "WHERE `comment_agent` != '' AND DATEDIFF(NOW(), `comment_date`) > %d";
        $sql = $this->prepare( $sql, $keep_days );
        return $this->go( $sql );
    }

    public function comments_by_status( string $comment_status, string $comment_type, int $keep_days = 0 ) {
        $keep_days--;
        $actual_status = ( $comment_status == 'unapproved' ? '0' : (( $comment_status == 'approved' ? '1' : $comment_status )) );
        $sql = "FROM {$this->comments} c ";
        $sql .= "LEFT JOIN {$this->commentmeta} m ON m.`comment_id` = c.`comment_ID` ";
        $sql .= "WHERE c.`comment_approved` = %s AND DATEDIFF(NOW(), c.`comment_date`) > %d ";
        $sql .= "AND c.`comment_type` = %s";
        $sql = $this->prepare(
            $sql,
            $actual_status,
            $keep_days,
            $comment_type
        );
        $query = array(
            'delete' => "DELETE c, m",
            'shared' => $sql,
            'task'   => $comment_type,
            'tables' => array(
                $this->comments    => array(
                    'key'    => 'comment_ID',
                    'select' => 'SELECT DISTINCT c.`comment_ID`',
                ),
                $this->commentmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function comments_by_type( string $comment_type, int $keep_days = 0 ) {
        $keep_days--;
        $sql = "FROM {$this->comments} c ";
        $sql .= "LEFT JOIN {$this->commentmeta} m ON m.`comment_id` = c.`comment_ID` ";
        $sql .= "WHERE c.`comment_type` = %s AND DATEDIFF(NOW(), c.`comment_date`) > %d";
        $sql = $this->prepare( $sql, $comment_type, $keep_days );
        $query = array(
            'delete' => "DELETE c, m",
            'shared' => $sql,
            'task'   => $comment_type,
            'tables' => array(
                $this->comments    => array(
                    'key'    => 'comment_ID',
                    'select' => 'SELECT DISTINCT c.`comment_ID`',
                ),
                $this->commentmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function akismet_meta_records( int $keep_days = 0 ) {
        $keep_days--;
        $sql = "FROM {$this->comments} c INNER JOIN {$this->commentmeta} m ON m.`comment_id` = c.`comment_ID` ";
        $sql .= "WHERE m.`meta_key` IN ('" . join( "', '", sweeppress_akismet_meta_keys() ) . "') ";
        $sql .= "AND c.`comment_approved` = '1' AND DATEDIFF(NOW(), c.`comment_date`) > %d";
        $sql = $this->prepare( $sql, $keep_days );
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->commentmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function usermeta_orphans() {
        $sql = "FROM {$this->usermeta} m ";
        $sql .= "LEFT JOIN {$this->users} p ON p.ID = m.user_id ";
        $sql .= "WHERE p.ID IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->usermeta => array(
                    'key'    => 'umeta_id',
                    'select' => 'SELECT DISTINCT m.`umeta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function termmeta_orphans() {
        $sql = "FROM {$this->termmeta} m ";
        $sql .= "LEFT JOIN {$this->terms} p ON p.term_id = m.term_id ";
        $sql .= "WHERE p.term_id IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->termmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function term_relationships_taxonomy_orphans() {
        $sql = "FROM {$this->term_relationships} tr ";
        $sql .= "LEFT JOIN {$this->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id ";
        $sql .= "WHERE tt.`term_taxonomy_id` IS NULL";
        $query = array(
            'delete' => "DELETE tr",
            'shared' => $sql,
            'tables' => array(
                $this->term_relationships => array(
                    'key'    => 'term_taxonomy_id',
                    'select' => 'SELECT DISTINCT tr.`term_taxonomy_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function term_relationships_object_orphans() {
        $sql = "FROM {$this->term_relationships} tr ";
        $sql .= "INNER JOIN {$this->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id ";
        $sql .= "WHERE tr.`object_id` NOT IN (SELECT `ID` FROM {$this->posts})";
        $query = array(
            'delete' => "DELETE tr",
            'shared' => $sql,
            'tables' => array(
                $this->term_relationships => array(
                    'key'    => 'object_id',
                    'select' => 'SELECT DISTINCT tr.`object_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function terms_orphans() {
        $sql = "FROM {$this->terms} t ";
        $sql .= "LEFT JOIN {$this->termmeta} m ON m.`term_id` = t.`term_id` ";
        $sql .= "LEFT JOIN {$this->term_taxonomy} x ON x.`term_id` = t.`term_id` ";
        $sql .= "WHERE x.`term_id` IS NULL";
        $query = array(
            'delete' => "DELETE t, m",
            'shared' => $sql,
            'tables' => array(
                $this->terms    => array(
                    'key'    => 'term_id',
                    'select' => 'SELECT DISTINCT t.`term_id`',
                ),
                $this->termmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function terms_amp_errors( string $task ) {
        $sql = "FROM {$this->terms} t ";
        $sql .= "INNER JOIN {$this->term_taxonomy} x ON x.`term_id` = t.`term_id` ";
        $sql .= "LEFT JOIN {$this->termmeta} m ON m.`term_id` = t.`term_id` ";
        $sql .= "LEFT JOIN {$this->term_relationships} r ON r.`term_taxonomy_id` = x.`term_taxonomy_id` ";
        $sql .= "WHERE x.`taxonomy` = 'amp_validation_error'";
        if ( $task == 'yes' ) {
            $sql .= "  AND r.`object_id` IS NOT NULL";
        } else {
            if ( $task == 'no' ) {
                $sql .= "  AND r.`object_id` IS NULL";
            }
        }
        $query = array(
            'delete' => "DELETE t, m, x, r",
            'shared' => $sql,
            'task'   => 'amp_validation_error',
            'tables' => array(
                $this->terms              => array(
                    'key'    => 'term_id',
                    'select' => 'SELECT DISTINCT t.`term_id`',
                ),
                $this->termmeta           => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
                $this->term_taxonomy      => array(
                    'key'    => 'term_taxonomy_id',
                    'select' => 'SELECT DISTINCT x.`term_taxonomy_id`',
                ),
                $this->term_relationships => array(
                    'key'    => 'term_taxonomy_id',
                    'select' => 'SELECT DISTINCT r.`term_taxonomy_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function unused_terms_by_taxonomy( string $taxonomy ) {
        $sql = "FROM {$this->terms} t ";
        $sql .= "INNER JOIN {$this->term_taxonomy} x ON x.`term_id` = t.`term_id` ";
        $sql .= "LEFT JOIN {$this->term_relationships} r ON r.`term_taxonomy_id` = x.`term_taxonomy_id` ";
        $sql .= "LEFT JOIN {$this->termmeta} m ON m.`term_id` = t.`term_id` ";
        $sql .= "WHERE x.`taxonomy` = %s AND r.term_taxonomy_id IS NULL";
        $sql = $this->prepare( $sql, $taxonomy );
        $query = array(
            'delete' => "DELETE t, m, x",
            'shared' => $sql,
            'task'   => $taxonomy,
            'tables' => array(
                $this->terms         => array(
                    'key'    => 'term_id',
                    'select' => 'SELECT DISTINCT t.`term_id`',
                ),
                $this->termmeta      => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
                $this->term_taxonomy => array(
                    'key'    => 'term_id',
                    'select' => 'SELECT DISTINCT x.`term_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function posts_by_status( string $post_status, string $post_type, int $keep_days = 0 ) {
        $keep_days--;
        $sql = "FROM {$this->posts} p ";
        $sql .= "LEFT JOIN {$this->postmeta} m ON m.`post_id` = p.`ID` ";
        $sql .= "LEFT JOIN {$this->comments} c ON c.`comment_post_ID` = p.`ID` ";
        $sql .= "LEFT JOIN {$this->commentmeta} t ON t.`comment_id` = c.`comment_ID` ";
        $sql .= "LEFT JOIN {$this->term_relationships} r ON p.ID = r.object_id ";
        $sql .= "WHERE p.`post_status` = %s AND p.`post_type` = %s AND DATEDIFF(NOW(), p.`post_date`) > %d";
        $sql = $this->prepare(
            $sql,
            $post_status,
            $post_type,
            $keep_days
        );
        $query = array(
            'delete' => "DELETE p, m, c, t, r",
            'shared' => $sql,
            'task'   => $post_type,
            'tables' => array(
                $this->posts              => array(
                    'key'    => 'ID',
                    'select' => 'SELECT DISTINCT p.`ID`',
                ),
                $this->postmeta           => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
                $this->comments           => array(
                    'key'    => 'comment_post_ID',
                    'select' => 'SELECT DISTINCT c.`comment_post_ID`',
                ),
                $this->commentmeta        => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT t.`meta_id`',
                ),
                $this->term_relationships => array(
                    'key'    => 'object_id',
                    'select' => 'SELECT DISTINCT r.`object_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function bbpress_orphaned_replies() {
        $post_type = ( function_exists( 'bbp_get_reply_post_type' ) ? bbp_get_reply_post_type() : 'reply' );
        $sql = "FROM {$this->posts} r ";
        $sql .= "LEFT JOIN {$this->posts} t ON t.`ID` = r.`post_parent` ";
        $sql .= "LEFT JOIN {$this->postmeta} m ON m.`post_id` = r.`ID` ";
        $sql .= "WHERE r.`post_type` = %s AND t.`ID` IS NULL";
        $sql = $this->prepare( $sql, $post_type );
        $query = array(
            'delete' => "DELETE r, m",
            'shared' => $sql,
            'task'   => $post_type,
            'tables' => array(
                $this->posts    => array(
                    'key'    => 'ID',
                    'select' => 'SELECT DISTINCT r.`ID`',
                ),
                $this->postmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function posts_revisions( string $post_type, int $keep_days = 0, array $post_status = array('publish') ) {
        $keep_days--;
        $sql = "FROM {$this->posts} r ";
        $sql .= "INNER JOIN {$this->posts} p ON p.`ID` = r.`post_parent` ";
        $sql .= "LEFT JOIN {$this->postmeta} m ON m.`post_id` = r.`ID` ";
        $sql .= "WHERE r.post_type = 'revision' AND p.post_type = %s AND p.`post_status` IN ('" . join( "', '", $post_status ) . "')";
        $sql .= "AND DATEDIFF(NOW(), r.`post_date`) > %d";
        $sql = $this->prepare( $sql, $post_type, $keep_days );
        $query = array(
            'delete' => "DELETE r, m",
            'shared' => $sql,
            'task'   => $post_type,
            'tables' => array(
                $this->posts    => array(
                    'key'    => 'ID',
                    'select' => 'SELECT DISTINCT r.`ID`',
                ),
                $this->postmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function posts_orphaned_revisions() {
        $sql = "FROM {$this->posts} r ";
        $sql .= "LEFT JOIN {$this->posts} p ON p.ID = r.post_parent ";
        $sql .= "WHERE r.post_type = 'revision' AND p.ID IS NULL";
        $query = array(
            'delete' => "DELETE r",
            'shared' => $sql,
            'tables' => array(
                $this->posts => array(
                    'key'    => 'ID',
                    'select' => 'SELECT DISTINCT r.`ID`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function postmeta_orphans() {
        $sql = "FROM {$this->postmeta} m ";
        $sql .= "LEFT JOIN {$this->posts} p ON p.ID = m.post_id ";
        $sql .= "WHERE p.ID IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->postmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function postmeta_by_key( string $meta_key ) {
        $sql = "FROM {$this->postmeta} m WHERE m.`meta_key` = %s";
        $sql = $this->prepare( $sql, $meta_key );
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->postmeta => array(
                    'key'    => 'meta_id',
                    'select' => 'SELECT DISTINCT m.`meta_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function postmeta_oembeds() {
        $sql = "DELETE m FROM {$this->postmeta} m WHERE m.`meta_key` LIKE '_oembed_%'";
        return $this->go( $sql );
    }

    public function signups_inactive( int $keep_days = 0 ) {
        $keep_days--;
        $sql = "FROM " . $this->wpdb()->signups . " s WHERE s.`active` = 0 AND DATEDIFF(NOW(), s.`registered`) > %d";
        $sql = $this->prepare( $sql, $keep_days );
        $query = array(
            'delete' => "DELETE s",
            'shared' => $sql,
            'tables' => array(
                $this->wpdb()->signups => array(
                    'key'    => 'signup_id',
                    'select' => 'SELECT DISTINCT s.`signup_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function elementor_trashed_submission( string $form_id, int $keep_days = 0 ) {
        $keep_days--;
        $sql = "FROM " . $this->prefix . "e_submissions a ";
        $sql .= "LEFT JOIN " . $this->prefix . "e_submissions_actions_log l ON a.`id` = l.`submission_id` ";
        $sql .= "LEFT JOIN " . $this->prefix . "e_submissions_values v ON a.`id` = v.`submission_id` ";
        $sql .= "WHERE a.`status` = 'trash' ";
        $sql .= "AND a.`element_id` = %s ";
        $sql .= "AND DATEDIFF(NOW(), a.`created_at`) > %d";
        $sql = $this->prepare( $sql, $form_id, $keep_days );
        $query = array(
            'delete' => "DELETE a, l, v",
            'shared' => $sql,
            'tables' => array(
                $this->prefix . "e_submissions"             => array(
                    'key'    => 'id',
                    'select' => 'SELECT DISTINCT a.`id`',
                ),
                $this->prefix . "e_submissions_actions_log" => array(
                    'key'    => 'id',
                    'select' => 'SELECT DISTINCT l.`id`',
                ),
                $this->prefix . "e_submissions_values"      => array(
                    'key'    => 'id',
                    'select' => 'SELECT DISTINCT v.`id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function actionscheduler_log_orphaned_records() {
        $sql = "FROM {$this->actionscheduler_logs} m ";
        $sql .= "LEFT JOIN {$this->actionscheduler_actions} p ON p.`action_id` = m.`action_id` ";
        $sql .= "WHERE p.`action_id` IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->actionscheduler_logs => array(
                    'key'    => 'action_id',
                    'select' => 'SELECT DISTINCT m.`action_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function actionscheduler_log_records( int $group_id, int $keep_days ) {
        $keep_days--;
        $sql = "FROM {$this->actionscheduler_logs} l ";
        $sql .= "INNER JOIN {$this->actionscheduler_actions} a ON a.`action_id` = l.`action_id` ";
        $sql .= "WHERE a.`group_id` = %d AND DATEDIFF(NOW(), l.`log_date_gmt`) > %d";
        $sql = $this->prepare( $sql, $group_id, $keep_days );
        $query = array(
            'delete' => "DELETE l",
            'shared' => $sql,
            'tables' => array(
                $this->actionscheduler_logs => array(
                    'key'    => 'action_id',
                    'select' => 'SELECT DISTINCT l.`action_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function actionscheduler_actions_records_for_status( array $action_status, int $group_id, int $keep_days ) {
        $keep_days--;
        $sql = "FROM {$this->actionscheduler_actions} a ";
        $sql .= "LEFT JOIN {$this->actionscheduler_logs} l ON a.`action_id` = l.`action_id` ";
        $sql .= "WHERE a.`status` IN ('" . join( "', '", $action_status ) . "') ";
        $sql .= " AND a.`group_id` = %d AND DATEDIFF(NOW(), a.`scheduled_date_gmt`) > %d";
        $sql = $this->prepare( $sql, $group_id, $keep_days );
        $query = array(
            'delete' => "DELETE a, l",
            'shared' => $sql,
            'tables' => array(
                $this->actionscheduler_actions => array(
                    'key'    => 'action_id',
                    'select' => 'SELECT DISTINCT a.`action_id`',
                ),
                $this->actionscheduler_logs    => array(
                    'key'    => 'action_id',
                    'select' => 'SELECT DISTINCT l.`action_id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function bp_activity_meta_orphans() {
        $sql = "FROM {$this->bp_activity_meta} m ";
        $sql .= "LEFT JOIN {$this->bp_activity} p ON p.`id` = m.`activity_id` ";
        $sql .= "WHERE p.`id` IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->bp_activity_meta => array(
                    'key'    => 'id',
                    'select' => 'SELECT DISTINCT m.`id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function bp_groups_meta_orphans() {
        $sql = "FROM {$this->bp_groups_groupmeta} m ";
        $sql .= "LEFT JOIN {$this->bp_groups} p ON p.`id` = m.`group_id` ";
        $sql .= "WHERE p.`id` IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->bp_groups_groupmeta => array(
                    'key'    => 'id',
                    'select' => 'SELECT DISTINCT m.`id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function bp_notifications_meta_orphans() {
        $sql = "FROM {$this->bp_notifications_meta} m ";
        $sql .= "LEFT JOIN {$this->bp_groups} p ON p.`id` = m.`notification_id` ";
        $sql .= "WHERE p.`id` IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->bp_notifications_meta => array(
                    'key'    => 'id',
                    'select' => 'SELECT DISTINCT m.`id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function bp_messages_meta_orphans() {
        $sql = "FROM {$this->bp_messages_meta} m ";
        $sql .= "LEFT JOIN {$this->bp_groups} p ON p.`id` = m.`message_id` ";
        $sql .= "WHERE p.`id` IS NULL";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'tables' => array(
                $this->bp_messages_meta => array(
                    'key'    => 'id',
                    'select' => 'SELECT DISTINCT m.`id`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    public function duplicated_meta_records(
        $name,
        $table,
        $columns,
        $exceptions
    ) {
        $sql = "SELECT COUNT(`" . $columns['id'] . "`) AS `meta_records`, GROUP_CONCAT(`" . $columns['id'] . "`) AS `meta_ids` FROM {$table} m ";
        if ( !empty( $exceptions ) ) {
            $sql .= "WHERE m.`" . $columns['key'] . "` NOT IN (" . $this->prepare_in_list( $exceptions ) . ") ";
        }
        $sql .= "GROUP BY m.`" . $columns['ref'] . "`, m.`" . $columns['key'] . "`, m.`" . $columns['value'] . "` HAVING `meta_records` > 1";
        $raw = $this->get_results( $sql );
        $ids = array();
        foreach ( $raw as $row ) {
            $list = explode( ',', $row->meta_ids );
            $list = $this->clean_ids_list( $list );
            if ( !empty( $list ) ) {
                array_pop( $list );
                if ( !empty( $list ) ) {
                    $ids = array_merge( $ids, $list );
                }
            }
        }
        $sql = "FROM {$table} m WHERE m.`" . $columns['id'] . "` IN (" . $this->prepare_in_list( $ids, '%d' ) . ")";
        $query = array(
            'delete' => "DELETE m",
            'shared' => $sql,
            'task'   => $name,
            'tables' => array(
                $table => array(
                    'key'    => $columns['id'],
                    'select' => 'SELECT DISTINCT m.`' . $columns['id'] . '`',
                ),
            ),
        );
        return $this->_handler( $query );
    }

    protected function _handler( $query ) {
        $delete_sql = $query['delete'] . ' ' . $query['shared'];
        return $this->go( $delete_sql );
    }

}
