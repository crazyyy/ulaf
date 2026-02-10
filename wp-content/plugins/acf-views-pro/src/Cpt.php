<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfGroups\AcfViewData;
use org\wplake\acf_views\AcfView\AcfViews;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\AcfGroupInterface;
use WP_Post;

defined('ABSPATH') || exit;

abstract class Cpt
{
    const NAME = '';

    protected Html $html;
    protected Plugin $plugin;
    protected array $fieldValues;
    protected Cache $cache;

    public function __construct(Html $html, Plugin $plugin, Cache $cache)
    {
        $this->html = $html;
        $this->plugin = $plugin;
        $this->fieldValues = [];
        $this->cache = $cache;
    }

    public abstract function replacePostUpdatedMessage(array $messages): array;

    protected function getInstanceData(int $postId): AcfGroupInterface
    {
        return static::NAME === AcfViews::NAME ?
            $this->cache->getAcfViewData($postId) :
            $this->cache->getAcfCardData($postId);
    }

    protected function getActionClone(): string
    {
        return static::NAME . '_clone';
    }

    protected function getActionCloned(): string
    {
        return static::NAME . '_cloned';
    }

    /**
     * @param int|string $postId Can be string, e.g. 'options'
     * @param array|null $targetStatuses
     *
     * @return bool
     */
    protected function isMyPost($postId, ?array $targetStatuses = ['publish',]): bool
    {
        // for 'site-settings' and similar
        if (!is_numeric($postId) ||
            !$postId) {
            return false;
        }

        $post = get_post($postId);

        if (!$post ||
            static::NAME !== $post->post_type ||
            wp_is_post_revision($postId) ||
            ($targetStatuses && !in_array($post->post_status, $targetStatuses, true))) {
            return false;
        }

        return true;
    }

    // by tests, json in post_meta in 13 times quicker than ordinary postMeta way (30ms per 10 objects vs 400ms)
    protected function replaceAcfFieldsWithJson(): void
    {
        // below ->isMyPost() is used without 'targetStatuses', as can be draft e.g. after cloning

        // 1. save meta field to the local array instead of the postmeta,
        // 'save_post' hook to make sure code executes only at the right time (not at the front, it would be time-wasting)

        add_action('acf/save_post', function ($postId) {
            if (!$this->isMyPost($postId, null)) {
                return;
            }

            add_filter('acf/pre_update_value', function ($isUpdated, $value, int $postId, array $field): bool {
                // extra check, as probably it's about another post
                if (!$this->isMyPost($postId, null)) {
                    return $isUpdated;
                }

                $this->saveMetaField($value, $postId, $field);

                // avoid saving to the postmeta
                return true;
            }, 10, 4);
        });

        // 2. save the local meta fields array to the post_content field
        // 'save_post' hook to make sure code executes only at the right time (not at the front, it would be time-wasting)
        // priority (20) is important here, means only after ACF UI saved all values

        add_action('acf/save_post', function ($postId) {
            if (!$this->isMyPost($postId, null)) {
                return;
            }

            $this->saveMetaFieldsToPost($postId);
        }, 20);

        // 3. 'replace' loading values for ACF interface from postmeta to my source (post_content)
        // 'admin_head' hook to make sure code executes only at the right time (not at the front, it would be time-wasting)

        add_action('acf/input/admin_head', function () {
            global $post;
            $postId = $post->ID ?? 0;

            if (!$this->isMyPost($postId, null)) {
                return;
            }

            // values are cache here, to avoid call instanceData->getFieldValues() every time
            // as it takes resources (to go through all inner objects)
            $values = [];

            add_filter('acf/pre_load_value', function ($value, $postId, $field) use ($values) {
                // extra check, as probably it's about another post
                if (!$this->isMyPost($postId, null)) {
                    return $value;
                }

                if (!key_exists($postId, $values)) {
                    $instanceData = $this->getInstanceData($postId);

                    $values[$postId] = $instanceData->getFieldValues();
                }

                return $this->getAcfFieldFromInstance($value, $postId, $field, $values[$postId]);
            }, 10, 3);
        });
    }

    public function setHooks(): void
    {
        add_action('admin_init', [$this, 'cloneItemAction']);
        add_action('admin_notices', [$this, 'showItemClonedMessage']);
        add_action('add_meta_boxes', [$this, 'addMetaboxes']);
        add_action('admin_enqueue_scripts', [$this, 'addCodemirrorToCPTEditScreens']);
        add_action('post_edit_form_tag', [$this, 'disableAutocompleteForPostEdit']);

        add_filter('views_edit-' . static::NAME, [$this, 'printPostTypeDescription',]);
        add_filter('post_row_actions', [$this, 'getRowActions',], 10, 2);
        add_filter('post_updated_messages', [$this, 'replacePostUpdatedMessage']);

        $this->replaceAcfFieldsWithJson();
    }

    public function cloneItemAction(): void
    {
        if (!isset($_GET[$this->getActionClone()])) {
            return;
        }

        $postId = (int)$_GET[$this->getActionClone()];
        $post = get_post($postId);

        if (!$post ||
            static::NAME !== $post->post_type) {
            return;
        }

        check_admin_referer('bulk-posts');

        $isHasGutenbergField = AcfViewData::POST_FIELD_IS_HAS_GUTENBERG;

        $args = [
            'post_type' => $post->post_type,
            'post_status' => 'draft',
            'post_name' => $post->post_name,
            'post_title' => $post->post_title . ' Clone',
            'post_author' => $post->post_author,
            'post_content' => wp_slash($post->post_content), // json of fields
            AcfViewData::POST_FIELD_IS_HAS_GUTENBERG => $post->{$isHasGutenbergField}, // isHasGutenbergBlock
        ];

        $newPostId = wp_insert_post($args);

        // something went wrong
        if (is_wp_error($newPostId)) {
            return;
        }

        $targetUrl = get_admin_url(null, '/edit.php?post_type=' . static::NAME);
        $targetUrl .= '&' . $this->getActionCloned() . '=1';

        wp_redirect($targetUrl);
        exit;
    }

    public function showItemClonedMessage(): void
    {
        if (!isset($_GET[$this->getActionCloned()])) {
            return;
        }

        echo '<div class="notice notice-success">' .
            sprintf('<p>%s</p>', __('Item success cloned.', 'acf-views')) .
            '</div>';
    }

    public function getRowActions(array $actions, WP_Post $view): array
    {
        if (static::NAME !== $view->post_type) {
            return $actions;
        }

        $trash = str_replace(
            '>Trash<',
            sprintf('>%s<', __('Delete', 'acf-views')),
            $actions['trash'] ?? ''
        );

        // quick edit
        unset($actions['inline hide-if-no-js']);
        unset($actions['trash']);

        $cloneLink = get_admin_url(null, '/edit.php?post_type=' . static::NAME);
        $cloneLink .= '&' . $this->getActionClone() . '=' . $view->ID . '&_wpnonce=' . wp_create_nonce(
                'bulk-posts'
            );
        $actions['clone'] = sprintf("<a href='%s'>%s</a>", $cloneLink, __('Clone', 'acf-views'));
        $actions['trash'] = $trash;

        return $actions;
    }

    protected function addProBannerMetabox(): void
    {
        add_meta_box(
            'acf-views_pro',
            __('Pro', 'acf-views'),
            function ($post, $meta) {
                echo $this->html->proBanner(Plugin::PRO_VERSION_URL, $this->plugin->getAssetsUrl('pro.png'));
            },
            [
                static::NAME,
            ],
            'side'
        );
    }

    protected function insertIntoArrayAfterKey(array $array, string $key, array $newItems): array
    {
        $keys = array_keys($array);
        $index = array_search($key, $keys);

        $pos = false === $index ?
            count($array) :
            $index + 1;

        return array_merge(array_slice($array, 0, $pos), $newItems, array_slice($array, $pos));
    }

    protected function printMountPoints(AcfCptData $acfCptData): void
    {
        $postTypes = [];
        $posts = [];

        foreach ($acfCptData->mountPoints as $mountPoint) {
            $postTypes = array_merge($postTypes, $mountPoint->postTypes);
            $posts = array_merge($posts, $mountPoint->posts);
        }

        $postTypes = array_unique($postTypes);
        $posts = array_unique($posts);

        foreach ($posts as $index => $post) {
            $postInfo = sprintf(
                '<a target="_blank" href="%s">%s</a>',
                get_the_permalink($post),
                get_the_title($post)
            );

            $posts[$index] = $postInfo;
        }

        if ($postTypes) {
            echo __('Post Types:', 'acf-views') . ' ' . join(', ', $postTypes);
        }

        if ($posts) {
            if ($postTypes) {
                echo '<br>';
            }

            echo __('Pages:', 'acf-views') . ' ' . join(', ', $posts);
        }
    }

    public function addMetaboxes(): void
    {
        add_meta_box(
            'acf-views_review',
            __('Rate & Review', 'acf-views'),
            function ($post, $meta) {
                echo $this->html->postboxReview();
            },
            [
                static::NAME,
            ],
            'side'
        );

        add_meta_box(
            'acf-views_support',
            __('Having issues?', 'acf-views'),
            function ($post, $meta) {
                echo $this->html->postboxSupport();
            },
            [
                static::NAME,
            ],
            'side'
        );
        // $this->addProBannerMetabox();
    }

    public function addCodemirrorToCPTEditScreens()
    {
        if (!$this->plugin->isCPTScreen(static::NAME)) {
            return;
        }

        $cmSettings = [
            '_html' => wp_enqueue_code_editor([
                'type' => 'text/html',
                'codemirror' => [
                    'lint' => true,
                ],
            ]),
            '_css' => wp_enqueue_code_editor([
                'type' => 'text/css',
                'codemirror' => [
                    'lint' => true,
                ],
            ]),
            '_js' => wp_enqueue_code_editor([
                'type' => 'javascript',
                'codemirror' => [
                    'lint' => true,
                    'matchBrackets' => true,
                    'autoCloseBrackets' => true,
                ],
            ]),
            '_php' => wp_enqueue_code_editor([
                'type' => 'php',
                'codemirror' => [
                    'matchBrackets' => true,
                    'autoCloseBrackets' => true,
                ],
            ]),
        ];

        wp_localize_script('jquery', '_cm_settings', $cmSettings);

        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
    }

    public function printPostTypeDescription($views)
    {
        $screen = get_current_screen();
        $postType = get_post_type_object($screen->post_type);

        if ($postType->description) {
            // don't use esc_html as it contains links
            printf('<p>%s</p>', $postType->description);
        }

        return $views; // return original input unchanged
    }

    /**
     * Otherwise in case editing fields (without saving) and reloading a page,
     * then the fields have these unsaved values, it's wrong and breaks logic (e.g. of group-field selects)
     */
    public function disableAutocompleteForPostEdit(WP_Post $post): void
    {
        if (static::NAME !== $post->post_type) {
            return;
        }

        echo ' autocomplete="off"';
    }

    public function saveMetaField($value, int $postId, array $field): void
    {
        $fieldName = $field['name'] ?? '';

        $instanceData = $this->getInstanceData($postId);

        // convert repeater format. don't check simply 'is_array(value)' as not every array is a repeater
        // also check to make sure it's array (can be empty string)
        if (in_array($fieldName, $instanceData->getRepeaterFieldNames(), true) &&
            is_array($value)) {
            $value = AcfGroup::convertRepeaterFieldValues($fieldName, $value);
        }

        // convert clone format
        // also check to make sure it's array (can be empty string)
        if (in_array($fieldName, $instanceData->getCloneFieldNames(), true) &&
            is_array($value)) {
            $newValue = AcfGroup::convertCloneField($fieldName, $value);
            $this->fieldValues = array_merge($this->fieldValues, $newValue);

            return;
        }

        $this->fieldValues[$fieldName] = $value;
    }

    public function saveMetaFieldsToPost(int $postId): void
    {
        $instanceData = $this->getInstanceData($postId);

        // remove slashes added by WP, as it's wrong to have slashes so early
        // (corrupts next data processing, like markup generation (will be \&quote; instead of &quote; due to this escaping)
        // in the 'saveToPostContent()' method using $wpdb that also has 'addslashes()',
        // it means otherwise \" will be replaced with \\\" and it'll create double slashing issue (every saving amount of slashes before " will be increasing)

        $fieldValues = array_map('stripslashes_deep', $this->fieldValues);

        $instanceData->load($postId, '', $fieldValues);
        $instanceData->saveToPostContent();
    }

    public function getAcfFieldFromInstance($value, int $postId, array $field, array $values)
    {
        $fieldName = $field['name'] ?? '';

        // skip sub-fields or fields from other groups
        if (!key_exists($fieldName, $values)) {
            return $value;
        }

        $value = $values[$fieldName];
        $instanceData = $this->getInstanceData($postId);

        // convert repeater format. don't check simply 'is_array(value)' as not every array is a repeater
        // also check to make sure it's array (can be empty string)
        $value = in_array($fieldName, $instanceData->getRepeaterFieldNames(), true) &&
        is_array($value) ?
            AcfGroup::convertRepeaterFieldValues($fieldName, $value, false) :
            $value;

        // convert clone format
        $cloneFieldNames = $instanceData->getCloneFieldNames();
        foreach ($cloneFieldNames as $cloneFieldName) {
            $clonePrefix = $cloneFieldName . '_';

            if (0 !== strpos($fieldName, $clonePrefix)) {
                continue;
            }

            // can be string field
            if (!is_array($value)) {
                break;
            }

            $fieldNameWithoutClonePrefix = substr($fieldName, strlen($clonePrefix));

            $value = AcfGroup::convertCloneField($fieldNameWithoutClonePrefix, $value, false);

            break;
        }

        return $value;
    }
}