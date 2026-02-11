<?php
if (!defined('ABSPATH')) {
    die();
}

$data = $this->getLayoutData();
?>

<div class="wpextended__card">
    <?php if ($data['header']['enabled'] && !empty($data['header']['logo']['image'])) : ?>
        <div class="wpextended__logo">
            <?php echo wp_get_attachment_image(
                $data['header']['logo']['image'],
                'full',
                false,
                array(
                    'class' => 'wpextended__logo-img',
                    'alt' => esc_attr($data['header']['logo']['alt']),
                )
            ); ?>
        </div>
    <?php endif; ?>

    <h1 class="wpextended__headline">
        <?php echo esc_html($data['content']['headline']['text']); ?>
    </h1>

    <div class="wpextended__description">
        <?php do_action('wpextended/maintenance-mode/before_description'); ?>
        <div class="wpextended__description-text">
            <?php echo wpautop(wp_kses_post($data['content']['body']['text'])); ?>
        </div>
        <?php do_action('wpextended/maintenance-mode/after_description'); ?>
    </div>

    <footer class="wpextended__footer">
        <?php do_action('wpextended/maintenance-mode/before_footer'); ?>
        <p><?php echo esc_html($data['content']['footer']['text']); ?></p>
        <?php do_action('wpextended/maintenance-mode/after_footer'); ?>
    </footer>
</div>
