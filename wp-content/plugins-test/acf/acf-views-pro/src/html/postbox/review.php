<?php

$view = $view ?? [];
?>

<div>
    <p>
        <?php
        echo __(
            'If you like the ACF Views plugin consider leaving a rating. We greatly appreciate feedback!',
            'acf-views'
        ); ?>
    </p>
    <a class="button button-primary button-large" href="https://wordpress.org/plugins/acf-views/#reviews"
       target="_blank"><?php
        echo __('Write a review', 'acf-views'); ?></a>
</div>
