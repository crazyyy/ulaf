<?php 

class PhpSnippetTest extends PHPUnit_Framework_TestCase {


    function test_readonly_textarea_ok() {    
        $expected ='<textarea readonly rows="3" cols="30" onclick="this.focus();this.select()">abcde</textarea>';
        $output = readonly_textarea( 'abcde' );
        $this->assertEquals($expected, $output);
    }
    function test_readonly_textarea_escape() {    
        $expected ='<textarea readonly rows="3" cols="30" onclick="this.focus();this.select()">a&lt;b&gt;cde</textarea>';
        $output = readonly_textarea( 'a<b>cde', '3<b>', '30<b>' );
        $this->assertEquals($expected, $output);
    }

    function test_create_snippet_if() {   
        $expected = "&lt;?php if ( is_page ( '2' ) ): // page: sample-page  ?&gt; 

        &lt;?php endif; // is_page( '2' ) page: sample-page  ?&gt;";
        $output = create_snippet_if( 'is_page', '2', 'page: sample-page' );
        $this->assertEquals($expected, $output);
    }

    function test_create_snippet_if_escape() {    
        $expected = "&lt;?php if ( is_&lt;b&gt;page ( '2&lt;b&gt;' ) ): // page: sample-&lt;b&gt;page  ?&gt; 

        &lt;?php endif; // is_&lt;b&gt;page( '2&lt;b&gt;' ) page: sample-&lt;b&gt;page  ?&gt;";
        $output = create_snippet_if( 'is_<b>page', '2<b>', 'page: sample-<b>page' );
        $this->assertEquals($expected, $output);
    }

}
