<?php
if ( class_exists('ImltHooksAndActionsList') ) return;

class ImltHooksAndActionsList
{
    private $components = array();
    private $sourceList = array();

    public function __construct()
    {
      global $wp_filter;

      $hookNames = array_keys( $wp_filter );
      $components = array();
      foreach ( $hookNames as $hookName ){
          if ( !isset( $wp_filter[ $hookName ] ) ) {
               continue;
          }
          $actionData = $wp_filter[ $hookName ];
          foreach ( $actionData as $priority => $callbacks ){
              foreach ( $callbacks as $callback ) {
                  $class = '';
                  $function = '';
                  $file = '';
                  if ( is_object($callback['function']) ){
                      if ( is_a( $callback['function'], 'Closure' ) ) {
                          $reflection  = new \ReflectionFunction( $callback['function'] );
                          $file = basename( $reflection->getFileName() );
                          $class = 'Closure on file ' . $file;
                      } else {
                          $class = get_class( $callback['function'] );
                          $callback['name'] = $class . "->__invoke()";
                          if ( class_exists( $class ) ){
                            $reflection  = new \ReflectionMethod( $class, '__invoke' ); // before was ReflectionFunction
                            $file = $reflection->getFileName();
                          }

                      }
                  } else if ( is_array( $callback['function'] ) ) {
                      if ( is_object( $callback['function'][0] ) ) {
                          $class  = get_class( $callback['function'][0] );
                          $function = $callback['function'][1];
                          if ( !class_exists($class) || !method_exists($class, $function) ){
                              continue;
                          }
                          $reflection = new ReflectionMethod( $class, $function );
                          $file = $reflection->getFileName();
                      } else {
                          $class  = $callback['function'][0];
                          $function = $callback['function'][1];
                          if ( class_exists($class) ){
                              try {
                                $reflection = new ReflectionMethod( $class, $function );
                                $file = $reflection->getFileName();
                              } catch ( Exception $e ){}

                          }
                      }
                  } else {
                      $class = '';
                      $function = $callback['function'];
                      if ( function_exists($function) ){
                          $reflection  = new ReflectionFunction( $function );
                          $file = $reflection->getFileName();
                      }
                  }
                  $source = $this->getSource( $file );
                  if ( $source && !in_array( $source, $this->sourceList ) ){
                      $this->sourceList[] = $source;
                  }
                  $this->components[ $hookName ][] = array(
                                'class'       => $class,
                                'function'    => $function,
                                'file'        => $file,
                                'source'      => $source,
                  );
              }
          }
      }
  		ksort( $this->components );

    }

    public function getAll()
    {
        return $this->components;
    }

    public function getSourceList()
    {
        return $this->sourceList;
    }

    public function getAllBySource( $sourceName='' )
    {
        if ( !$sourceName || !$this->components ){
            return $this->components;
        }
        foreach ( $this->components as $key => $data){
            foreach ( $data as $hookName => $callbacks ){
              if ( $callbacks['source'] != $sourceName ){
                  unset($this->components[$key][$hookName]);
              }
            }
            if ( empty($this->components[$key]) ){
                unset( $this->components[$key] );
            }


        }
        return $this->components;
    }

    private function getSource( $filePath='' )
    {
        if ( !$filePath ){
            return '';
        }
        if ( strpos( $filePath, 'wp-includes/') !== false ){
            return 'Core';
        } else if ( strpos( $filePath, '/wp-content/themes/') !== false ){
            return wp_get_theme()->get('Name');
        } else if ( stripos( $filePath, '/wp-content/plugins/' ) !== false ){
            $temporaryData = explode( '/wp-content/plugins/', $filePath );
            if ( empty( $temporaryData[1] ) ){
                return '';
            }
            $stringParts = explode( '/', $temporaryData[1] );
            if ( empty($stringParts[0]) ){
                return '';
            }
            return $stringParts[0];
        }
        return '';
    }

}
