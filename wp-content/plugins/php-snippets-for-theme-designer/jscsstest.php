<?php 

class jscssTest extends PHPUnit_Framework_TestCase {

    var $obj;
    public function setUp() {
        $this->obj = new psftd_get_js();
    }
    // test assumes TwentyFourteen Theme
    // test requires php5.3
    public function test_func_name() {
        $expected = 'twentyfifteen_add_js';
        $output = $this->obj->create_func_name();
        $this->assertEquals($expected, $output);
    }

    public function testjs() {
        $expected = <<<'EOF'
  wp_enqueue_script( 'js-color-scheme-control.js', $dir.'/js/color-scheme-control.js', array() );
  wp_enqueue_script( 'js-customize-preview.js', $dir.'/js/customize-preview.js', array() );
  wp_enqueue_script( 'js-functions.js', $dir.'/js/functions.js', array() );
  wp_enqueue_script( 'js-html5.js', $dir.'/js/html5.js', array() );
  wp_enqueue_script( 'js-keyboard-image-navigation.js', $dir.'/js/keyboard-image-navigation.js', array() );
  wp_enqueue_script( 'js-skip-link-focus-fix.js', $dir.'/js/skip-link-focus-fix.js', array() );

EOF;
        $obj = new psftd_get_js();
        $output = $this->obj->output();
        $this->assertEquals($expected, $output);
    }

}