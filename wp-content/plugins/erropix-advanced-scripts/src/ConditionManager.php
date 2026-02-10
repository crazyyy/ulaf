<?php

namespace ERROPiX\AdvancedScripts;

use DateTime;
use stdClass;
use WP_Post;
use WP_Query;

/**
 * Class ConditionManager
 * @package ERROPiX\AdvancedScripts
 */
class ConditionManager
{
    use Utils;

    /**
     * Conditions filters groups
     */
    const GROUP_POSTS_ARCHIVE = "Posts Archive";
    const GROUP_POSTS_SINGLE = "Single Post";
    const GROUP_USERS = "Users";
    const GROUP_DATE = "Date & Time";
    const GROUP_OXYGEN = "Oxygen";
    const GROUP_CUSTOM = "Custom";

    /**
     * Conditions values constants
     */
    const OXY_CONTEXT_NONE = "0";
    const OXY_CONTEXT_UI = "1";
    const OXY_CONTEXT_IFRAME = "2";
    const OXY_CONTEXT_AJAX = "3";

    /**
     * Conditions filters required hooks
     */
    const HOOK_WP_QUERY = "wp";
    const HOOK_WP_USER = "init";

    /**
     * Conditions filters
     * @var array[]
     */
    private $filters;

    /**
     * Conditions operators
     * @var array[]
     */
    private $operators;

    public function __construct()
    {
        $this->setup_operators();
        $this->setup_filters();
    }

    public function setup_operators()
    {
        $this->operators = [
            [
                "id" => "equal",
                "label" => "equal",
                "maxItems" => 1,
                "types" => [
                    "string",
                    "number",
                    "enum",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "not_equal",
                "label" => "do not equal",
                "maxItems" => 1,
                "types" => [
                    "string",
                    "number",
                    "enum",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "greater",
                "label" => "greater than",
                "maxItems" => 1,
                "types" => [
                    "number",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "greater_equal",
                "label" => "greater than or equal",
                "maxItems" => 1,
                "types" => [
                    "number",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "less",
                "label" => "less than",
                "maxItems" => 1,
                "types" => [
                    "number",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "less_equal",
                "label" => "less than or equal",
                "maxItems" => 1,
                "types" => [
                    "number",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "between",
                "label" => "between",
                "minItems" => 2,
                "maxItems" => 2,
                "types" => [
                    "number",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "not_between",
                "label" => "not between",
                "minItems" => 2,
                "maxItems" => 2,
                "types" => [
                    "number",
                    "date",
                    "datetime",
                    "time",
                ]
            ],
            [
                "id" => "contains",
                "label" => "contains",
                "types" => [
                    "array",
                ]
            ],
            [
                "id" => "not_contains",
                "label" => "do not contains",
                "types" => [
                    "array",
                ]
            ],
            [
                "id" => "in",
                "label" => "in",
                "minItems" => 2,
                "types" => [
                    "string",
                    "number",
                    "enum",
                    "date",
                ]
            ],
            [
                "id" => "not_in",
                "label" => "not in",
                "minItems" => 2,
                "types" => [
                    "string",
                    "number",
                    "enum",
                    "date",
                ]
            ],
            [
                "id" => "empty",
                "label" => "is empty",
                "minItems" => 0,
                "maxItems" => 0,
                "types" => [
                    "string",
                    "number",
                    "enum",
                    "array",
                ]
            ],
            [
                "id" => "not_empty",
                "label" => "is not empty",
                "minItems" => 0,
                "maxItems" => 0,
                "types" => [
                    "string",
                    "number",
                    "enum",
                    "array",
                ]
            ],
            [
                "id" => "true",
                "label" => "true",
                "minItems" => 0,
                "maxItems" => 0,
                "types" => [
                    "boolean",
                ]
            ],
            [
                "id" => "not_true",
                "label" => "false",
                "minItems" => 0,
                "maxItems" => 0,
                "types" => [
                    "boolean",
                ]
            ],
        ];
    }

    public function setup_filters()
    {
        $this->filters = [];

        // Single post
        $this->filters[] = [
            "id" => "post_id",
            "label" => "Post",
            "type" => "enum",
            "group" => self::GROUP_POSTS_SINGLE,
            "require_hook" => self::HOOK_WP_QUERY,
            "value_callback" => function () {
                return get_the_ID();
            },
            "options_callback" => function () {
                return $this->options_callback_post_id();
            },
            "optgroups_callback" => function () {
                return $this->optgroups_callback_post_types();
            },
        ];

        // Post parent
        $this->filters[] = [
            "id" => "post_parent",
            "label" => "Post parent",
            "type" => "enum",
            "group" => self::GROUP_POSTS_SINGLE,
            "require_hook" => self::HOOK_WP_QUERY,
            "value_callback" => function () {
                return get_post_field("post_parent");
            },
            "options_callback" => function () {
                return $this->options_callback_post_id([
                    "hierarchical" => true,
                ]);
            },
            "optgroups_callback" => function () {
                return $this->optgroups_callback_post_types([
                    "hierarchical" => true,
                ]);
            },
        ];

        // Post ancestors
        $this->filters[] = [
            "id" => "post_ancestors",
            "label" => "Post ancestors",
            "type" => "array",
            "group" => self::GROUP_POSTS_SINGLE,
            "require_hook" => self::HOOK_WP_QUERY,
            "value_callback" => function () {
                global $post;

                return get_post_ancestors($post);
            },
            "options_callback" => function () {
                return $this->options_callback_post_id([
                    "hierarchical" => true,
                ]);
            },
            "optgroups_callback" => function () {
                return $this->optgroups_callback_post_types([
                    "hierarchical" => true,
                ]);
            },
        ];

        // Post type
        $this->filters[] = [
            "id" => "post_type",
            "label" => "Post type",
            "type" => "string",
            "group" => self::GROUP_POSTS_SINGLE,
            "require_hook" => self::HOOK_WP_QUERY,
            "value_callback" => function () {
                return get_post_field("post_type");
            },
            "options_callback" => function () {
                return $this->options_callback_post_types();
            },
        ];

        // User
        $this->filters[] = [
            "id" => "user_id",
            "label" => "User",
            "type" => "enum",
            "group" => self::GROUP_USERS,
            "require_hook" => self::HOOK_WP_USER,
            "value_callback" => function () {
                return get_current_user_id();
            },
            "options_callback" => function () {
                return $this->options_callback_users();
            },
        ];

        // User roles
        $this->filters[] = [
            "id" => "user_roles",
            "label" => "User roles",
            "type" => "array",
            "group" => self::GROUP_USERS,
            "require_hook" => self::HOOK_WP_USER,
            "value_callback" => function () {
                global $current_user;

                if (is_array($current_user->roles)) {
                    return $current_user->roles;
                }

                return [];
            },
            "options_callback" => function () {
                return $this->options_callback_user_roles();
            },
        ];

        // User login status
        $this->filters[] = [
            "id" => "user_login_status",
            "label" => "User logged in",
            "type" => "boolean",
            "group" => self::GROUP_USERS,
            "require_hook" => self::HOOK_WP_USER,
            "value_callback" => function () {
                if (function_exists("is_user_logged_in")) {
                    return is_user_logged_in();
                }
                return false;
            },
        ];

        // Date
        $this->filters[] = [
            "id" => "date",
            "label" => "Date",
            "type" => "date",
            "input" => "datepicker",
            "group" => self::GROUP_DATE,
            "value_callback" => function () {
                $timezone = wp_timezone();
                $datetime = new DateTime("now", $timezone);
                return $datetime->format("Y-m-d");
            },
        ];

        // Datetime
        $this->filters[] = [
            "id" => "datetime",
            "label" => "Datetime",
            "type" => "datetime",
            "input" => "datepicker",
            "group" => self::GROUP_DATE,
            "value_callback" => function () {
                $timezone = wp_timezone();
                $datetime = new DateTime("now", $timezone);
                return $datetime->format("Y-m-d H:i:s");
            },
        ];

        // Datetime
        $this->filters[] = [
            "id" => "day",
            "label" => "Day",
            "type" => "number",
            "group" => self::GROUP_DATE,
            "value_callback" => function () {
                $timezone = wp_timezone();
                $datetime = new DateTime("now", $timezone);
                return $datetime->format("j"); // Day of the month without leading zeros: 1 to 31
            },
            "options_callback" => function () {
                $days = [];
                for ($i = 1; $i <= 31; $i++) {
                    $days[] = [
                        "value" => $i,
                        "text" => "$i",
                    ];
                }
                return $days;
            },
        ];

        // Datetime
        $this->filters[] = [
            "id" => "week_day",
            "label" => "Day of week",
            "type" => "enum",
            "group" => self::GROUP_DATE,
            "value_callback" => function () {
                $timezone = wp_timezone();
                $datetime = new DateTime("now", $timezone);
                return $datetime->format("N"); // ISO 8601 numeric representation of the day of the week: 1 (for Monday) through 7 (for Sunday)
            },
            "options" => $this->array_to_options([
                1 => "Monday",
                2 => "Tuesday",
                3 => "Wednesday",
                4 => "Thursday",
                5 => "Friday",
                6 => "Saturday",
                7 => "Sunday",
            ]),
        ];

        // Datetime
        $this->filters[] = [
            "id" => "month",
            "label" => "Month",
            "type" => "enum",
            "group" => self::GROUP_DATE,
            "value_callback" => function () {
                $timezone = wp_timezone();
                $datetime = new DateTime("now", $timezone);
                return $datetime->format("n"); // Numeric representation of a month, without leading zeros: 1 through 12
            },
            "options" => $this->array_to_options([
                1 => "January",
                2 => "February",
                3 => "March",
                4 => "April",
                5 => "May",
                6 => "June",
                7 => "July",
                8 => "August",
                9 => "September",
                10 => "October",
                11 => "November",
                12 => "December",
            ]),
        ];

        // Datetime
        $this->filters[] = [
            "id" => "time",
            "label" => "Time",
            "type" => "time",
            "input" => "datepicker",
            "group" => self::GROUP_DATE,
            "value_callback" => function () {
                $timezone = wp_timezone();
                $datetime = new DateTime("now", $timezone);
                return $datetime->format("H:i:s");
            },
        ];

        // Oxygen related conditions
        if (defined("CT_VERSION")) {
            // Builder context
            $this->filters[] = [
                "id" => "oxygen_context",
                "label" => "Builder Context",
                "type" => "enum",
                "group" => self::GROUP_OXYGEN,
                // "require_hook" => "init",
                "value_callback" => function () {
                    // Oxygen ajax requests
                    $action = $_REQUEST["action"] ?? "";

                    if ($action) {
                        $actions = [
                            "ct_eval_condition",
                            "ct_eval_conditions",
                            "ct_get_post_data",
                            "ct_save_components_tree",
                            "ct_exec_code",
                            "oxy_get_dynamic_data_query"
                        ];

                        if (in_array($action, $actions)) {
                            return self::OXY_CONTEXT_AJAX;
                        }

                        if (strpos($action, "ct_render_") === 0) {
                            return self::OXY_CONTEXT_AJAX;
                        }

                        if (strpos($action, "oxy_render_") === 0) {
                            return self::OXY_CONTEXT_AJAX;
                        }
                    }

                    // Oxygen interfaces
                    $is_builder = $_GET['ct_builder'] ?? false;
                    $is_iframe = $_GET['oxygen_iframe'] ?? false;

                    if ($is_builder) {
                        if ($is_iframe) {
                            return self::OXY_CONTEXT_IFRAME;
                        }

                        return self::OXY_CONTEXT_UI;
                    }

                    return self::OXY_CONTEXT_NONE;
                },
                "options" => [
                    [
                        "text" => "None",
                        "value" => self::OXY_CONTEXT_NONE,
                    ],
                    [
                        "text" => "Oxygen UI",
                        "value" => self::OXY_CONTEXT_UI,
                    ],
                    [
                        "text" => "Oxygen Iframe",
                        "value" => self::OXY_CONTEXT_IFRAME,
                    ],
                    [
                        "text" => "Ajax requests",
                        "value" => self::OXY_CONTEXT_AJAX,
                    ],
                ],
            ];

            // Template
            $this->filters[] = [
                "id" => "oxygen_template",
                "label" => "Template",
                "type" => "array",
                "group" => self::GROUP_OXYGEN,
                "require_hook" => self::HOOK_WP_QUERY,
                "value_callback" => function () {
                    global $ct_template_id, $ct_parent_template_id;

                    $templates = [];

                    if ($ct_template_id) {
                        $templates[] = intval($ct_template_id);
                    }

                    if ($ct_parent_template_id) {
                        $templates[] = intval($ct_parent_template_id);
                    }

                    return $templates;
                },
                "options_callback" => function () {
                    $options = [];

                    $posts = get_posts([
                        "post_type" => "ct_template",
                        "posts_per_page" => -1,
                    ]);

                    foreach ($posts as $post) {
                        $template_type = get_post_meta($post->ID, "ct_template_type", true);
                        if ($template_type === "reusable_part") {
                            continue;
                        }

                        $options[] = [
                            "value" => $post->ID,
                            "text" => $post->post_title,
                        ];
                    }

                    return $options;
                },
            ];
        }

        // Custom
        $this->filters[] = [
            "id" => "expression",
            "label" => "Expression",
            "type" => "boolean",
            "group" => self::GROUP_CUSTOM,
            "require_hook" => self::HOOK_WP_QUERY,
            "require_data" => true,
            "data_placeholder" => "PHP expression like: is_front_page() && is_home()",
            "value_callback" => function ($expression) {
                return eval("return ($expression);");
            },
        ];
    }

    public function validate_rule(stdClass $rule)
    {
        $result = false;

        $filterData = $this->get_filter($rule->filter);
        $operatorData = $this->get_operator($rule->operator);

        if ($filterData && $operatorData) {
            $operator = $rule->operator;
            $rule_value = $rule->value;

            $totlaItems = count($rule_value);
            $minItems = $operatorData->minItems ?? 0;
            $maxItems = $operatorData->maxItems ?? INF;
            if ($minItems > $totlaItems || $totlaItems > $maxItems) {
                return $result;
            }

            $filter_value = call_user_func($filterData->value_callback, $rule->data);

            $reverse = false;
            if (strpos($operator, "not_") === 0) {
                $reverse = true;
                $operator = substr($operator, 4);
            }

            $type = $filterData->type ?? "string";
            switch ($type) {
                case "id":
                case "number":
                    $rule_value = array_map("intval", $rule_value);
                    break;
            }

            switch ($operator) {
                case "equal":
                    $result = $filter_value == $rule_value[0];
                    break;

                case "greater":
                    $result = $filter_value > $rule_value[0];
                    break;

                case "greater_equal":
                    $result = $filter_value >= $rule_value[0];
                    break;

                case "less":
                    $result = $filter_value < $rule_value[0];
                    break;

                case "less_equal":
                    $result = $filter_value <= $rule_value[0];
                    break;

                case "between":
                    if ($type == "time") {
                        $result = $this->time_in_inteval($filter_value, $rule_value[0], $rule_value[1]);
                        break;
                    }

                    $result = ($rule_value[0] <= $filter_value) && ($filter_value <= $rule_value[1]);
                    break;

                case "in":
                    $result = in_array($filter_value, $rule_value);
                    break;

                case "contains":
                    $intersection = array_intersect($filter_value, $rule_value);
                    $result = count($intersection) == count($rule_value);
                    break;

                case "empty":
                    $result = empty($filter_value);
                    break;

                case "true":
                    $result = $filter_value === true;
                    break;
            }

            if ($reverse) {
                $result = !$result;
            }
        }

        return $result;
    }

    /**
     * 
     * @param string $conditions script conditions to be validated
     * @param callable|null $callback 
     * @return boolean
     */
    public function validate(string $conditions, callable $callback = null)
    {
        $conditions = $this->decode($conditions);

        $relation = $conditions->relation ?? "and";
        $rules = $conditions->rules ?? [];

        $hooks = $this->get_rules_hooks($rules);

        new HooksWatcher($hooks, function () use ($relation, $rules, $callback) {
            $valid = true;
            foreach ($rules as $rule) {
                $valid = $this->validate_rule($rule);

                if ($relation == 'and' && $valid === false) break;
                if ($relation == 'or' && $valid === true) break;
            }

            if ($valid && $callback) {
                call_user_func($callback);
            }
        });
    }

    public function get_rules_hooks(array $rules)
    {
        $hooks = [];

        foreach ($rules as $rule) {
            $filter = $this->get_filter($rule->filter);
            $require_hook = $filter->require_hook ?? null;

            if ($require_hook && !in_array($require_hook, $hooks)) {
                $hooks[] = $require_hook;
            }
        }

        return $hooks;
    }

    public function options_callback_post_id($pt_args = [])
    {
        $pt_args = wp_parse_args($pt_args, [
            "public" => true,
        ]);
        $post_types = get_post_types($pt_args);

        $wp_query = new WP_Query();
        $posts = $wp_query->query([
            "post_type" => $post_types,
            "suppress_filters" => true,
            "update_post_term_cache" => false,
            "update_post_meta_cache" => false,
            "post_status" => [
                "publish",
                "draft",
                "future",
                "private",
            ],
            "posts_per_page" => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $options = [];

        foreach ($posts as $post) {
            if ($post->post_status != "publish") {
                $status = get_post_status_object($post->post_status);
                if ($status) {
                    $post->post_title .= " <i>— {$status->label}</i>";
                }
            }

            $options[] = [
                "value" => $post->ID,
                "text" => $post->post_title,
                "optgroup" => $post->post_type,
            ];
        }

        return $options;
    }

    public function options_callback_post_types($args = [])
    {
        $args = wp_parse_args($args, [
            "public" => true,
        ]);
        $post_types = get_post_types($args, "objects");

        $options = [];
        foreach ($post_types as $post_type => $object) {
            $options[] = [
                "value" => $post_type,
                "text" => $object->label,
            ];
        }

        return $options;
    }

    public function options_callback_users()
    {
        $options = [];
        $args = [
            "fields" => ["ID", "display_name"],
        ];
        $users = get_users($args);

        foreach ($users as $user) {
            $options[] = [
                "value" => $user->ID,
                "text" => $user->display_name,
            ];
        }

        return $options;
    }

    public function options_callback_user_roles()
    {
        $options = [];

        $roles = wp_roles()->get_names();
        foreach ($roles as $name => $label) {
            $options[] = [
                "value" => $name,
                "text" => $label,
            ];
        }

        return $options;
    }

    public function optgroups_callback_post_types($args = [])
    {
        $args = wp_parse_args($args, [
            "public" => true,
        ]);
        $post_types = get_post_types($args, "objects");

        $optgroups = [];

        $i = 1;
        foreach ($post_types as $post_type => $object) {
            $optgroups[] = [
                "value" => $post_type,
                "label" => $object->label,
                // "order" => $i++,
            ];
        }

        return $optgroups;
    }

    public function get_builder_filters()
    {
        $filters = [];

        foreach ($this->filters as $filter) {
            $filter_id = $filter["id"] ?? null;
            $filter_label = $filter["label"] ?? null;
            $filter_group = $filter["group"] ?? "";
            $filter_type = $filter["type"] ?? "string";
            $filter_input = $filter["input"] ?? "selectize";
            $filter_require_hook = $filter["require_hook"] ?? null;
            $filter_require_data = $filter["require_data"] ?? null;
            $filter_data_placeholder = $filter["data_placeholder"] ?? "";
            $filter_operators = $filter["operators"] ?? [];
            $filter_options = $filter["options"] ?? [];
            $filter_optgroups = $filter["optgroups"] ?? [];

            if (empty($filter_operators)) {
                foreach ($this->operators as $operator) {
                    $operator_id = $operator["id"];
                    $operator_types = $operator["types"];

                    if (in_array($filter_type, $operator_types)) {
                        $filter_operators[] = $operator_id;
                    }
                }
            }

            if (empty($filter_options)) {
                $options_callback = $filter["options_callback"] ?? null;
                if (is_callable($options_callback)) {
                    $filter_options = call_user_func($options_callback);
                }
            }

            if (empty($filter_optgroups)) {
                $optgroups_callback = $filter["optgroups_callback"] ?? null;
                if (is_callable($optgroups_callback)) {
                    $filter_optgroups = call_user_func($optgroups_callback);
                }
            }

            $data = [
                "id" => $filter_id,
                "label" => $filter_label,
                "group" => $filter_group,
                "type" => $filter_type,
                "input" => $filter_input,
                "require_hook" => $filter_require_hook,
                "require_data" => $filter_require_data,
                "data_placeholder" => $filter_data_placeholder,
                "operators" => $filter_operators,
                "options" => $filter_options,
                "optgroups" => $filter_optgroups,
            ];

            $filters[] = array_filter($data, function ($value) {
                return !empty($value);
            });
        }

        return $filters;
    }

    public function get_builder_operators()
    {
        return $this->operators;
    }

    public function get_filter($id)
    {
        foreach ($this->filters as $filter) {
            if ($filter["id"] === $id) {
                return new SmartObject($filter);
            }
        }

        return null;
    }

    public function get_operator($id)
    {
        foreach ($this->operators as $operator) {
            if ($operator["id"] === $id) {
                return new SmartObject($operator);
            }
        }

        return null;
    }
}
