<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro\AcfView;

use org\wplake\acf_views\AcfGroups\AcfViewData;
use org\wplake\acf_views\AcfGroups\Item;
use org\wplake\acf_views\AcfView\FieldMeta;
use org\wplake\acf_views\AcfView\Post;
use org\wplake\acf_views\Cache;
use org\wplake\acf_views\Html;
use org\wplake\acf_views\Plugin;
use org\wplake\acf_views\pro\Acf;
use WP_Query;

defined('ABSPATH') || exit;

class AcfViews extends \org\wplake\acf_views\AcfView\AcfViews
{
    const COLUMN_MOUNT_POINTS = self::NAME . '_mountPoints';

    private Acf $acf;

    /**
     * @var ViewMarkup
     */
    protected $viewMarkup;

    public function __construct(
        Html $html,
        ViewMarkup $viewMarkup,
        Plugin $plugin,
        Acf $acf,
        Cache $cache
    ) {
        parent::__construct($html, $viewMarkup, $plugin, $cache);

        $this->acf = $acf;
    }

    protected function addProBannerMetabox(): void
    {
        // nothing
    }

    protected function signupGutenbergBlock(AcfViewData $viewGroup): string
    {
        if (!function_exists('acf_register_block_type')) {
            return '';
        }

        $description = __('ACF View', 'acf-views');
        $description = sprintf(
            '%s #%s. %s',
            $description,
            $viewGroup->getSource(),
            $viewGroup->description
        );

        $acfName = 'acf-views-block-' . $viewGroup->getSource();
        acf_register_block_type([
            'name' => $acfName,
            'title' => get_the_title($viewGroup->getSource()),
            'description' => $description,
            'category' => __('ACF Views', 'acf-views'),
            'supports' => [],
            'render_callback' => function (
                $blockData,
                $content = '',
                $isPreview = false,
                $postId = 0
            ) use ($viewGroup) {
                $viewMarkup = $this->viewMarkup->getMarkup($viewGroup, $postId);

                $acfView = new AcfView(
                    $this->acf,
                    $viewGroup,
                    new Post($postId, [], true),
                    $postId,
                    $viewMarkup
                );
                $acfView->insertFields();

                echo $acfView->getHTML();
            }
        ]);

        return $acfName;
    }

    protected function signupLocalGroup(AcfViewData $view, string $blockId): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        $fieldIds = [];

        foreach ($view->items as $item) {
            if (Acf::GROUP_POST === $item->group) {
                continue;
            }

            $fieldIds[] = $item->field->getAcfFieldId();
        }

        $title = __('ACF View', 'acf-views');
        $title .= ' #' . $view->getSource();

        acf_add_local_field_group([
            'key' => 'acf_views_group_' . $view->getSource(),
            'title' => $title,
            // to hide in lists (e.g. list of groups in ACF Card)
            'private' => true,
            'fields' => [
                [
                    'key' => 'acf_views_clone_' . $view->getSource(),
                    'label' => __('Clone', 'acf-views'),
                    'name' => 'clone',
                    'type' => 'clone',
                    'clone' => $fieldIds,
                    'display' => 'seamless',
                    'layout' => 'block',
                    'prefix_label' => 0,
                    'prefix_name' => 0,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'block',
                        'operator' => '==',
                        'value' => 'acf/' . $blockId,
                    ],
                ],
            ],
        ]);
    }

    protected function updateIdentifiers(AcfViewData $acfViewData): void
    {
        parent::updateIdentifiers($acfViewData);

        foreach ($acfViewData->items as $item) {
            foreach ($item->repeaterFields as $repeaterField) {
                $repeaterField->id = ($repeaterField->id &&
                    !preg_match('/^[a-zA-Z0-9_\-]+$/', $repeaterField->id)) ?
                    '' :
                    $repeaterField->id;


                if ($repeaterField->id &&
                    $repeaterField->id === $this->getUniqueSubFieldId($item, $repeaterField, $repeaterField->id)) {
                    continue;
                }

                $subFieldMeta = new FieldMeta($repeaterField->getAcfFieldId());

                if (!$subFieldMeta->isFieldExist()) {
                    continue;
                }

                // $Post$ fields have '_' prefix, remove it, otherwise looks bad in the markup
                $name = ltrim($subFieldMeta->getName(), '_');
                $repeaterField->id = $this->getUniqueSubFieldId($item, $repeaterField, $name);
            }
        }
    }

    protected function updateMarkup(AcfViewData $acfViewData): void
    {
        // pageId 0, so without CSS, also skipCache and ignoreCustomMarkup
        $viewMarkup = $this->viewMarkup->getMarkup($acfViewData, 0, '', true, true);

        $acfViewData->markup = $viewMarkup;
    }

    public function getUniqueSubFieldId(Item $item, $excludeObject, string $name): string
    {
        $isUnique = true;

        foreach ($item->repeaterFields as $repeaterField) {
            if ($repeaterField === $excludeObject ||
                $repeaterField->id !== $name) {
                continue;
            }

            $isUnique = false;
            break;
        }

        return $isUnique ?
            $name :
            $this->getUniqueSubFieldId($item, $excludeObject, $name . '2');
    }

    public function signupGutenbergBlocks(): void
    {
        if (!function_exists('acf_add_local_field_group') ||
            !function_exists('acf_get_field')) {
            return;
        }

        $args = [
            'post_type' => self::NAME,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            AcfViewData::POST_FIELD_IS_HAS_GUTENBERG => AcfViewData::POST_VALUE_IS_HAS_GUTENBERG,
        ];
        $views = new WP_Query($args);
        $views = $views->get_posts();

        foreach ($views as $post) {
            $view = $this->cache->getAcfViewData($post->ID);

            $blockId = $this->signupGutenbergBlock($view);

            if ($blockId) {
                $this->signupLocalGroup($view, $blockId);
            }
        }
    }

    public function getColumns(array $columns): array
    {
        $columns = parent::getColumns($columns);

        return $this->insertIntoArrayAfterKey($columns, self::COLUMN_SHORTCODE, [
            self::COLUMN_MOUNT_POINTS => __('Mount points', 'acf-views'),
        ]);
    }

    public function printColumn(string $column, int $postId): void
    {
        parent::printColumn($column, $postId);

        switch ($column) {
            case self::COLUMN_MOUNT_POINTS:
                $this->printMountPoints($this->cache->getAcfViewData($postId));
                break;
        }
    }

    public function setHooks(): void
    {
        parent::setHooks();

        add_action('plugins_loaded', function () {
            // can be checked only after 'plugins_loaded' hook (so all plugins' code should be available)
            if ($this->plugin->isAcfPluginAvailable(true)) {
                add_action('acf/init', [$this, 'signupGutenbergBlocks']);
            }
        });
    }

    public function replacePostUpdatedMessage(array $messages): array
    {
        global $post;
        $messages = parent::replacePostUpdatedMessage($messages);

        if (self::NAME === $post->post_type &&
            'publish' === $post->post_status) {
            $acfViewData = $this->cache->getAcfViewData($post->ID);

            $customMarkup = trim($acfViewData->customMarkup);

            if ($customMarkup) {
                $extraMessage = "<br>Custom Markup is in use, if you've added or removed fields then remember to update your Markup too.";
                $messages[self::NAME][1] .= $extraMessage;
                $messages[self::NAME][4] .= $extraMessage;
            }
        }

        return $messages;
    }
}
