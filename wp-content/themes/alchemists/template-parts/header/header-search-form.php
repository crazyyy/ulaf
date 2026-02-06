<?php
/**
 * Template part for Header Secondary section.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.2
 * @version   4.7.0
 */

// Header Search Form
?>
<div class="header-search-form">
	<form action="<?php echo esc_url( home_url( '/' ) ); ?>" id="mobile-search-form" class="search-form">
		<input id="s" name="s" type="text" class="form-control header-mobile__search-control" value="<?php echo get_search_query(); ?>" placeholder="<?php echo esc_attr__( 'Enter your search here...', 'alchemists' ); ?>">
		<button type="submit" class="header-mobile__search-submit"><i class="fa fa-search"></i></button>
	</form>
</div>
