<?php

/**
 * Rule class format
 */
if(!class_exists("Rule_wordpress_single_resource_id")):
class Rule_wordpress_single_resource_id extends Super_Rule{
    public $rule_set = array();
    function __construct(){
        $this->rule_set();

        add_filter("css_js_manager_rules", array($this, "send_rule_set"));
    }

    function rule_set(){
        $this->rule_set = array(
                    "group_type"=>"textbox",
                    "title"=>"Wordpress Single Resource ID",
                    "group_id"=>"wordpress_single_resource_id",
                    "textbox_list"=> array(
					array(
						"label"=>"Is Page",
						"name"=>"is_page",
                        "value"=>"",
                        "desc"=>"Enter page id saperated by comma like this: 1,2,3,4"
					),
					array(
						"label"=>"Is Post",
						"name"=>"is_single",
                        "value"=>"",
                        "desc"=>"Free version only accept one post id, Enter post id separated by comma like this: 1,2,3,4"
                    ),
                    array(
						"label"=>"Is Category",
						"name"=>"is_category",
                        "value"=>"",
                        "desc"=>"When the actual page is associated with the Category."
                    )
                )
        );
    }

    function send_rule_set($array){
        $array[] = $this->rule_set;
        return $array;
    }

    function is_page($value){
        if($value == "") return false;

        $array = $this->string_to_array($value);
        $return = is_page($array);
        return $return;
    }

    function is_single($value){
        if($value == "") return false;
        
        $array = $this->string_to_array($value);
        return is_single($array[0]);
    }

    function is_category($value){
        if($value == "") return false;
        
        return is_category($this->string_to_array($value));
    }

}

new Rule_wordpress_single_resource_id();

endif;