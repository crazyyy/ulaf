<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfView;

use org\wplake\acf_views\AcfGroups\AcfViewData;
use org\wplake\acf_views\Cache;
use org\wplake\acf_views\Cpt;
use org\wplake\acf_views\Html;
use org\wplake\acf_views\Plugin;
use WP_Query;

defined('ABSPATH') || exit;

/**
 * ACF View = list of fields with extra settings.
 * On a front field info is gotten by 'get_field_object', field value by 'get_field'.
 * The first also is used by ACF internally, it means no extra loading here.
 * So extra loading comparing with the code way only in the following items :
 * 1. getting ACF View fields from DB (json from post_meta is used here, so low)
 * 2. markup preparation and data convertations (low).
 */
class AcfViews extends Cpt
{
    const NAME = 'acf_views';
    const COLUMN_DESCRIPTION = self::NAME . '_description';
    const COLUMN_SHORTCODE = self::NAME . '_shortcode';
    const COLUMN_AUTHOR = self::NAME . '_author';
    const COLUMN_CREATED = self::NAME . '_created';
    const COLUMN_LAST_MODIFIED = self::NAME . '_lastModified';

    /**
     * @var ViewMarkup
     */
    protected $viewMarkup;

    public function __construct(
        Html $html,
        ViewMarkup $viewMarkup,
        Plugin $plugin,
        Cache $cache
    ) {
        parent::__construct($html, $plugin, $cache);

        $this->viewMarkup = $viewMarkup;
    }

    protected function updateIdentifiers(AcfViewData $acfViewData): void
    {
        foreach ($acfViewData->items as $item) {
            $item->field->id = ($item->field->id &&
                !preg_match('/^[a-zA-Z0-9_\-]+$/', $item->field->id)) ?
                '' :
                $item->field->id;

            if ($item->field->id &&
                $item->field->id === $this->getUniqueFieldId($acfViewData, $item, $item->field->id)) {
                continue;
            }

            $fieldMeta = new FieldMeta($item->field->getAcfFieldId());
            if (!$fieldMeta->isFieldExist()) {
                continue;
            }

            // $Post$ fields have '_' prefix, remove it, otherwise looks bad in the markup
            $name = ltrim($fieldMeta->getName(), '_');
            $item->field->id = $this->getUniqueFieldId($acfViewData, $item, $name);
        }
    }

    protected function updateMarkup(AcfViewData $acfViewData): void
    {
        // pageId 0, so without CSS, also skipCache
        $viewMarkup = $this->viewMarkup->getMarkup($acfViewData, 0, '', true);

        $acfViewData->markup = $viewMarkup;
    }

    public function getUniqueFieldId(AcfViewData $acfViewData, $excludeObject, string $name): string
    {
        $isUnique = true;

        foreach ($acfViewData->items as $item) {
            if ($item === $excludeObject ||
                $item->field->id !== $name) {
                continue;
            }

            $isUnique = false;
            break;
        }

        return $isUnique ?
            $name :
            $this->getUniqueFieldId($acfViewData, $excludeObject, $name . '2');
    }

    public function setHooks(): void
    {
        parent::setHooks();

        add_action('init', [$this, 'addCPT']);
        add_action(
            'manage_' . self::NAME . '_posts_custom_column',
            [
                $this,
                'printColumn',
            ],
            10,
            2
        );
        // priority is important here, should be 1) after the acf code (20)
        // 2) after the CPT save hook, which replaces fields with json (30)
        add_action('acf/save_post', [$this, 'performSaveActions'], 30);
        add_action('admin_menu', [$this, 'removeAddNewItemSubmenuLink']);
        add_filter('manage_' . self::NAME . '_posts_columns', [$this, 'getColumns',]);
        add_filter('manage_edit-' . self::NAME . '_sortable_columns', [$this, 'getSortableColumns',]);
        add_action('pre_get_posts', [$this, 'addSortableColumnsToRequest',]);
        add_filter('enter_title_here', [$this, 'getTitlePlaceholder',]);
        add_filter('register_block_type_args', [$this, 'extendGutenbergShortcode'], 10, 2);
    }

    // https://github.com/WordPress/gutenberg/issues/43053
    // https://support.advancedcustomfields.com/forums/topic/add-custom-field-to-query-loop/
    public function extendGutenbergShortcode(array $args, string $name): array
    {
        if ('core/shortcode' !== $name) {
            return $args;
        }

        return array_merge($args, [
            'render_callback' => function ($attributes, $content, $data) {
                // skip if something else than our shortcode
                if (false === strpos($content, '[' . Plugin::SHORTCODE)) {
                    return $content;
                }

                // native functionality is to call 'wpautop',
                // which means we lose access to the Post ID and ACF related data
                return do_shortcode($content);
            },
        ]);
    }

    public function replacePostUpdatedMessage(array $messages): array
    {
        global $post;

        $restoredMessage = false;
        $scheduledMessage = __('ACF View scheduled for:', 'acf-views');
        $scheduledMessage .= sprintf(
            ' <strong>%1$s</strong>',
            date_i18n('M j, Y @ G:i', strtotime($post->post_date))
        );

        if (isset($_GET['revision'])) {
            $restoredMessage = __('ACF View restored to revision from', 'acf-views');
            $restoredMessage .= ' ' . wp_post_revision_title((int)$_GET['revision'], false);
        }

        $messages[self::NAME] = [
            0 => '', // Unused. Messages start at index 1.
            1 => __('ACF View updated.', 'acf-views'),
            2 => __('Custom field updated.', 'acf-views'),
            3 => __('Custom field deleted.', 'acf-views'),
            4 => __('ACF View updated.', 'acf-views'),
            5 => $restoredMessage,
            6 => __('ACF View published.', 'acf-views'),
            7 => __('ACF View saved.', 'acf-views'),
            8 => __('ACF View submitted.', 'acf-views'),
            9 => $scheduledMessage,
            10 => __('ACF View draft updated.', 'acf-views'),
        ];

        return $messages;
    }

    /**
     * @param int|string $postId
     *
     * @return void
     */
    public function performSaveActions($postId): void
    {
        if (!$this->isMyPost($postId)) {
            return;
        }

        $acfViewData = $this->cache->getAcfViewData($postId);

        $this->updateIdentifiers($acfViewData);
        $this->updateMarkup($acfViewData);

        // it'll also update post fields, like 'comment_count'
        $acfViewData->saveToPostContent();
    }

    public function addCPT(): void
    {
        $labels = [
            'name' => __('ACF Views', 'acf-views'),
            'singular_name' => __('ACF View', 'acf-views'),
            'menu_name' => __('ACF Views', 'acf-views'),
            'parent_item_colon' => __('Parent ACF View', 'acf-views'),
            'all_ite__(ms' => __('ACF Views', 'acf-views'),
            'view_item' => __('Browse ACF View', 'acf-views'),
            'add_new_item' => __('Add New ACF View', 'acf-views'),
            'add_new' => __('Add New', 'acf-views'),
            'edit_item' => __('Edit ACF View', 'acf-views'),
            'update_item' => __('Update ACF View', 'acf-views'),
            'search_items' => __('Search ACF View', 'acf-views'),
            'not_found' => __('Not Found', 'acf-views'),
            'not_found_in_trash' => __('Not Found In Trash', 'acf-views'),
        ];

        $args = [
            'label' => __('ACF Views', 'acf-views'),
            'description' => __(
                'Create ACF View item to select target ACF fields and copy the shortcode to display field values for a specific post/page/CPT item.',
                'acf-views'
            ),
            'labels' => $labels,
            'public' => true,
            // e.g. Yoast doesn't reflect in Sitemap then
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_rest' => false,
            'has_archive' => false,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'delete_with_user' => false,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'can_export' => false,
            'rewrite' => false,
            'query_var' => false,
            'menu_icon' => 'dashicons-format-gallery',
            'supports' => ['title',],
            'show_in_graphql' => false,
        ];

        register_post_type(self::NAME, $args);
    }

    public function getColumns(array $columns): array
    {
        unset($columns['date']);

        return array_merge($columns, [
            self::COLUMN_DESCRIPTION => __('Description', 'acf-views'),
            self::COLUMN_SHORTCODE => __('Shortcode', 'acf-views'),
            self::COLUMN_AUTHOR => __('Author', 'acf-views'),
            self::COLUMN_LAST_MODIFIED => __('Last modified', 'acf-views'),
            self::COLUMN_CREATED => __('Created', 'acf-views'),
        ]);
    }

    public function getSortableColumns(array $columns): array
    {
        return array_merge($columns, [
            self::COLUMN_AUTHOR => self::COLUMN_AUTHOR,
            self::COLUMN_LAST_MODIFIED => self::COLUMN_LAST_MODIFIED,
            self::COLUMN_CREATED => self::COLUMN_CREATED,
        ]);
    }

    public function addSortableColumnsToRequest(WP_Query $query): void
    {
        if (!is_admin()) {
            return;
        }

        $orderBy = $query->get('orderby');

        switch ($orderBy) {
            case self::COLUMN_AUTHOR:
                $query->set('orderby', 'post_author');
                break;
            case self::COLUMN_LAST_MODIFIED:
                $query->set('orderby', 'post_modified');
                break;
            case self::COLUMN_CREATED:
                $query->set('orderby', 'post_date');
                break;
        }
    }

    public function printColumn(string $column, int $postId): void
    {
        switch ($column) {
            case self::COLUMN_DESCRIPTION:
                $view = $this->cache->getAcfViewData($postId);

                echo esc_html($view->description);
                break;
            case self::COLUMN_SHORTCODE:
                echo $this->html->postboxShortcodes(
                    $postId,
                    true,
                    Plugin::SHORTCODE,
                    get_the_title($postId),
                    false
                );
                break;
            case self::COLUMN_AUTHOR:
                echo esc_html(get_user_by('id', get_post($postId)->post_author)->display_name ?? '');
                break;
            case self::COLUMN_LAST_MODIFIED:
                echo esc_html(explode(' ', get_post($postId)->post_modified)[0]);
                break;
            case self::COLUMN_CREATED:
                echo esc_html(explode(' ', get_post($postId)->post_date)[0]);
                break;
        }
    }

    public function addMetaboxes(): void
    {
        parent::addMetaboxes();

        add_meta_box(
            'acf-views_shortcode',
            __('Shortcode', 'acf-views'),
            function ($post, $meta) {
                if (!$post ||
                    'publish' !== $post->post_status) {
                    echo __('Your View shortcode is available after publishing.', 'acf-views');

                    return;
                }

                echo $this->html->postboxShortcodes(
                    $post->ID,
                    false,
                    Plugin::SHORTCODE,
                    get_the_title($post),
                    false
                );
            },
            [
                self::NAME,
            ],
            'side',
            // right after the publish button
            'core'
        );
    }

    public function getTitlePlaceholder(string $title): string
    {
        $screen = get_current_screen()->post_type ?? '';
        if (self::NAME !== $screen) {
            return $title;
        }

        return __('Name your view', 'acf-views');
    }

    public function removeAddNewItemSubmenuLink(): void
    {
        $url = sprintf('edit.php?post_type=%s', self::NAME);

        global $submenu;

        if (!$submenu[$url]) {
            $submenu[$url] = [];
        }

        foreach ($submenu[$url] as $itemKey => $item) {
            if (3 !== count($item) ||
                $item[2] !== 'post-new.php?post_type=acf_views') {
                continue;
            }

            unset($submenu[$url][$itemKey]);
            break;
        }
    }
}
