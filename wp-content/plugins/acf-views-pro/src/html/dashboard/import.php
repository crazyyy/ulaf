<?php

$view = $view ?? [];
$isHasDemoObjects = $view['isHasDemoObjects'] ?? false;
$formNonce = $view['formNonce'] ?? '';
$formMessage = $view['formMessage'] ?? '';

?>

<form action="" method="post" class="av-dashboard">
    <input type="hidden" name="_av-demo-import" value="_av-demo-import">
    <input type="hidden" name="_wpnonce"
           value="<?php
           echo esc_attr($formNonce) ?>">

    <div class="av-dashboard__main">

        <?php
        if ($formMessage) { ?>
            <div class="av-introduction av-dashboard__block av-dashboard__block--medium">
                <?php
                echo $formMessage;
                if ($isHasDemoObjects) {
                    ?>
                    <br><br>
                    <button class="button button-primary button-large av-dashboard__button av-dashboard__button--red"
                            name="_delete">
                        <?php
                        echo __('Delete imported objects', 'acf-views'); ?>
                    </button>
                    <?php
                } ?>
            </div>
            <?php
        } ?>

        <?php
        if (!$isHasDemoObjects){ ?>
        <div class="av-introduction av-dashboard__block">
            <p class="av-introduction__title"><?php
                echo __('Import Demo to get started in seconds', 'acf-views'); ?></p>
            <p class="av-introduction__description">
                <?php
                echo __(
                    "Whether you're new to ACF Views or you just want to get the basic setup quickly then this tool will help
                you with the following scenarios:",
                    'acf-views'
                ); ?><br><br>
            </p>
            <p><b><?php
                    echo __("Display page's ACF fields on the same page", 'acf-views'); ?></b></p>
            <ol class="av-introduction__description av-introduction__ol">
                <li><?php
                    echo __(
                        "Create 'draft' pages for 'Samsung Galaxy A53', 'Nokia X20' and 'Xiaomi 12T'.",
                        'acf-views'
                    ); ?></li>
                <li><?php
                    echo __(
                        'Create an ACF Field Group called "Phone" with location set to those pages.',
                        'acf-views'
                    ); ?></li>
                <li><?php
                    echo __(
                        'Create an ACF View called "Phone" with fields assigned from the "Phone" Field Group.',
                        'acf-views'
                    ); ?></li>
                <li><?php
                    echo __(
                        "Fill each page’s ACF fields with text and add the ACF View shortcode to the page content.",
                        'acf-views'
                    ); ?></li>
            </ol>
            <p><b><?php
                    echo __('Display a specific post, page or CPT item with its fields', 'acf-views'); ?></b></p>
            <ol class="av-introduction__description av-introduction__ol">
                <li><?php
                    echo __("Create a 'draft' page called 'Article about Samsung'", 'acf-views'); ?></li>
                <li><?php
                    echo __(
                        'Add the ACF View shortcode to the page content with "object-id" argument to "Samsung Galaxy A53".',
                        'acf-views'
                    ); ?>
                </li>
            </ol>
            <p><b><?php
                    echo __(
                        'Display specific posts, pages or CPT items and their fields by using filters',
                        'acf-views'
                    ); ?> <br>
                    <?php
                    echo __('or by manually assigning items', 'acf-views'); ?></b></p>
            <ol class="av-introduction__description av-introduction__ol">
                <li><?php
                    echo __(
                        'Create an ACF Card for "List of Phones" with ACF View "Phone" assigned and filtered to.',
                        'acf-views'
                    ); ?>
                </li>
                <li><?php
                    echo __(
                        "Create a 'draft' page called 'Most popular phones in 2022' and add the ACF Card shortcode to the
                    page content.",
                        'acf-views'
                    ); ?>
                </li>
            </ol>

            <p class="av-introduction__description">
                <br><?php
                echo __('Press the Import button and wait a few seconds.', 'acf-views'); ?><br><br>
                <?php
                echo __(
                    "When the process has completed, you'll see links to all the items for quick editing.",
                    'acf-views'
                ); ?><br><br>
                <b><?php
                    echo __(
                        'Note: After the import, a delete button will appear, that can be used to remove the imported
                    items.',
                        'acf-views'
                    ); ?></b><br><br>
            </p>
            <button class="button button-primary button-large" name="_import"><?php
                echo __('Import demo now', 'acf-views'); ?></button>
            <?php
            } ?>
        </div>
    </div>
</form>
