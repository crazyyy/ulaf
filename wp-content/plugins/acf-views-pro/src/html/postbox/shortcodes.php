<?php

$view = $view ?? [];
$isShort = $view['isShort'] ?? false;
$shortcodeName = $view['shortcodeName'] ?? '';
$viewId = $view['viewId'] ?? 0;
$isSingle = $view['isSingle'] ?? false;
$description = $view['description'] ?? '';
$idArgument = $view['idArgument'] ?? '';
$entryName = $view['entryName'] ?? '';
$typeName = $view['typeName'] ?? '';

$type = $isShort ?
    'short' :
    'full';
?>
<av-shortcodes class="av-shortcodes av-shortcodes--type--<?php
echo esc_attr($type) ?>">
    <span class='av-shortcodes__code av-shortcodes__code--type--short'>[<?php
        echo esc_html($shortcodeName) ?> <?php
        echo esc_html($idArgument) ?>="<?php
        echo esc_html($viewId) ?>" name="<?php
        echo esc_html($entryName) ?>"]</span>

    <?php
    if (!$isShort) { ?>
        <button class="av-shortcodes__copy-button button button-primary button-large"
                data-target=".av-shortcodes__code--type--short"><?php
            echo __('Copy to clipboard', 'acf-views'); ?>
        </button>
        <span><?php
            // don't escape, contains HTML
            echo $description ?></span>
        <?php
        if (!$isSingle) { ?>
            <hr class="av-shortcodes__delimiter">
            <span class='av-shortcodes__code av-shortcodes__code--type--full'>[<?php
                echo esc_html($shortcodeName) ?> view-id="<?php
                echo esc_html($viewId) ?>" object-id="ANOTHER_POST_ID" name="<?php
                echo esc_html($entryName) ?>"]</span>
            <button class='av-shortcodes__copy-button button button-primary button-large'
                    data-target=".av-shortcodes__code--type--full"><?php
                echo __('Copy to clipboard', 'acf-views'); ?>
            </button>
            <span><?php
                echo __(
                    'displays the view, chosen ACF fields should be filled at the target object. Insert ID in place of "ANOTHER_POST_ID".',
                    'acf-views'
                ); ?>
            <a target="_blank"
               href="https://docs.acfviews.com/guides/acf-views/basic/display-custom-post-and-its-fields"><?php
                echo __('Read more', 'acf-views'); ?></a></span>
            <?php
        }
        ?>

        <hr class="av-shortcodes__delimiter">
        <span class='av-shortcodes__code av-shortcodes__code--type--roles'>[<?php
            echo esc_html($shortcodeName) ?> <?php
            echo esc_html($idArgument) ?>="<?php
            echo esc_html($viewId) ?>" name="<?php
            echo esc_html($entryName) ?>" user-with-roles="ROLE1,ROLE2" user-without-roles="ROLE1,ROLE2"]</span>
        <button class='av-shortcodes__copy-button button button-primary button-large'
                data-target=".av-shortcodes__code--type--roles"><?php
            echo __('Copy to clipboard', 'acf-views'); ?>
        </button>
        <span><?php
            echo __('restrict access to the', 'acf-views'); ?>&nbsp;<?php
            echo esc_html($typeName) ?>&nbsp;<?php
            echo __('by using these arguments.', 'acf-views'); ?> <a target="_blank"
                                                                     href="https://docs.acfviews.com/guides/acf-views/features/restrict-visibility-for-user-roles">
        <?php
        echo __('Read more', 'acf-views'); ?></a></span>
        <?php
    } ?>
</av-shortcodes>