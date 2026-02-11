<?php

namespace RollbarWP;

/**
 * @since 3.0.0
 *
 * @var array{
 *     type: "error"|"warning"|"success"|"info",
 *     message: string,
 * }[] $messages
 */
foreach ($messages as $message) {
    ?>
<div class="notice notice-<?php 
    echo $message['type'];
    ?> is-dismissible">
    <p><?php 
    echo $message['message'];
    ?></p>
</div>
<?php 
}
