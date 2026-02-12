<?php

$view                = $view ?? [];
$tabs                = $view['tabs'] ?? '';
$classes             = $view['classes'] ?? '';
$type                = $view['type'] ?? '';
$label               = $view['label'] ?? '';
$field               = $view['field'] ?? '';
$isCustomFieldMarkup = $view['isCustomFieldMarkup'] ?? false;

$newLine = "\r\n";
$oneTab  = "\t";
$classes = $classes ?
    ' ' . $classes :
    '';

echo esc_html($tabs) . "<div class=\"acf-view__" . esc_attr($type . $classes) . "\">" . esc_html($newLine);

if ($label) {
    echo esc_html($tabs . $oneTab) . "<div class=\"acf-view__label\">" . esc_html($label) . "</div>" . esc_html(
            $newLine
        );
}

if (! $isCustomFieldMarkup) {
    echo esc_html($tabs . $oneTab) . "<div class=\"acf-view__field\">" . esc_html($field) . "</div>" . esc_html(
            $newLine
        );
} else {
    // no escaping for $field, because it's an HTML code (output of FieldMarkup.php, that have escaped variables)
    echo $field;
}

echo esc_html($tabs) . "</div>" . esc_html($newLine);
