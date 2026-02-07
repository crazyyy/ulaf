<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta name="format-detection" content="telephone=no">
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php wp_head(); ?>
</head>

<?php
$bg_image = '';
$page_image_bg = get_post_meta(get_the_ID(), 'page_image_bg', true);
if (!empty($page_image_bg)) {
    $page_image_bg = wp_get_attachment_image_src($page_image_bg, 'full');
    if (!empty($page_image_bg[0])) {
        $bg_image = $page_image_bg[0];
    }
}

$bodyBg = '';
if (splash_is_layout('magazine_one')) {
    if (!empty($bg_image))
        $bodyBg = 'style="background: url(' . esc_url($bg_image) . '); background-size: cover; background-attachment: fixed;"';
}

?>

<body <?php body_class(splash_get_body_class()); ?> <?php if (!empty($bodyBg)) echo splash_sanitize_text_field($bodyBg); ?>>
<?php wp_body_open(); ?>
<div id="wrapper" <?php if (!empty($bg_image) && empty($bodyBg)) echo 'style="background: url(' . esc_url($bg_image) . '); background-size: cover; background-attachment: fixed;"'; ?>>
    <?php
    if (file_exists(get_stylesheet_directory() . '/partials/header/envato-preview.php')) {
        get_template_part('partials/header/envato-preview');
    }
    ?>
    <?php
    if (!is_page_template('coming-soon.php')) {
        $header_type = get_theme_mod('header_type', 'header_1');
        switch ($header_type) {
            case 'header_2':
                get_template_part('partials/header/header-second');
                break;
            case 'header_3':
                if (splash_is_layout('soccer_two')) {
                    get_template_part('partials/header/header-third-soccer');
                } else {
                    get_template_part('partials/header/header-third');
                }
                break;
            case 'header_4':
                get_template_part('partials/header/header-four');
                break;
            case 'header_magazine_one':
                get_template_part('partials/header/header-magazine-one');
                break;
            case 'header_5':
                get_template_part('partials/header/header-five');
                break;
            default:
                get_template_part('partials/header/header-default');
                break;
        }
    }

    ?>

    <div id="main">
