<?php

$view = $view ?? [];
$classes = $view['classes'] ?? '';
$content = $view['content'] ?? '';
$id = $view['id'] ?? '';

$newLine = "\r\n";
$classes = $classes ?
    ' ' . $classes :
    '';
// no escaping for $content, because it's an HTML code (of other things, that have escaped variables)
echo "<div class=\"acf-view acf-view--id--" . esc_attr(
        $id . $classes
    ) . " acf-view--object-id--{object-id}\">" . esc_html(
         $newLine
     ) . $content . "</div>" . esc_html($newLine);

