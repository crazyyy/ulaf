<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

defined('ABSPATH') || exit;

class Html
{
    protected function render(string $name, array $args = []): string
    {
        $pathToView = __DIR__ . '/html/' . $name . '.php';

        if (!file_exists($pathToView)) {
            return '';
        }

        $view = $args;
        ob_start();
        include $pathToView;

        return ob_get_clean();
    }

    public function postboxShortcodes(
        int $viewId,
        bool $isShort,
        string $shortcodeName,
        string $entryName,
        bool $isSingle
    ): string {
        $description = '';
        $idArgument = '';

        if ($isSingle) {
            $description .= __(
                'displays the card, posts will be queried according to the filters and displayed according to the selected ACF View.',
                'acf-views'
            );
            $description .= ' ';
            $description .= sprintf(
                '<a target="_blank" href="https://docs.acfviews.com/guides/acf-cards/basic/display-multiple-posts-and-their-fields">%s</a>',
                __('Read more', 'acf-views')
            );
            $idArgument = 'card-id';
        } else {
            $description .= __(
                'displays the view, chosen ACF fields should be filled at the same object where the shortcode is pasted (post/page).',
                'acf-views'
            );
            $description .= ' ';
            $description .= sprintf(
                '<a target="_blank" href="https://docs.acfviews.com/guides/acf-views/basic/display-fields-on-a-single-page">%s</a>',
                __('Read more', 'acf-views')
            );
            $idArgument = 'view-id';
        }

        return $this->render('postbox/shortcodes', [
            'isShort' => $isShort,
            'idArgument' => $idArgument,
            'shortcodeName' => $shortcodeName,
            'entryName' => $entryName,
            'viewId' => $viewId,
            'isSingle' => $isSingle,
            'description' => $description,
            'typeName' => $isSingle ? 'Card' : 'View',
        ]);
    }

    public function postboxReview(): string
    {
        return $this->render('postbox/review');
    }

    public function postboxSupport(): string
    {
        return $this->render('postbox/support');
    }

    public function view(int $id, string $classes, string $content): string
    {
        return $this->render('view/view', [
            'id' => $id,
            'classes' => $classes,
            'content' => $content,
        ]);
    }

    public function viewRow(
        string $type,
        string $tabs,
        string $classes,
        string $label,
        string $field,
        bool $isCustomFieldMarkup
    ): string {
        return $this->render('view/row', [
            'tabs' => $tabs,
            'classes' => $classes,
            'type' => $type,
            'label' => $label,
            'field' => $field,
            'isCustomFieldMarkup' => $isCustomFieldMarkup,
        ]);
    }

    // $tabs : [ [isActive, url, label,] ]
    public function dashboardHeader(string $name, array $tabs): string
    {
        return $this->render('dashboard/header', [
            'name' => $name,
            'tabs' => $tabs,
        ]);
    }

    // $supportedFieldTypes : [ group => [], ]
    public function dashboardOverview(
        string $createAcfViewLink,
        string $createAcfCardLink,
        array $supportedFieldTypes,
        array $supportBlock,
        array $reviewBlock,
        string $pluginsVersion,
        string $demoImportLink,
        string $videoReview,
        array $proBanner = []
    ): string {
        $acfPluginInstallLink = get_admin_url(null, Plugin::ACF_INSTALL_URL);

        return $this->render('dashboard/overview', [
            'createAcfViewLink' => $createAcfViewLink,
            'createAcfCardLink' => $createAcfCardLink,
            'supportedFieldTypes' => $supportedFieldTypes,
            'supportBlock' => $this->postboxSupport(),
            'reviewBlock' => $reviewBlock,
            'pluginsVersion' => $pluginsVersion,
            'proBanner' => $proBanner,
            'demoImportLink' => $demoImportLink,
            'videoReview' => $videoReview,
            'acfPluginInstallLink' => $acfPluginInstallLink,
        ]);
    }

    public function dashboardImport(bool $isHasDemoObjects, string $formNonce, string $formMessage): string
    {
        return $this->render('dashboard/import', [
            'isHasDemoObjects' => $isHasDemoObjects,
            'formNonce' => $formNonce,
            'formMessage' => $formMessage,
        ]);
    }

    public function proBanner(string $link, string $image): string
    {
        return $this->render('postbox/pro-banner', [
            'link' => $link,
            'image' => $image,
        ]);
    }

    public function getProBanner(string $link, string $image): array
    {
        return [
            'link' => $link,
            'image' => $image,
        ];
    }
}
