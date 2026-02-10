<?php

namespace RollbarWP;

/**
 * @since 3.0.0
 *
 * @var CheckBoxInput $input
 */
use Rollbar\WordPress\Html\Input\CheckBoxInput;
use Rollbar\WordPress\Html\Template;
// Exit if accessed directly
\defined('ABSPATH') || exit;
foreach ($input->getOptions() as $value => $label) {
    ?>
<div>
    <input
        type="checkbox"
        name="<?php 
    echo \esc_attr($input->getName());
    ?>[]"
        id="<?php 
    echo \esc_attr($input->getId());
    ?>_<?php 
    echo \esc_attr($value);
    ?>"
        data-setting="<?php 
    echo \esc_attr($input->getId());
    ?>"
        value="<?php 
    echo \esc_attr($value);
    ?>"
        <?php 
    echo \checked(\in_array($value, $input->getValue()), display: \false);
    ?>
        <?php 
    echo \disabled($input->isDisabled());
    ?>
        <?php 
    echo \disabled($input->isDisabled());
    ?>
    />
    <?php 
    if (!empty($label)) {
        ?>
        <label for="<?php 
        echo \esc_attr($input->getId());
        ?>_<?php 
        echo \esc_attr($value);
        ?>"><?php 
        echo $label;
        ?></label>
    <?php 
    }
    ?>
</div>
<?php 
}
echo $input->showReset() ? Template::string(\ROLLBAR_PLUGIN_DIR . '/templates/html/reset.php', ['input' => $input]) : '';
if (!empty($input->getHelpText())) {
    ?>
    <p class="description"><?php 
    echo $input->getHelpText();
    ?></p>
<?php 
}
