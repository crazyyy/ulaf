<?php

$view = $view ?? [];
$createAcfViewLink = $view['createAcfViewLink'] ?? '';
$createAcfCardLink = $view['createAcfCardLink'] ?? '';
$supportedFieldTypes = $view['supportedFieldTypes'] ?? '';
$supportBlock = $view['supportBlock'] ?? '';
$reviewBlock = $view['reviewBlock'] ?? '';
$pluginsVersion = $view['pluginsVersion'] ?? '';
// $proBanner = $view['proBanner'] ?? '';
$proBanner = '';
$demoImportLink = $view['demoImportLink'] ?? '';
$videoReview = $view['videoReview'] ?? '';
$acfPluginInstallLink = $view['acfPluginInstallLink'] ?? '';
?>
<div class="av-dashboard">
    <div class="av-dashboard__main">
        <div class="av-introduction av-dashboard__block">
            <p class="av-introduction__title"><?php
                echo __('ACF Views for WordPress', 'acf-views'); ?></p>
            <div class="av-introduction__description">
                <?php
                echo __(
                    'The plugin allows you to display selected ACF fields or Posts anywhere using shortcodes, HTML markup is
                created automatically by the plugin.',
                    'acf-views'
                ); ?>
            </div>
        </div>

        <div class="av-introduction av-dashboard__block">
            <p class="av-introduction__title"><?php
                echo __('How it works', 'acf-views'); ?></p>
            <div class="av-introduction__description">
                <b><?php
                    echo __('View for ACF fields', 'acf-views'); ?></b><br>
                <a href="https://docs.acfviews.com/guides/acf-views/basic/creating-an-acf-view" target="_blank"><?php
                    echo __(
                        'Create a
                    View',
                        'acf-views'
                    ); ?></a> <?php
                echo __(
                    "and assign one or more custom fields, our plugin then generates a shortcode that you'll use to display the field values to users. Style the output with the CSS field included in every View.",
                    'acf-views'
                ); ?>
                <br><br>
                <b><?php
                    echo __('Card for post selections', 'acf-views'); ?></b><br>
                <a href="https://docs.acfviews.com/guides/acf-cards/basic/creating-an-acf-card" target="_blank"><?php
                    echo __('Create a Card', 'acf-views'); ?></a>&nbsp;<?php
                echo __(
                    "and assign posts (or CPT items), choose a View (that will be used to display each item) and our plugin generates a shortcode that you'll use to display the set of posts. The list of posts can be assigned manually or dynamically with filters.",
                    'acf-views'
                ); ?>
            </div>
        </div>

        <div class="av-introduction av-dashboard__block">
            <p class="av-introduction__title"><?php
                echo __('Import Demo to get started in seconds', 'acf-views'); ?></p>
            <div class="av-introduction__description">
                <?php
                echo __(
                    "Whether you're new to ACF Views or you just want to get the basic setup quickly then try",
                    'acf-views'
                ); ?>&nbsp;<a
                    href="<?php
                    echo esc_attr($demoImportLink) ?>"><?php
                    echo __('a demo import', 'acf-views'); ?></a>.
            </div>
        </div>
    </div>
    <div class="av-dashboard__side">
        <div class="av-dashboard__side-block">
            <p><?php
                echo __("Plugin's version is", 'acf-views'); ?> <b><?php
                    echo esc_html($pluginsVersion) ?></b></p>
        </div>
        <div class="av-dashboard__side-block">
            <p class="av-dashboard__title"><?php
                echo __('Having issues?', 'acf-views'); ?></p>
            <?php
            echo $supportBlock;
            ?>
        </div>
        <div class="av-dashboard__side-block">
            <p class="av-dashboard__title"><?php
                echo __('Rate & review', 'acf-views'); ?></p>
            <?php
            list($currentView, $view) = [$view, $reviewBlock];
            include __DIR__ . '/../postbox/review.php';
            $view = $currentView;
            ?>
        </div>
        <?php
        if ($proBanner) { ?>
            <div class="av-dashboard__side-block">
                <?php
                list($currentView, $view) = [$view, $proBanner];
                include __DIR__ . '/../postbox/pro-banner.php';
                $view = $currentView;
                ?>
            </div>
            <?php
        } ?>
    </div>
</div>
