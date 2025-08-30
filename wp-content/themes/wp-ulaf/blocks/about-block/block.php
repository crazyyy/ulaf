<?php
/**
 * Block template file: blocks/about-block/block.php
 *
 * Wpeb About Block Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'wpeb-about-block-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'section block-wpeb-about-block';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
?>

<style type="text/css">
	<?php echo '#' . $id; ?> {
		/* Add styles that use ACF values here */
	}
</style>

<!-- About Section -->
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>" id="about">
    <h2 class="section-title"><?php the_field( 'title' ); ?></h2>
    <div class="about-container">
        <div class="about-content">
            <?php the_field( 'content' ); ?>
        </div>
        <?php $image = get_field( 'image' ); ?>
        <?php if ( $image ) : ?>
            <div class="about-image" style="background-image: url('<?php echo esc_url( $image['url'] ); ?>');"></div>
        <?php endif; ?>
    </div>
</section>