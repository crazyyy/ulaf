<?php

namespace RollbarWP;

/**
 * @since 3.0.0
 *
 * @var NumberInput $input
 */
use Rollbar\WordPress\Html\Input\NumberInput;
use Rollbar\WordPress\Html\Template;
// Exit if accessed directly
\defined('ABSPATH') || exit;
?>
<input
    type="number"
    name="<?php 
echo \esc_attr($input->getName());
?>"
    id="<?php 
echo \esc_attr($input->getId());
?>"
    data-setting="<?php 
echo \esc_attr($input->getId());
?>"
    value="<?php 
echo \esc_attr($input->getValue());
?>"
    <?php 
echo \disabled($input->isDisabled());
?>
    <?php 
echo $input->getHelpText() ? 'aria-describedby="' . \esc_attr($input->getId()) . '-help"' : '';
?>
    <?php 
echo $input->serializeAttributes();
?>
/>
<?php 
echo $input->showReset() ? Template::string(\ROLLBAR_PLUGIN_DIR . '/templates/html/reset.php', ['input' => $input]) : '';
if (!empty($input->getLabel())) {
    ?>
    <label for="<?php 
    echo \esc_attr($input->getId());
    ?>"><?php 
    echo $input->getLabel();
    ?></label>
<?php 
}
if (!empty($input->getHelpText())) {
    ?>
    <p class="description"><?php 
    echo $input->getHelpText();
    ?></p>
<?php 
}
