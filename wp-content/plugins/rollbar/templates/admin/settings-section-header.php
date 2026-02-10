<?php

namespace RollbarWP;

/**
 * @since 3.0.0
 *
 * @var string $id
 * @var string $title
 * @var string $description
 */
// Exit if accessed directly
\defined('ABSPATH') || exit;
?>
<hr>
<div class="rollbar-settings-section-header">
    <h2 id="<?php 
echo $id;
?>" class="section-heading">
        <?php 
echo $title;
?>
        <span class="dashicons dashicons-admin-collapse"></span>
    </h2>
    <?php 
if (!empty($description)) {
    ?>
        <div class="">
            <?php 
    echo $description;
    ?>
        </div>
    <?php 
}
?>
</div>
<?php 
