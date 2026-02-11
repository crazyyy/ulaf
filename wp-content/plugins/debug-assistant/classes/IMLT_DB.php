<?php
if ( class_exists('IMLT_DB') ) return;

class IMLT_DB extends wpdb
{

    public function __construct()
    {
        parent::__construct( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
    }

    public function query( $query )
    {
        $result = parent::query( $query );
    		if ( !defined('SAVEQUERIES') || !SAVEQUERIES ) {
    			   return $result;
    		}
        if ( empty($this->num_queries) ){
            $this->num_queries = 1;
        }
        $i = $this->num_queries - 1;
        $this->queries[$i]['indeed_backtrace'] = $this->addExtraInfo($this->queries[$i]);
        return $result;
    }


    private function addExtraInfo( $queryDetails )
    {
        $backtrace = debug_backtrace( false );
        if ( !$backtrace ){
            return false;
        }
        $returnBacktrace = array();
        foreach ( $backtrace as $item ){
            if ( !$item || empty($item['file']) || empty($item['line']) || empty($item['function']) ){
                continue;
            }
            $returnBacktrace[] = array(
                'file'          => $item['file'],
                'line'          => $item['line'],
                'function'      => $item['function'],
            );
        }
        return $returnBacktrace;
    }

}
