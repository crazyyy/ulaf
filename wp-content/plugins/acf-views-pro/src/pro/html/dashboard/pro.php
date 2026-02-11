<?php

$view = $view ?? [];
$formNonce = $view['formNonce'] ?? '';
$formMessage = $view['formMessage'] ?? '';
$license = $view['license'] ?? '';
$licenseExpiration = $view['licenseExpiration'] ?? '';
$licenseUsedDomains = $view['licenseUsedDomains'] ?? '';
$licenseUsedDevDomains = $view['licenseUsedDevDomains'] ?? '';

if ($licenseExpiration) {
    $licenseExpiration = DateTime::createFromFormat('Ymd', $licenseExpiration);
    $licenseExpiration = $licenseExpiration ?
        $licenseExpiration->format('d-m-Y') :
        '';
}

$activateButtonLabel = !$licenseExpiration ?
    __('Activate', 'acf-views') :
    __('Update', 'acf-views');

?>

<form action="" method="post" class="av-dashboard">
    <input type="hidden" name="_av-pro" value="_av-pro">
    <input type="hidden" name="_wpnonce"
           value="<?php
           echo esc_attr($formNonce) ?>">

    <div class="av-dashboard__main">

        <?php
        if ($formMessage) { ?>
            <div class="av-introduction av-dashboard__block av-dashboard__block--medium">
                <?php
                echo $formMessage; ?>
            </div>
            <?php
        } ?>


        <div class="av-introduction av-dashboard__block">
            <p class="av-introduction__title"><?php
                echo __('Activate Pro License Key', 'acf-views'); ?></p>
            <p class="av-introduction__description">
                <?php
                echo __(
                    'To receive automatic updates, including compatibility and security updates, please insert your license key.',
                    'acf-views'
                ); ?>
                <br><br>
            </p>

            <div class="av-license">
                <span class="av-license__label"><?php
                    echo __('License Key:', 'acf-views'); ?></span>
                <div>
                    <input class="av-license__input" type="text" name="_license" value="<?php
                    echo $license ?>" placeholder="XXXX-XXXX-XXXX-XXXX">
                    <p class="av-introduction__description av-introduction__description--type--light">
                        <?php
                        echo __('The key was sent to your email from WPLake.org', 'acf-views'); ?>
                    </p>
                </div>
            </div>

            <div class="av-license">
                <span class="av-license__label"><?php
                    echo __('Status:', 'acf-views'); ?></span>
                <?php
                if ($licenseExpiration) { ?>
                    <div
                        class="av-license__description av-introduction__description av-introduction__description--type--success">
                        <span><?php
                            echo __("You're currently receiving automatic updates.", 'acf-views'); ?> </span><br>
                        <span><?php
                            echo __('License expires on:', 'acf-views'); ?> <b><?php
                                echo $licenseExpiration ?></b>.</span><br>
                        <span><?php
                            echo __('Used on websites:', 'acf-views'); ?> <b><?php
                                echo $licenseUsedDomains ?></b>. </span><br>
                        <span><?php
                            echo __('Used on dev websites:', 'acf-views'); ?> <b><?php
                                echo $licenseUsedDevDomains ?></b>.</span>
                    </div>
                    <?php
                } else { ?>
                    <span
                        class="av-license__description av-introduction__description av-introduction__description--type--fail">
                        <?php
                        echo __('Automatic updates are unavailable.', 'acf-views'); ?>
                    </span>
                    <?php
                } ?>
            </div>

            <br> <br>

            <div style="display: flex;justify-content: space-between;max-width: 200px;">
                <button class="button button-primary button-large av-dashboard__button" name="_activate"><?php
                    echo esc_html($activateButtonLabel) ?>
                </button>
                <!-- <?php
                // don't allow to deactivate (need to put new or leave empty)
                // because deactivation doesn't remove the domain from the list on the update server
                /*                if ($licenseExpiration) { */ ?>
                    <button class="button button-primary button-large av-dashboard__button av-dashboard__button--red"
                            name="_deactivate">Deactivate
                    </button>
                    --><?php
                /*                }*/ ?>
            </div>


            <br> <br>

            <p class="av-introduction__description av-introduction__description--type--light">
                <?php
                if ($licenseExpiration) { ?>
                    <span><?php
                        echo __(
                            "'Status' information updates regularly. Press the 'Update' button to refresh now.",
                            'acf-views'
                        ); ?></span>
                    <br>
                    <span><?php
                        echo __('Read how we determine if a domain is considered a Dev', 'acf-views'); ?>&nbsp;<a
                            href="https://docs.acfviews.com/getting-started/pro-license#dev-and-live-domains"
                            target="_blank"><?php
                            echo __('here', 'acf-views'); ?></a>.</span>
                    <br>
                    <span><?php
                        echo __('If you want to detach this domain from your license please', 'acf-views'); ?>&nbsp;<a
                            href="https://wplake.org/acf-views-support/"
                            target="_blank"><?php
                            echo __('contact us', 'acf-views'); ?></a>.</span>
                    <?php
                } else { ?>
                    <span><?php
                        echo __("If you're having problems activating your license key please", 'acf-views'); ?>&nbsp;<a
                            href="https://wplake.org/acf-views-support/"
                            target="_blank"><?php
                            echo __('contact us', 'acf-views'); ?></a>.</span>
                    <?php
                } ?>
            </p>
        </div>
    </div>
</form>
