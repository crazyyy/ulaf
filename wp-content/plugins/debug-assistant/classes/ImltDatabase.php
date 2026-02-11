<?php
if ( !class_exists('ImltDatabase') ) return;

class ImltDatabase
{

    public function getAllTables()
    {
        global $wpdb;
        $returnData = [];
        $query = 'select * from information_schema.tables';
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return [];
        }
        foreach ( $data as $object ){
            if ( $object->ENGINE=='Aria' || $object->ENGINE=='MEMORY' ) continue;
            $returnData[] = [
                                'tableName'             => $object->TABLE_NAME,
                                'engine'                => $object->ENGINE,
                                'createTime'            => $object->CREATE_TIME,
                                'updateTime'            => $object->UPDATE_TIME,
                                'tableCollation'        => $object->TABLE_COLLATION,
                                'count'                 => $object->TABLE_ROWS,
            ];
        }
        return $returnData;
    }

    public function selectDataFromTable( $table='', $limit=50, $offset=0, $extraConditions='' )
    {
        global $wpdb;
        if ( !$table ){
            return [];
        }
        $query = $wpdb->prepare( "SELECT * FROM $table LIMIT %d OFFSET %d ", $limit, $offset );
        $query .= $extraConditions;
        return $wpdb->get_results( $query );
    }

    public function getQueryForSelectDataFromTable( $table='', $limit=50, $offset=0, $extraConditions='' )
    {
        global $wpdb;
        if ( !$table ){
            return [];
        }
        $query = $wpdb->prepare( "SELECT * FROM $table LIMIT %d OFFSET %d ", $limit, $offset );
        $query .= $extraConditions;
        return $query;
    }

    public function countTableData( $table='' )
    {
        global $wpdb;
        if ( !$table ){
            return 0;
        }
        $query = "SELECT COUNT(0) as c FROM $table;";
        return (int)$wpdb->get_var( $query );
    }

    public function getTableDetails( $table='' )
    {
        global $wpdb;
        $returnData = [];
        if ( !$table ){
            return [];
        }
        $query = "DESCRIBE $table;";
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return [];
        }
        return $data;
    }

    public function getTableDataByQuery( $query='' )
    {
        global $wpdb;
        if ( !$query ){
            return [];
        }
        $data = $wpdb->get_results( $query );
        return $data;
    }

    public function extractTableNameFromQuery( $query='' )
    {
        if ( !$query ){
            return '';
        }
        $tableNameTemp = explode( 'FROM ', $query );
        if ( isset( $tableNameTemp[1] ) ){
            $tableTemp = explode(' ', $tableNameTemp[1]);
            if ( isset( $tableTemp[0] ) ){
                return $tableTemp[0];
            }
        }
        return '';
    }

    public function selectUsersByLastLoggedTime( $time='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT a.*, b.meta_value as value FROM {$wpdb->users} a INNER JOIN {$wpdb->usermeta} b ON a.ID=b.user_id WHERE b.meta_key='imlt_last_login' AND b.meta_value>%d ;", $time );
        return $wpdb->get_results( $query );
    }

    public function getUidByUsername( $username='' )
    {
        global $wpdb;
        if ( !$username ){
            return 0;
        }
        $query = $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE user_login=%s;", $username );
        return $wpdb->get_var($query);
    }

    public function getCronJobsList()
    {
        $crons = get_option( 'cron' );
        if ( !$crons ){
            return false;
        }
        $all = array();
        foreach ( $crons as $time => $timeArray ){
            if ( !is_array( $timeArray ) ) continue;
            foreach ( $timeArray as $cronName => $cron ){
                $key = key($cron);
                $all[] = array(
                    'lastRun'               => $time,
                    'slug'                  => $cronName,
                    'interval'              => $cron[$key]['schedule'],
                    'intervalInSeconds'     => isset($cron[$key]['interval']) ? $cron[$key]['interval'] : '',
                );
            }
        }
        return $all;
    }

    public function getCronData( $cronName='' )
    {
        $crons = get_option( 'cron' );
        if ( !$crons ){
            return array();
        }
        foreach ( $crons as $time => $timeArray ){
            foreach ( $timeArray as $currentCronName => $cron ){
                if ( $cronName==$currentCronName ){
                    $key = key($cron);
                    return array(
                        'name'                  => $cronName,
                        'lastRun'               => $time,
                        'schedule'              => $cron[$key]['schedule'],
                        'interval'              => $cron[$key]['interval'],
                        'args'                  => $cron[$key]['args'],
                    );
                }
            }
        }
        return array();
    }

    public function getSpeedTestData( $limit=50, $offset=0, $extraConditions='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}imlt_speed_tests LIMIT %d OFFSET %d ", $limit, $offset );
        $query .= $extraConditions;
        return $wpdb->get_results( $query );

    }

    public function clearSpeedTestData()
    {
        global $wpdb;
        return $wpdb->query( "DELETE FROM {$wpdb->prefix}imlt_speed_tests" );
    }

}
