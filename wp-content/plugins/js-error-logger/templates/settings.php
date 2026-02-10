<?php if (!defined('ABSPATH')) {
    exit;
} ?>
<div class="js-err-log-settings">
    <section>
        <div class="js-err-log-settings-table">
            <?php
            foreach ($this->_default_settings as $jserrlog_optionName => $jserrlog_defaultValues) {
                if (!isset($jserrlog_defaultValues['type'])) {
                    continue;
                } ?>
                <div class="js-err-log-settings-table-row<?php if (isset($jserrlog_defaultValues['conditional'])) { ?>
                     hidden-<?php echo esc_attr($jserrlog_optionName); ?><?php if (!$this->_settings[$jserrlog_defaultValues['conditional']]) { ?> js-err-log-hidden<?php } ?>
                <?php } ?>">
                    <div class="js-err-log-settings-desc"><label for="<?php echo esc_attr($jserrlog_optionName); ?>"><?php echo esc_html($jserrlog_defaultValues['text']); ?></label>
                            <?php if (isset($jserrlog_defaultValues['desc'])) {
                                $jserrlog_description = implode('<br>', $jserrlog_defaultValues['desc']);
                                echo '<br><div class="js-err-log-option-desc">' . wp_kses($jserrlog_description, ['br' => []]) . '</div>';
                            } ?>
                    </div>
                    <div class="js-err-log-settings-value">
                        <?php
                        $jserrlog_known_values = null;
                        if (isset($jserrlog_defaultValues['filter'])) {
                            $jserrlog_known_values = apply_filters($jserrlog_defaultValues['filter'], null);
                        }
                        if ($jserrlog_known_values !== null) { ?>
                            <div><?php
                                /* translators: WP filter name. */
                                echo esc_html(sprintf(__('This setting is currently managed by the "%s" filter', 'js-error-logger'), $jserrlog_defaultValues['filter'])); ?></div>
                        <?php } else {
                            switch ($jserrlog_defaultValues['type']) {
                                case "switch": ?>
                                    <div class="js-err-log-switch" role="button" tabindex="0">
                                        <input type="checkbox" id="<?php echo esc_attr($jserrlog_optionName); ?>"
                                               name="<?php echo esc_attr($jserrlog_optionName); ?>"<?php if (isset($jserrlog_defaultValues['shows'])) {
                                            echo ' data-shows="' . esc_attr($jserrlog_defaultValues['shows']) . '"';
                                        } ?> <?php if ($this->_settings[$jserrlog_optionName]) {
                                            echo 'checked';
                                        } ?>>
                                        <span class="js-err-log-slider"></span>
                                    </div>
                                    <?php break;
                                case "select":
                                    ?>
                                    <select class="js-err-log-select" id="<?php echo esc_attr($jserrlog_optionName); ?>"
                                            name="<?php echo esc_attr($jserrlog_optionName); ?>">
                                        <?php foreach ($jserrlog_defaultValues['choices'] as $jserrlog_text => $jserrlog_value) { ?>
                                            <option value="<?php echo esc_attr($jserrlog_value); ?>"<?php if ($this->_settings[$jserrlog_optionName] == $jserrlog_value) {
                                                echo ' selected';
                                            } ?>>
                                                <?php echo esc_attr($jserrlog_text); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <?php break;
                                case "number": ?>
                                    <div class="js-err-log-input-holder">
                                        <input class="js-err-log-number-input<?php if (isset($defaultValues['unit'])) {
                                            echo ' has-unit';
                                        } ?>" <?php if (isset($jserrlog_defaultValues['range'])) { ?>
                                               min="<?php echo (int)$jserrlog_defaultValues['range'][0]; ?>"
                                               <?php } ?>type="number"
                                               data-current="<?php echo (int)$this->_settings[$jserrlog_optionName]; ?>"
                                               id="<?php echo esc_attr($jserrlog_optionName); ?>" name="<?php echo esc_attr($jserrlog_optionName); ?>"
                                               value="<?php echo (int)$this->_settings[$jserrlog_optionName] ?>"><?php if (isset($jserrlog_defaultValues['unit'])) { ?>
                                            <div class="js-err-log-setting-unit"><?php echo esc_html($jserrlog_defaultValues['unit']); ?></div>
                                        <?php } ?>
                                        <a href="#" class="js-err-log-button js-err-log-button-reverse-colors js-err-log-save-button js-err-log-option-<?php echo esc_attr($jserrlog_optionName); ?>"
                                           data-option="<?php echo esc_attr($jserrlog_optionName); ?>">
                                            <?php esc_html_e('Save','default'); ?>
                                        </a>
                                    </div>
                                    <?php break;
                                case "multiselect":
                                    wp_enqueue_script('js-err-log-multiselect', $this->_path . '/res/jquery.multiselect/jquery.multiselect.js', [], '2.4.24', ["in_footer" => true]);
                                    wp_enqueue_style('js-err-log-multiselect', $this->_path . '/res/jquery.multiselect/jquery.multiselect.css', [], JSERRLOG_VERSION); ?>
                                    <select multiple class="js-err-log-select" id="<?php echo esc_attr($jserrlog_optionName); ?>"
                                            name="<?php echo esc_attr($jserrlog_optionName); ?>[]">
                                        <?php foreach ($jserrlog_defaultValues['choices'] as $jserrlog_text => $jserrlog_value) { ?>
                                            <option value="<?php echo esc_attr($jserrlog_value); ?>"<?php if (in_array($jserrlog_value, $this->_settings[$jserrlog_optionName])) {
                                                echo ' selected';
                                            } ?>>
                                                <?php echo esc_html($jserrlog_text); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <?php
                                    break;
                                case "text": ?>
                                    <input type="<?php echo esc_attr($jserrlog_defaultValues['type']) ?>" class="js-err-log-text-input"
                                           data-current="<?php echo esc_attr($this->_settings[$jserrlog_optionName]); ?>"
                                           id="<?php echo esc_attr($jserrlog_optionName); ?>" name="<?php echo esc_attr($jserrlog_optionName); ?>"
                                           value="<?php echo esc_attr($this->_settings[$jserrlog_optionName]); ?>">
                                    <a href="#" class="js-err-log-save-button js-err-log-button js-err-log-button-reverse-colors js-err-log-option-<?php echo esc_attr($jserrlog_optionName); ?>"
                                       data-option="<?php echo esc_attr($jserrlog_optionName); ?>">
                                        <?php esc_html_e('Save','default'); ?>
                                    </a>
                                    <?php break;
                                case "color": ?>
                                    <input type="color" id="js-err-log-color"
                                           value="<?php echo esc_attr($this->_settings[$jserrlog_optionName]); ?>"/>
                                    <?php break;
                                case "textarea": ?>
                                    <textarea rows="10" cols="50" class="js-err-log-textarea-input"
                                              id="<?php echo esc_attr($jserrlog_optionName); ?>"
                                              name="<?php echo esc_attr($jserrlog_optionName); ?>"><?php echo esc_html(stripslashes($this->_settings[$jserrlog_optionName])); ?></textarea>
                                    <a href="#" class="js-err-log-save-button js-err-log-button js-err-log-button-reverse-colors js-err-log-option-<?php echo esc_attr($jserrlog_optionName); ?>"
                                       data-option="<?php echo esc_attr($jserrlog_optionName); ?>">
                                        <?php esc_html_e('Save','default'); ?>
                                    </a>
                                <?php }
                        } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</div>
