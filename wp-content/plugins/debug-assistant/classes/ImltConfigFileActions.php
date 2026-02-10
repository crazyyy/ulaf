<?php
if ( class_exists('ImltConfigFileActions') ) return;

class ImltConfigFileActions
{
    private $filePath             = '';
    private $filePathHandle       = '';
    private $constantType         = '';
    public $tmp_file              = '';
    //public $dbg_value             = '';

    public function __construct()
    {
        $this->filePath = ABSPATH ."wp-config.php";
        //$this->filePath = chmod($this->filePath, 0444);
        $this->imlt_changePerm();


    }

    public function setConstantType( $value = '' )
    {
        $this->constantType = $value;
        return $this->constantType;
    }

    public function imlt_changePerm()
    {
      if(!is_writable($this->filePath)) {
        $this->filePath = chmod($this->filePath, 0664);
        return $this->filePath;
      }

    }
    public function imlt_getContent()
    {
        $config_dbg = file_get_contents($this->filePath);
        return $config_dbg;

    }

    public function setConstantInFile( $desiredValueLog = '', $valueType ='' )
    {
      // 1. take string from temp directory
      $this->tmp_file = IMLT_DIR_PATH . "/tmp/temp-wp-config.php";

      $str_tmp = file_get_contents($this->tmp_file);

      // 2. eliminates duplicates
      $str_tmp = $this->removeAllConstantAppereance( $str_tmp, $valueType, $this->tmp_file );

          if ( $valueType == 'bool' )
          {

              if ( $desiredValueLog == 1 ) {
                $dbg_value = 'true';
              }

              if ( $desiredValueLog == 0) {
                $dbg_value = 'false';
             }
          }

          if ( $valueType == 'number' ) {
              $dbg_value = $desiredValueLog;
          }

          if ( $valueType == 'string' ) {
              $dbg_value = "'" . $desiredValueLog . "'";
          }

      // 3. insert new constants in temp-wp-config.php

      $set_custom_str = "define('".  $this->constantType."', $dbg_value);" . "\n";
      $imlt_needle_str = "/* That's all, stop editing! Happy blogging. */";

      $imlt_neddle_str_regex ='/^\$table_prefix(\s*)?=(\s*)?(\'|")(.+)(\'|");/m';

      if ( strpos($str_tmp, $imlt_needle_str ) !== false  ) {

      $new_str = str_replace($imlt_needle_str, $set_custom_str.$imlt_needle_str, $str_tmp );
        file_put_contents( $this->tmp_file, $new_str );

    } else if ( preg_match_all($imlt_neddle_str_regex, $str_tmp, $matches) !== false ) {

        if( isset($matches[0]) && isset($matches[0][0]) && $matches[0][0] != '' ) {
            $new_str = str_replace( $matches[0][0],  $set_custom_str.$matches[0][0], $str_tmp );
            file_put_contents( $this->tmp_file, $new_str );
        }
      }


    }

    public function removeAllConstantAppereance( $string= '', $valueType='', $imlt_pth='')
    {

        // validate  if exist constant or value set
        switch ( $valueType )
        {
            case 'bool':
              $debugRegex = '/^(\/\*|\/\/)?(\s)?(.)?(.)*define[(](\s)?\'' . $this->constantType . '\', (true|TRUE|false|FALSE)(\s)?[)];/m'; //(true|TRUE|false|FALSE)(\s)?[)];/m'; // '\', (false|true|FALSE|TRUE)(\s)?[)];(\s)?(.)?(.)*(\*\/)?$/m';
              break;
            case 'string':
              $debugRegex = '/^(\/\*|\/\/)?(\s)?(.)?(.)*define[(](\s)?\'' . $this->constantType . '\', [\'\"\w\s]+[a-zA-z0-9]+[\'\"\w\s]+(\s)?[)];/m'; // '\', [\'\"\w\s]+[a-zA-z0-9]+[\'\"\w\s]+(\s)?[)];(\s)?(.)?(.)*(\*\/)?$/m';
              break;
            case 'number':
              $debugRegex = '/^(\/\*|\/\/)?(\s)?(.)?(.)*define[(](\s)?\'' . $this->constantType . '\', (\'|")[0-9]+M(\'|")(\s)?[)];/m'; /// '\', [0-9]+(\s)?[)];(\s)?(.)?(.)*(\*\/)?$/m'
              break;

        }

        if ( empty( $debugRegex) ){
            return $string;
        }
        preg_match_all($debugRegex, $string, $matches);

        if ( empty($matches[0]) ){
            return $string;
        }

        $allMatches = $matches[0];

        foreach ( $allMatches as $match ) {
            $string = str_replace($match, '', $string);
          }
            return $string;
        }

    public function wp_config_final_str()
    {
        $imlt_final_str = '';
        $tmp_file_size_str = file_get_contents($this->tmp_file);

              if( $tmp_file_size_str == '' || strlen($tmp_file_size_str) <= 100 )
              {
                  $imlt_final_str = "Temporary file is empty or is broken!";
                    return $imlt_final_str;
              }

              if( !defined('DB_NAME') &&
                  !defined('DB_USER') &&
                  !defined('DB_PASSWORD') &&
                  !defined('DB_HOST') &&
                  !defined('DB_CHARSET') &&
                  !defined('DB_COLLATE') &&
                  !defined('AUTH_KEY') &&
                  !defined('SECURE_AUTH_KEY') &&
                  !defined('LOGGED_IN_KEY') &&
                  !defined('NONCE_KEY') &&
                  !defined('AUTH_SALT') &&
                  !defined('SECURE_AUTH_SALT') &&
                  !defined('LOGGED_IN_SALT') &&
                  !defined('NONCE_SALT'))
              {
                  $imlt_final_str =  'Constants are missing';
                    return $imlt_final_str;
              }

              if(!defined('ABSPATH')) {
                $imlt_final_str = 'Constant ABSPATH is not defined!';
                   return $imlt_final_str;
              }

      $imlt_require_abspath = "require_once(ABSPATH . 'wp-settings.php');";

              /*if (strpos($tmp_file_size_str, $imlt_require_abspath) === false) {
                 $imlt_final_str = "require_once(ABSPATH . 'wp-settings.php'); is missing";
                   return true;
             }*/


      $imlt_final_str = str_replace($this->imlt_getContent(), $tmp_file_size_str, $this->imlt_getContent() );
      $imlt_final_str = file_put_contents($this->filePath, $tmp_file_size_str);
        return $imlt_final_str;


    }

}
