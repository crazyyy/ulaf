<?php

$view = $view ?? [];

$link = $view['link'] ?? '';
$image  = $view['image'] ?? '';
?>
<a href='<?php
echo esc_attr($link) ?>' target='_blank'><img src='<?php
    echo esc_attr($image) ?>' alt='pro' style='max-width: 100%;'></a>
