<?php

/**
 * Rule class format
 */
if(!class_exists("Rule_Wp_Condition_Tags")):

class Rule_Wp_Condition_Tags extends Super_Rule{
    public $rule_set = array();
    function __construct(){
        $this->rule_set();

        add_filter("css_js_manager_rules", array($this, "send_rule_set"));
    }

    function rule_set(){
        $this->rule_set = array(
                    "group_type"=>"checkbox",
                    "title"=>"Wordpress Page Types",
                    "group_id"=>"Wp_Condition_Tags",
                    "checkbox_list"=> array(
					array(
						"label"=>"Is Front Page",
						"desc"=>"Determines whether the query is for the front page of the site.",
						"name"=>"is_front_page",
						"value"=>false
					),
					array(
						"label"=>"Is Home Page",
						"name"=>"is_home",
						"value"=>false,
						"desc"=>"Determines whether the query is for the blog homepage."
					),
					array(
						"label"=>"Is Page",
						"name"=>"is_page",
						"value"=>false,
						"desc"=>"Determines whether the query is for an existing single page."
					),
					array(
						"label"=>"Is Page (Not front page)",
						"name"=>"is_page_not_front",
						"value"=>false,
						"desc"=>"Determines whether the query is for an page. but not the front page"
					),
					array(
						"label"=>"Is RTL",
						"name"=>"is_rtl",
						"value"=>false,
						"desc"=>"Returns true if the current locale est read from right to left (RTL)."
					),
					array(
						"label"=>"Is User loged in",
						"name"=>"is_user_logged_in",
						"value"=>false,
						"desc"=>"Determines whether the current visitor is a logged in user."
					),
					array(
						"label"=>"Is Sticky",
						"name"=>"is_sticky",
						"value"=>false,
						"desc"=>"Returns true if: Stick this post to the front page check box has been checked for the current post."
					),
					array(
						"label"=>"Is Tag",
						"name"=>"is_tag",
						"value"=>false,
						"desc"=>"When any Tag archive page is being displayed."
					),
					array(
						"label"=>"Is Author",
						"name"=>"is_author",
						"value"=>false,
						"desc"=>"When any Author page is being displayed."
					),
					array(
						"label"=>"Is Search page",
						"name"=>"is_search",
						"value"=>false,
						"desc"=>"When a search result page archive is being displayed."
					),
					array(
						"label"=>"Is 404 page",
						"name"=>"is_404",
						"value"=>false,
						"desc"=>"When a page displays after an HTTP 404: Not Found error occurs."
					),
					array(
						"label"=>"Is Attachment",
						"name"=>"is_attachment",
						"value"=>false,
						"desc"=>"When an attachment document to a post or Page is being displayed."
					),
					array(
						"label"=>"Is Singular",
						"name"=>"is_singular",
						"value"=>false,
						"desc"=>"Returns true for any is_single(), is_page(), or is_attachment()"
					),
					array(
						"label"=>"Is single post",
						"name"=>"is_single",
						"value"=>false,
						"desc"=>"Determines whether the query is for an existing single post."
					),
					array(
						"label"=>"Is Mobile / Tablet",
						"name"=>"is_mobile",
						"value"=>false,
						"desc"=>"Determines whether the query is from an Mobile."
					),
					array(
						"label"=>"Is Desktop",
						"name"=>"is_not_mobile",
						"value"=>false,
						"desc"=>"Determines whether the query is from an Desktop."
					),
                )
        );
    }

    function send_rule_set($array){
        $array[] = $this->rule_set;
        return $array;
	}

	function is_single($value){
		if(!function_exists('is_single')) return false;

		return is_single();
	}

	function is_singular($value){
		if(!function_exists('is_singular')) return false;

		return is_singular();
	}

	function is_attachment($value){
		if(!function_exists('is_attachment')) return false;

		return is_attachment();
	}

	function is_404($value){
		if(!function_exists('is_404')) return false;

		return is_404();
	}

	function is_search($value){
		if(!function_exists('is_search')) return false;

		return is_search();
	}
	
	function is_front_page($value){
		if(!function_exists('is_front_page')) return false;

		return is_front_page();
	}

	function is_home($value){
		if(!function_exists('is_home')) return false;

		return is_home();
	}

	function is_page($value){
		if(!function_exists('is_page')) return false;

		return is_page();
	}

	function is_page_not_front($value){
		if(!function_exists('is_page') || !function_exists('is_front_page')) return false;

		if(is_page() && !is_front_page()){
			return true;
		}
		return false;
	}

	function is_user_logged_in($value){
		if(!function_exists('is_user_logged_in')) return false;

		return is_user_logged_in();
	}

	function is_rtl($value){
		if(!function_exists('is_rtl')) return false;

		return is_rtl();
	}

	function is_sticky($value){
		if(!function_exists('is_sticky')) return false;

		return is_sticky();
	}

	function is_tag($value){
		if(!function_exists('is_tag')) return false;

		return is_tag();
	}

	function is_author($value){
		if(!function_exists('is_author')) return false;

		return is_author();
	}

	function is_mobile($value){
		if(!function_exists('wp_is_mobile')) return false;

		return wp_is_mobile();
	}

	function is_not_mobile($value){
		if(!function_exists('wp_is_mobile')) return false;
		
		return !wp_is_mobile();
	}

}

new Rule_Wp_Condition_Tags();

endif;