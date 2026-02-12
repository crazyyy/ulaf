<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro;

defined('ABSPATH') || exit;

class Html extends \org\wplake\acf_views\Html
{
    protected function render(string $name, array $args = []): string
    {
        $pathToView = __DIR__ . '/html/' . $name . '.php';

        if (!file_exists($pathToView)) {
            return parent::render($name, $args);
        }

        $view = $args;
        ob_start();
        include $pathToView;

        return ob_get_clean();
    }

    public function dashboardPro(
        string $formNonce,
        string $formMessage,
        string $license,
        string $licenseExpiration,
        string $licenseUsedDomains,
        string $licenseUsedDevDomains
    ): string {
        return $this->render('dashboard/pro', [
            'formNonce' => $formNonce,
            'formMessage' => $formMessage,
            'license' => $license,
            'licenseExpiration' => $licenseExpiration,
            'licenseUsedDomains' => $licenseUsedDomains,
            'licenseUsedDevDomains' => $licenseUsedDevDomains,
        ]);
    }
}
