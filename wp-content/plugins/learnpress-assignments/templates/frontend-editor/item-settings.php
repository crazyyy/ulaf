<?php
/**
 * Template for displaying settings of assignment on FrontEnd Editor.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/frontend-editor/item-settings.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Assignments/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit(); ?>

<script type="text/x-template" id="tmpl-e-course-item-settings-lp_assignment">
    <div class="e-item-settings-extra">
        <ul class="e-form-field-table flex">
            <component v-for="i in getFields('lp_assignment')" :is="includeFormField(i)" :settings="settings"
                       :field="i"
                       v-if="drawComponent"
                       :item-data="itemData"
                       :item="item">
            </component>
        </ul>
    </div>
</script>
