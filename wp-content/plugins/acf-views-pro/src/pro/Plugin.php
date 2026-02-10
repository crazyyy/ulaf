<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro;

use org\wplake\acf_views\AcfGroups\AcfCardData;
use org\wplake\acf_views\AcfView\Post;
use org\wplake\acf_views\Cache;
use org\wplake\acf_views\Options;
use org\wplake\acf_views\pro\AcfCard\AcfCard;
use org\wplake\acf_views\pro\AcfCard\AcfCards;
use org\wplake\acf_views\pro\AcfCard\CardMarkup;
use org\wplake\acf_views\pro\AcfCard\QueryBuilder;
use org\wplake\acf_views\pro\AcfView\AcfView;
use org\wplake\acf_views\pro\AcfView\AcfViews;
use org\wplake\acf_views\pro\AcfView\ViewMarkup;
use org\wplake\acf_views\Settings;

defined('ABSPATH') || exit;

class Plugin extends \org\wplake\acf_views\Plugin
{
    const JS_MASONRY_ID = AcfViews::NAME . '_masonry';
    const JS_LIGHT_BOX_ID = AcfViews::NAME . '_light-box';

    private Settings $settings;

    protected string $slug = 'acf-views-pro/acf-views-pro.php';
    protected string $shortSlug = 'acf-views-pro';
    protected string $version = '1.9.2';
    protected bool $isProVersion = true;

    /**
     * @var ViewMarkup
     */
    protected $viewMarkup;
    /**
     * @var CardMarkup
     */
    protected $cardMarkup;

    public function __construct(
        Settings $settings,
        Acf $acf,
        ViewMarkup $viewMarkup,
        CardMarkup $cardMarkup,
        QueryBuilder $queryBuilder,
        Options $options,
        Cache $cache
    ) {
        parent::__construct($acf, $viewMarkup, $cardMarkup, $queryBuilder, $options, $cache);

        $this->settings = $settings;
    }

    protected function getAcfView(Post $dataPost, int $viewId, int $pageId): AcfView
    {
        $viewGroup = $this->cache->getAcfViewData($viewId);

        $markup = $this->viewMarkup->getMarkup($viewGroup, $pageId);

        return new AcfView($this->acf, $viewGroup, $dataPost, $pageId, $markup);
    }

    protected function getAcfCard(AcfCardData $acfCardData): AcfCard
    {
        return new AcfCard($acfCardData, $this->queryBuilder, $this->cardMarkup);
    }

    protected function enqueueAdminAssets(array $jsData = []): void
    {
        parent::enqueueAdminAssets();

        wp_enqueue_style(AcfViews::NAME . '_pro', $this->getProAssetsUrl('admin.css'), [], $this->getVersion());
    }

    protected function getCommonSelector(array $selectors, string $toAppend): string
    {
        foreach ($selectors as &$selector) {
            $selector .= $toAppend;
        }

        return join(', ', $selectors);
    }

    public function printCustomAssets(): void
    {
        parent::printCustomAssets();

        if ($this->viewMarkup->getLightBoxGalleries()) {
            echo '<svg id="acf-views-zoom-icon" style="display:block;width:0;height: 0;" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 15.7 19.4" xml:space="preserve"><style type="text/css">.st0{fill:none;stroke:#FFFFFF;stroke-miterlimit:10;}</style><path class="st0" d="M12,3.4c1.5,2.3,1.2,5.4-0.6,7.4c-0.8,0.9-1.8,1.6-3,1.9c-3.3,0.9-6.8-1-7.7-4.3c-0.9-3.3,1-6.8,4.3-7.7  C7.7,0,10.5,1.1,12,3.4L12,3.4z"/><path class="st0" d="M11.4,10.9l3.4,5.4c0.5,0.8,0.3,1.9-0.6,2.4c-0.8,0.5-1.9,0.3-2.4-0.6l-3.4-5.4C9.6,12.4,10.6,11.8,11.4,10.9z"/> <g><line class="st0" x1="2.9" y1="6.7" x2="10.6" y2="6.7"/><line class="st0" x1="6.7" y1="2.9" x2="6.7" y2="10.6"/></g></svg>';
        }
    }

    protected function printPluginsCSS(): string
    {
        $allCssCode = '';

        $masonryGalleries = $this->viewMarkup->getMasonryGalleries();
        if ($masonryGalleries) {
            $gallerySelectors = array_column($masonryGalleries, 'selector');

            $allCssCode .= "\n/*masonry*/\n";
            $allCssCode .= sprintf(
                "%s{transition:opacity ease .1s;}",
                $this->getCommonSelector($gallerySelectors, '')
            );
            $allCssCode .= sprintf(
                "%s{content:'';display:block;clear:both;}",
                $this->getCommonSelector($gallerySelectors, '::after')
            );
            $allCssCode .= sprintf(
                "%s{opacity:0;}",
                $this->getCommonSelector($gallerySelectors, ':not([data-masonry])')
            );
        }

        $lightBoxGalleries = $this->viewMarkup->getLightBoxGalleries();
        if ($lightBoxGalleries) {
            $allCssCode .= "\n/*lightbox*/\n";
            $allCssCode .= sprintf(
                "%s{position:relative;}",
                $this->getCommonSelector($lightBoxGalleries, ' .acf-view__image-outer')
            );

            $allCssCode .= sprintf(
                "%s{opacity:1;cursor:pointer;}",
                $this->getCommonSelector($lightBoxGalleries, ' .acf-view__image-outer:hover .acf-view__image-inner')
            );

            // several '%' to escape
            $allCssCode .= sprintf(
                "%s{display:block;width:100%%;height:100%%;}",
                $this->getCommonSelector($lightBoxGalleries, ' .acf-view__image')
            );

            $allCssCode .= sprintf(
                "%s{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0, 0, 0, 0.4);transition:all .3s ease;opacity:0;}",
                $this->getCommonSelector($lightBoxGalleries, ' .acf-view__image-inner')
            );

            // several '%' to escape
            $allCssCode .= sprintf(
                "%s{width: 20px;height: 20px;position: absolute;left: 50%%;top: 50%%;transform: translate(-50%%);}",
                $this->getCommonSelector($lightBoxGalleries, ' .acf-view__image-zoom-icon')
            );

            $allCssCode .= ".acf-views-light-box{position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;display: flex;justify-content: center;align-items: center;background:rgba(0, 0, 0, 0.9);padding:5%;}";
            $allCssCode .= ".acf-views-light-box__image{max-width:100%;max-height:100%;}";
            $allCssCode .= ".acf-views-light-box__icon{stroke: currentColor;stroke-linecap: square;stroke-width: 6px;fill:none;position: absolute;z-index: 9;bottom: 15px;left: 50%;transform: translateX(50%);color:white;opacity:.5;transition:all ease .3s;}";
            $allCssCode .= ".acf-views-light-box__icon:hover{cursor:pointer;opacity:.7;}";
            $allCssCode .= ".acf-views-light-box__icon-left{transform: scaleX(-1) translateX(150%);}";
            $allCssCode .= ".acf-views-light-box__icon--inactive{opacity:.3;pointer-events:none;}";
        }

        return $allCssCode;
    }

    public function getProAssetsUrl(string $file): string
    {
        return plugin_dir_url(__FILE__) . 'assets/' . $file;
    }

    public function enqueuePaginationJS(): void
    {
        if (!$this->cardMarkup->isWithPagination()) {
            return;
        }

        wp_enqueue_script(
            AcfCards::NAME,
            $this->getProAssetsUrl('pagination.min.js'),
            [],
            $this->getVersion(),
            true
        );

        wp_localize_script(AcfCards::NAME, AcfCards::NAME, [
            'ajaxData' => [
                'url' => get_admin_url(null, 'admin-ajax.php'),
                'name' => AcfCards::NAME,
            ],
        ]);
    }

    public function enqueueMasonryJS(): void
    {
        $masonryGalleries = $this->viewMarkup->getMasonryGalleries();
        if (!$masonryGalleries) {
            return;
        }

        wp_enqueue_script(
            self::JS_MASONRY_ID,
            $this->getProAssetsUrl('light-masonry.min.js'),
            [],
            $this->getVersion(),
            true
        );
        wp_localize_script(self::JS_MASONRY_ID, 'acfViewsMasonry', $masonryGalleries);
    }

    public function enqueueLightBoxJS(): void
    {
        $lightboxGalleries = $this->viewMarkup->getLightBoxGalleries();
        if (!$lightboxGalleries) {
            return;
        }

        wp_enqueue_script(
            self::JS_LIGHT_BOX_ID,
            $this->getProAssetsUrl('light-box.min.js'),
            [],
            $this->getVersion(),
            true
        );
        wp_localize_script(self::JS_LIGHT_BOX_ID, 'acfViewsLightBox', $lightboxGalleries);
    }

    public function makeEnqueueJSAsync(string $tag, string $handle): string
    {
        $tag = parent::makeEnqueueJSAsync($tag, $handle);

        if (!in_array($handle, [AcfCards::NAME, self::JS_MASONRY_ID, self::JS_LIGHT_BOX_ID,], true)) {
            return $tag;
        }

        return str_replace(' src', ' async src', $tag);
    }

    public function setHooks(): void
    {
        parent::setHooks();

        add_action('wp_footer', [$this, 'enqueuePaginationJS']);
        add_action('wp_footer', [$this, 'enqueueMasonryJS']);
        add_action('wp_footer', [$this, 'enqueueLightBoxJS']);
    }

    public function getName(): string
    {
        return __('ACF Views Pro', 'acf-views');
    }
}
