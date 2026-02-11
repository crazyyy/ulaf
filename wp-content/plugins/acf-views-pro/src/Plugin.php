<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfCard\{AcfCard, AcfCards, CardMarkup, QueryBuilder};
use org\wplake\acf_views\AcfGroups\{AcfCardData, AcfViewData, Field, Item, MetaField, RepeaterField, TaxField};
use org\wplake\acf_views\AcfView\{AcfView, AcfViews, Post, ViewMarkup};

defined('ABSPATH') || exit;

class Plugin
{
    const SHORTCODE = AcfViews::NAME;
    const SHORTCODE_CARDS = AcfCards::NAME;

    const DOCS_URL = 'https://docs.acfviews.com/getting-started/acf-views-for-wordpress';
    const PRO_VERSION_URL = 'https://wplake.org/acf-views-pro/';
    const PRO_PRICING_URL = 'https://wplake.org/acf-views-pro/#pricing';
    const BASIC_VERSION_URL = 'https://wplake.org/acf-views/';
    const ACF_INSTALL_URL = 'plugin-install.php?s=deliciousbrains&tab=search&type=author';
    const SURVEY_URL = 'https://forms.gle/Wjb16B4mzgLEQvru6';
    const CONFLICTS_URL = 'https://docs.acfviews.com/getting-started/compatibility#conflicts';

    protected string $slug = 'acf-views/acf-views.php';
    protected string $shortSlug = 'acf-views';
    protected string $version = '1.9.2';
    protected bool $isProVersion = false;

    protected Acf $acf;
    /**
     * @var ViewMarkup
     */
    protected $viewMarkup;
    /**
     * @var CardMarkup
     */
    protected $cardMarkup;
    protected QueryBuilder $queryBuilder;
    protected Options $options;
    protected Cache $cache;
    // used to avoid recursion with post_object/relationship fields
    protected array $displayingView;

    public function __construct(
        Acf $acf,
        ViewMarkup $viewMarkup,
        CardMarkup $cardMarkup,
        QueryBuilder $queryBuilder,
        Options $options,
        Cache $cache
    ) {
        $this->acf = $acf;
        $this->viewMarkup = $viewMarkup;
        $this->cardMarkup = $cardMarkup;
        $this->queryBuilder = $queryBuilder;
        $this->options = $options;
        $this->cache = $cache;
        $this->displayingView = [];
    }

    protected static function getErrorMarkup(string $shortcode, array $args, string $error): string
    {
        $attrs = [];
        foreach ($args as $name => $value) {
            $attrs[] = sprintf('%s="%s"', $name, $value);
        }
        return sprintf(
            "<p style='color:red;'>%s %s %s</p>",
            __('Shortcode error:', 'acf-views'),
            $error,
            sprintf('(%s %s)', $shortcode, implode(' ', $attrs))
        );
    }

    // static, as called also in AcfGroup
    public static function isAcfProPluginAvailable(): bool
    {
        return class_exists('acf_pro');
    }

    protected function getAcfView(Post $dataPost, int $viewId, int $pageId): AcfView
    {
        $viewGroup = $this->cache->getAcfViewData($viewId);

        // don't use the 'AcfViewData->markup' field, as user can override it (and it shouldn't be supported)
        $viewMarkup = $this->viewMarkup->getMarkup($viewGroup, $pageId);

        return new AcfView($this->acf, $viewGroup, $dataPost, $pageId, $viewMarkup);
    }

    protected function getAcfCard(AcfCardData $acfCardData): AcfCard
    {
        return new AcfCard($acfCardData, $this->queryBuilder, $this->cardMarkup);
    }

    protected function getViewPreviewJsData(): array
    {
        $jsData = [
            'HTML' => '',
            'CSS' => '',
        ];

        global $post;

        if (!$this->isCPTScreen(AcfViews::NAME) ||
            'publish' !== $post->post_status) {
            return $jsData;
        }

        $acfViewData = $this->cache->getAcfViewData($post->ID);
        $previewPostId = $acfViewData->previewPost ?: 0;

        if ($previewPostId) {
            $acfView = $this->getAcfView(new Post($previewPostId), $post->ID, 0);
            // without minify, it's a preview
            $acfView->insertFields(false);
            $viewHTML = $acfView->getHTML();
        } else {
            $viewHTML = $this->viewMarkup->getMarkup($acfViewData, 0);
        }

        // amend to allow work the '#view' alias
        $viewHTML = str_replace('class="acf-view ', 'id="view" class="acf-view ', $viewHTML);
        $jsData['HTML'] = htmlentities($viewHTML, ENT_QUOTES);

        $jsData['CSS'] = htmlentities($acfViewData->getCssCode(false, true), ENT_QUOTES);
        $jsData['HOME'] = get_site_url();

        return $jsData;
    }

    protected function getCardPreviewJsData(): array
    {
        $jsData = [
            'HTML' => '',
            'CSS' => '',
        ];

        global $post;

        if (!$this->isCPTScreen(AcfCards::NAME) ||
            'publish' !== $post->post_status) {
            return $jsData;
        }

        $acfCardData = $this->cache->getAcfCardData($post->ID);
        $acfCard = $this->getAcfCard($acfCardData);
        $acfCard->queryPostsAndInsertData(1, false);
        $acfViewData = $this->cache->getAcfViewData($acfCardData->acfViewId);

        // amend to allow work the '#card' alias
        $viewHTML = str_replace(
            'class="acf-card ',
            'id="card" class="acf-card ',
            $acfCard->getHTML()
        );
        $jsData['HTML'] = htmlentities($viewHTML, ENT_QUOTES);
        // Card CSS without minification as it's for views' purposes
        $jsData['CSS'] = htmlentities($acfCardData->getCssCode(false, true), ENT_QUOTES);
        $jsData['VIEW_CSS'] = htmlentities($acfViewData->getCssCode(), ENT_QUOTES);
        $jsData['HOME'] = get_site_url();

        return $jsData;
    }

    protected function enqueueAdminAssets(array $jsData = []): void
    {
        $jsData = array_merge_recursive($jsData, [
            'markupTextarea' => [
                [
                    'idSelector' => AcfViewData::getAcfFieldName(AcfViewData::FIELD_MARKUP),
                    'isReadOnly' => true,
                    'mode' => 'htmlmixed',
                ],
                [
                    'idSelector' => AcfViewData::getAcfFieldName(AcfViewData::FIELD_CSS_CODE),
                    'isReadOnly' => false,
                    'mode' => 'css',
                ],
                [
                    'idSelector' => AcfViewData::getAcfFieldName(AcfViewData::FIELD_JS_CODE),
                    'isReadOnly' => false,
                    'mode' => 'javascript',
                ],
                [
                    'idSelector' => AcfViewData::getAcfFieldName(AcfViewData::FIELD_CUSTOM_MARKUP),
                    'isReadOnly' => false,
                    'mode' => 'htmlmixed',
                ],
                [
                    'idSelector' => AcfViewData::getAcfFieldName(AcfViewData::FIELD_PHP_VARIABLES),
                    'isReadOnly' => false,
                    'mode' => 'php',
                ],
                [
                    'idSelector' => AcfCardData::getAcfFieldName(AcfCardData::FIELD_MARKUP),
                    'isReadOnly' => true,
                    'mode' => 'htmlmixed',
                ],
                [
                    'idSelector' => AcfCardData::getAcfFieldName(AcfCardData::FIELD_CSS_CODE),
                    'isReadOnly' => false,
                    'mode' => 'css',
                ],
                [
                    'idSelector' => AcfCardData::getAcfFieldName(AcfCardData::FIELD_JS_CODE),
                    'isReadOnly' => false,
                    'mode' => 'javascript',
                ],
                [
                    'idSelector' => AcfCardData::getAcfFieldName(
                        AcfCardData::FIELD_CUSTOM_MARKUP
                    ),
                    'isReadOnly' => false,
                    'mode' => 'htmlmixed',
                ],
                [
                    'idSelector' => AcfCardData::getAcfFieldName(AcfCardData::FIELD_QUERY_PREVIEW),
                    'isReadOnly' => true,
                    'mode' => 'javascript',
                ],
            ],
            'fieldSelect' => [
                [
                    'mainSelectId' => Item::getAcfFieldName(Item::FIELD_GROUP),
                    'subSelectId' => Field::getAcfFieldName(Field::FIELD_KEY),
                    'identifierInputId' => Field::getAcfFieldName(Field::FIELD_ID),
                ],
                [
                    'mainSelectId' => AcfCardData::getAcfFieldName(AcfCardData::FIELD_ORDER_BY_META_FIELD_GROUP),
                    'subSelectId' => AcfCardData::getAcfFieldName(AcfCardData::FIELD_ORDER_BY_META_FIELD_KEY),
                    'identifierInputId' => '',
                ],
                [
                    'mainSelectId' => Field::getAcfFieldName(Field::FIELD_KEY),
                    'subSelectId' => RepeaterField::getAcfFieldName(RepeaterField::FIELD_KEY),
                    'identifierInputId' => RepeaterField::getAcfFieldName(RepeaterField::FIELD_ID),
                ],
                [
                    'mainSelectId' => MetaField::getAcfFieldName(MetaField::FIELD_GROUP),
                    'subSelectId' => MetaField::getAcfFieldName(MetaField::FIELD_FIELD_KEY),
                    'identifierInputId' => '',
                ],
                [
                    'mainSelectId' => TaxField::getAcfFieldName(TaxField::FIELD_TAXONOMY),
                    'subSelectId' => TaxField::getAcfFieldName(TaxField::FIELD_TERM),
                    'identifierInputId' => '',
                ],
            ],
            'viewPreview' => $this->getViewPreviewJsData(),
            'cardPreview' => $this->getCardPreviewJsData(),
        ]);

        wp_enqueue_style(AcfViews::NAME, $this->getAssetsUrl('admin.css'), [], $this->getVersion());
        // jquery is necessary for select2 events
        wp_enqueue_script(AcfViews::NAME, $this->getAssetsUrl('admin.js'), ['jquery',], $this->getVersion());
        wp_localize_script(AcfViews::NAME, 'acf_views', $jsData);
    }

    protected function printPluginsCSS(): string
    {
        return '';
    }

    public function isShortcodeAvailableForUser(array $userRoles, array $shortcodeArgs): bool
    {
        $userWithRoles = (string)($shortcodeArgs['user-with-roles'] ?? '');
        $userWithRoles = trim($userWithRoles);
        $userWithRoles = $userWithRoles ?
            explode(',', $userWithRoles) :
            [];

        $userWithoutRoles = (string)($shortcodeArgs['user-without-roles'] ?? '');
        $userWithoutRoles = trim($userWithoutRoles);
        $userWithoutRoles = $userWithoutRoles ?
            explode(',', $userWithoutRoles) :
            [];

        if (!$userWithRoles &&
            !$userWithoutRoles) {
            return true;
        }

        $userHasAllowedRoles = !!array_intersect($userWithRoles, $userRoles);
        $userHasDeniedRoles = !!array_intersect($userWithoutRoles, $userRoles);

        if (($userWithRoles && !$userHasAllowedRoles) ||
            ($userWithoutRoles && $userHasDeniedRoles)) {
            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return __('ACF Views', 'acf-views');
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getShortSlug(): string
    {
        return $this->shortSlug;
    }

    public function getVersion(): string
    {
        // return strval(time());

        return $this->version;
    }

    public function isProVersion(): bool
    {
        return $this->isProVersion;
    }

    public function getAssetsUrl(string $file): string
    {
        return plugin_dir_url(__FILE__) . 'assets/' . $file;
    }

    public function getAcfProAssetsUrl(string $file): string
    {
        return plugin_dir_url(__FILE__) . 'AcfPro/' . $file;
    }

    public function isAcfPluginAvailable(bool $isProOnly = false): bool
    {
        // don't use 'is_plugin_active()' as the function available lately
        return static::isAcfProPluginAvailable() ||
            (!$isProOnly && class_exists('ACF'));
    }

    public function startBuffering(): void
    {
        ob_start();
    }

    public function printStylesStub(): void
    {
        echo '<!--acf-views-styles-->';
    }

    public function showWarningAboutInactiveAcfPlugin(): void
    {
        if ($this->isAcfPluginAvailable()) {
            return;
        }

        $acfPluginInstallLink = get_admin_url(null, static::ACF_INSTALL_URL);
        $acfFree = 'https://wordpress.org/plugins/advanced-custom-fields/';
        $acfPro = 'https://www.advancedcustomfields.com/pro/';

        echo sprintf(
            '<div class="notice notice-error">' .
            '<p>%s <a target="_blank" href="%s">%s</a> (<a target="_blank" href="%s">%s</a> %s <a target="_blank" href="%s">%s</a>) %s</p>' .
            '</div>',
            __('"ACF Views" requires', 'acf-views'),
            $acfPluginInstallLink,
            __('Advanced Custom Fields', 'acf-views'),
            $acfFree,
            __('free', 'acf-views'),
            __('or', 'acf-views'),
            $acfPro,
            __('pro', 'acf-views'),
            __('to be installed and activated.', 'acf-views'),
        );
    }

    public function showWarningAboutOpcacheIssue(): void
    {
        if (!function_exists('ini_get') ||
            '0' !== ini_get('opcache.save_comments')) {
            return;
        }

        $readMoreLink = sprintf(
            '<a target="_blank" href="%s">%s</a>',
            self::CONFLICTS_URL,
            __('Read more', 'acf-views')
        );
        printf(
            '<div class="notice notice-error"><p>%s 
<br>%s %s
</p></div>',
            __('Compatibility issue detected! "ACF Views" plugin requires "PHPDoc" comments in code.', 'acf-views'),
            __(
                'Please change the "opcache.save_comments" option in your php.ini file to the default value of "1" on your hosting.',
                'acf-views'
            ),
            $readMoreLink
        );
    }

    public function acfCardsShortcode($attrs): string
    {
        $attrs = $attrs ?
            (array)$attrs :
            [];

        if (!$this->isShortcodeAvailableForUser(wp_get_current_user()->roles, $attrs)) {
            return '';
        }

        $cardId = (int)($attrs['card-id'] ?? 0);
        $acfCardPost = $cardId ?
            get_post($cardId) :
            null;

        if (!$acfCardPost ||
            !in_array($acfCardPost->post_type, [AcfCards::NAME,], true) ||
            'publish' !== $acfCardPost->post_status
        ) {
            return self::getErrorMarkup(
                self::SHORTCODE_CARDS,
                $attrs,
                __('card-id attribute is missing or wrong', 'acf-views')
            );
        }

        $acfCardData = $this->cache->getAcfCardData($cardId);

        $acfCard = $this->getAcfCard($acfCardData);
        $acfCard->queryPostsAndInsertData(1);

        return $acfCard->getHTML();
    }

    public function acfViewsShortcode($attrs): string
    {
        $attrs = $attrs ?
            (array)$attrs :
            [];

        if (!$this->isShortcodeAvailableForUser(wp_get_current_user()->roles, $attrs)) {
            return '';
        }

        // equals to 0 on WooCommerce Shop Page, but in this case pageID can't be gotten with built-in WP functions
        $currentPageId = get_queried_object_id();
        $viewId = (int)($attrs['view-id'] ?? 0);
        $acfViewPost = $viewId ?
            get_post($viewId) :
            null;

        if (!$acfViewPost ||
            !in_array($acfViewPost->post_type, [AcfViews::NAME,], true) ||
            'publish' !== $acfViewPost->post_status
        ) {
            return self::getErrorMarkup(
                self::SHORTCODE,
                $attrs,
                __('view-id attribute is missing or wrong', 'acf-views')
            );
        }

        global $post;

        // a. dataPostId from the shortcode argument

        $dataPostId = (string)($attrs['object-id'] ?? 0);

        if (in_array($dataPostId, ['$user$', 'options',], true)) {
            $dataPostId = '$user$' === $dataPostId ?
                'user_' . get_current_user_id() :
                $dataPostId;
        } else {
            $dataPostId = (int)$dataPostId;

            // b. dataPostId from the current loop (WordPress posts, WooCommerce products...)

            $dataPostId = $dataPostId ?: ($post->ID ?? 0);

            // c. dataPostId from the current page

            $dataPostId = $dataPostId ?: $currentPageId;

            // validate the ID

            $dataPostId = get_post($dataPostId) ?
                $dataPostId :
                0;
        }

        if (!$dataPostId) {
            return self::getErrorMarkup(
                self::SHORTCODE,
                $attrs,
                __('object-id argument contains the wrong value', 'acf-views')
            );
        }

        // recursionKey must consist from both. It's allowed to use the same View for a post_object field, but with another id
        $recursionKey = $viewId . '-' . $dataPostId;

        /*
         * In case with post_object and relationship fields can be a recursion
         * e.g. There is a post_object field. PostA contains link to PostB. PostB contains link to postA. View displays PostA...
         * In this case just return empty string, without any error message (so user can display PostB in PostA without issues)
         */
        if (isset($this->displayingView[$recursionKey])) {
            return '';
        }

        $this->displayingView[$recursionKey] = true;

        $acfView = $this->getAcfView(new Post($dataPostId), $viewId, $currentPageId);
        $acfView->insertFields();

        $html = $acfView->getHTML();

        unset($this->displayingView[$recursionKey]);

        return $html;
    }

    public function enqueueAdminScripts(): void
    {
        $currentScreen = get_current_screen();
        if ($currentScreen &&
            (in_array($currentScreen->id, [AcfViews::NAME, AcfCards::NAME,], true) ||
                in_array($currentScreen->post_type, [AcfViews::NAME, AcfCards::NAME], true))) {
            $this->enqueueAdminAssets();
        }
    }

    public function printCustomAssets(): void
    {
        $allJsCode = '';
        $allCssCode = $this->printPluginsCSS();

        $views = $this->viewMarkup->getRenderedViews();
        foreach ($views as $view) {
            $cssCode = $view->getCssCode();

            // 'minify' JS
            $jsCode = str_replace(["\t", "\n", "\r"], '', $view->jsCode);
            $jsCode = trim($jsCode);

            // no escaping, it's a CSS code, so e.g '.a > .b' shouldn't be escaped
            $allCssCode .= $cssCode ?
                sprintf("\n/*view-%s*/\n%s", $view->getSource(), $cssCode) :
                '';
            $allJsCode .= $jsCode ?
                sprintf("\n/*view-%s*/\n%s", $view->getSource(), $jsCode) :
                '';
        }

        $cards = $this->cardMarkup->getRenderedCards();
        foreach ($cards as $card) {
            $cssCode = $card->getCssCode();

            // 'minify' JS
            $jsCode = str_replace(["\t", "\n", "\r"], '', $card->jsCode);
            $jsCode = trim($jsCode);

            // no escaping, it's a CSS code, so e.g '.a > .b' shouldn't be escaped
            $allCssCode .= $cssCode ?
                sprintf("\n/*card-%s*/\n%s", $card->getSource(), $cssCode) :
                '';
            $allJsCode .= $jsCode ?
                sprintf("\n/*card-%s*/\n%s", $card->getSource(), $jsCode) :
                '';
        }

        $pageContent = ob_get_clean();
        $cssTag = $allCssCode ?
            sprintf("<style data-acf-views-css='css'>%s</style>", $allCssCode) :
            '';
        $pageContent = str_replace('<!--acf-views-styles-->', $cssTag, $pageContent);

        echo $pageContent;

        if ($allJsCode) {
            printf("<script data-acf-views-js='js'>(function (){%s}())</script>", $allJsCode);
        }
    }

    public function enqueueGoogleMapsJS(): void
    {
        if (!function_exists('acf_get_setting') ||
            !$this->viewMarkup->isWithGoogleMap()) {
            return;
        }

        $apiData = apply_filters('acf/fields/google_map/api', []);

        $key = $apiData['key'] ?? '';

        $key = !$key ?
            acf_get_setting('google_api_key') :
            $key;

        if (!$key) {
            return;
        }

        wp_enqueue_script(
            AcfViews::NAME . '_maps',
            $this->getAssetsUrl('maps.min.js'),
            [],
            $this->getVersion(),
            true
        );

        wp_enqueue_script(
            AcfViews::NAME . '_google-maps',
            sprintf('https://maps.googleapis.com/maps/api/js?key=%s&callback=acfViewsGoogleMaps', $key),
            [
                // setup deps, to make sure loaded only after plugin's maps.min.js
                AcfViews::NAME . '_maps',
            ],
            null,
            true
        );
    }

    public function isCPTScreen(string $cptName, array $targetBase = ['post', 'add',]): bool
    {
        $currentScreen = get_current_screen();

        $isTargetPost = in_array($currentScreen->id, [$cptName,], true) ||
            in_array($currentScreen->post_type, [$cptName], true);

        // base = edit (list management), post (editing), add (adding)
        return $isTargetPost &&
            in_array($currentScreen->base, $targetBase, true);
    }

    public function deactivateOtherInstances(string $activatedPlugin): void
    {
        if (!in_array($activatedPlugin, ['acf-views/acf-views.php', 'acf-views-pro/acf-views-pro.php'], true)) {
            return;
        }

        $pluginToDeactivate = 'acf-views/acf-views.php';
        $deactivatedNoticeId = 1;

        // If we just activated the free version, deactivate the pro version.
        if ($activatedPlugin === $pluginToDeactivate) {
            $pluginToDeactivate = 'acf-views-pro/acf-views-pro.php';
            $deactivatedNoticeId = 2;
        }

        if (is_multisite() &&
            is_network_admin()) {
            $activePlugins = (array)get_site_option('active_sitewide_plugins', []);
            $activePlugins = array_keys($activePlugins);
        } else {
            $activePlugins = (array)get_option('active_plugins', []);
        }

        foreach ($activePlugins as $pluginBasename) {
            if ($pluginToDeactivate !== $pluginBasename) {
                continue;
            }

            $this->options->setTransient(
                Options::TRANSIENT_DEACTIVATED_OTHER_INSTANCES,
                $deactivatedNoticeId,
                1 * HOUR_IN_SECONDS
            );
            deactivate_plugins($pluginBasename);

            return;
        }
    }

    // notice when either Basic or Pro was automatically deactivated
    public function showPluginDeactivatedNotice(): void
    {
        $deactivatedNoticeId = (int)$this->options->getTransient(Options::TRANSIENT_DEACTIVATED_OTHER_INSTANCES);

        // not set = false = 0
        if (!in_array($deactivatedNoticeId, [1, 2,], true)) {
            return;
        }

        $message = sprintf(
            '%s "%s".',
            __(
                "'ACF Views' and 'ACF Views Pro' should not be active at the same time. We've automatically deactivated",
                'acf-views'
            ),
            1 === $deactivatedNoticeId ?
                __('ACF Views', 'acf-views') :
                __('ACF Views Pro', 'acf-views')
        );

        $this->options->deleteTransient(Options::TRANSIENT_DEACTIVATED_OTHER_INSTANCES);

        echo sprintf(
            '<div class="notice notice-warning">' .
            '<p>%s</p>' .
            '</div>',
            $message
        );
    }

    public function amendProFieldLabelAndInstruction(array $field): array
    {
        $isProField = !$this->isProVersion() &&
            key_exists('a-pro', $field);
        $isAcfProField = !$this->isAcfPluginAvailable(true) &&
            key_exists('a-acf-pro', $field);

        if (!$isProField &&
            !$isAcfProField) {
            return $field;
        }

        $type = $field['type'] ?? '';
        $field['label'] = $field['label'] ?? '';
        $field['instructions'] = $field['instructions'] ?? '';

        if ($isProField) {
            if ('tab' === $type) {
                $field['label'] = $field['label'] . ' (Pro)';
            } else {
                $field['instructions'] = sprintf(
                    '<a href="%s" target="_blank">%s</a> %s %s',
                    Plugin::PRO_VERSION_URL,
                    __('Upgrade to Pro', 'acf-views'),
                    __('to unlock.', 'acf-views'),
                    $field['instructions']
                );
            }
        }

        if ($isAcfProField) {
            $field['instructions'] = sprintf(
                '(<a href="%s" target="_blank">%s</a> %s) %s',
                'https://www.advancedcustomfields.com/pro/',
                __('ACF Pro', 'acf-views'),
                __('version is required for this feature', 'acf-views'),
                $field['instructions']
            );
        }

        return $field;
    }

    public function addClassToAdminProFieldClasses(array $wrapper, array $field): array
    {
        $isProField = !$this->isProVersion() &&
            key_exists('a-pro', $field);
        $isAcfProField = !$this->isAcfPluginAvailable(true) &&
            key_exists('a-acf-pro', $field);

        if (!$isProField &&
            !$isAcfProField) {
            return $wrapper;
        }

        if (!key_exists('class', $wrapper)) {
            $wrapper['class'] = '';
        }

        $wrapper['class'] .= ' acf-views-pro';

        return $wrapper;
    }

    public function makeEnqueueJSAsync(string $tag, string $handle): string
    {
        if (!in_array($handle, [
            AcfViews::NAME . '_maps',
            AcfViews::NAME . '_google-maps'
        ], true)) {
            return $tag;
        }

        // defer, not async as order should be kept (google-maps will call a callback from maps' js)
        return str_replace(' src', ' defer src', $tag);
    }

    public function getAdminUrl(
        string $page = '',
        string $cptName = AcfViews::NAME,
        string $base = 'edit.php'
    ): string {
        $pageArg = $page ?
            '&page=' . $page :
            '';

        // don't use just '/wp-admin/x' as some websites can have custom admin url, like 'wp.org/wordpress/wp-admin'
        $pageUrl = get_admin_url(null, $base . '?post_type=');

        return $pageUrl . $cptName . $pageArg;
    }

    public function printSurveyLink(string $html): string
    {
        if (!$this->isCPTScreen(AcfViews::NAME, ['post', 'add', 'edit',]) &&
            !$this->isCPTScreen(AcfCards::NAME, ['post', 'add', 'edit',])) {
            return $html;
        }

        $content = sprintf(
            '%s <a target="_blank" href="%s">%s</a> %s <a target="_blank" href="%s">%s</a>.',
            __('Thank you for creating with', 'acf-views'),
            'https://wordpress.org/',
            __('WordPress', 'acf-views'),
            __('and', 'acf-views'),
            self::BASIC_VERSION_URL,
            __('ACF Views', 'acf-views')
        );
        $content .= " " . sprintf(
                "<span>%s <a target='_blank' href='%s'>%s</a> %s</span>",
                __('Take', 'acf-views'),
                self::SURVEY_URL,
                __('2 minute survey', 'acf-views'),
                __('to improve the ACF Views plugin.', 'acf-views')
            );

        return sprintf(
            '<span id="footer-thankyou">%s</span>',
            $content
        );
    }

    public function setHooks(): void
    {
        add_action('admin_notices', [$this, 'showWarningAboutInactiveAcfPlugin']);
        add_action('admin_notices', [$this, 'showWarningAboutOpcacheIssue']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_action('wp_footer', [$this, 'printCustomAssets']);
        add_action('wp_footer', [$this, 'enqueueGoogleMapsJS']);
        add_action('activated_plugin', [$this, 'deactivateOtherInstances']);
        add_action('pre_current_active_plugins', [$this, 'showPluginDeactivatedNotice']);
        add_action('wp_head', [$this, 'printStylesStub']);
        // don't use 'get_header', as it doesn't work in blocks theme
        add_action('template_redirect', [$this, 'startBuffering']);

        add_shortcode(Plugin::SHORTCODE, [$this, 'acfViewsShortcode']);
        add_shortcode(Plugin::SHORTCODE_CARDS, [$this, 'acfCardsShortcode']);

        add_filter('acf/prepare_field', [$this, 'amendProFieldLabelAndInstruction']);
        add_filter('acf/field_wrapper_attributes', [$this, 'addClassToAdminProFieldClasses'], 10, 2);
        add_filter('script_loader_tag', [$this, 'makeEnqueueJSAsync'], 10, 2);
        add_filter('admin_footer_text', [$this, 'printSurveyLink']);
    }
}
