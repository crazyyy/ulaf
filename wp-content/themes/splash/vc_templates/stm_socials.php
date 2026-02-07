<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$top_bar_enable_socials = get_theme_mod('top_bar_enable_socials', true);

if(!$top_bar_enable_socials) {
    return false;
}

$stm_socials = splash_socials();

if(!empty($stm_socials)): ?>
    <ul class="stm_socials">
        <?php foreach($stm_socials as $key => $value): ?>
            <li>
                <a href="<?php echo esc_attr($value); ?>" target="_blank">
                    <i class="fa fa-<?php echo esc_attr($key); ?>"></i>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>