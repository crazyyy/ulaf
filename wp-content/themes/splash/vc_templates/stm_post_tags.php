<?php
if (is_singular('post')){
	$post_tags = get_the_tags(get_the_ID());
	if ( $post_tags ) {
		echo '<div class="post-tags-wrap"><i class="icon-mg-tag"></i>';
		foreach( $post_tags as $tag ) { ?>
			<a href="<?php echo get_tag_link($tag->term_id); ?>">
				<?php echo esc_html($tag->name); ?>
			</a>

		<?php
		}
		echo '</div>';
	}
} ?>
<style>
	.post-tags-wrap i {
		color: #00bfe6;
		margin-right: 15px;
		vertical-align: middle;
	}
	.post-tags-wrap a {
		display: inline-block;
		padding: 5px 9px;
		margin-right: 5px;
		border: 1px #535366 solid;
		color: #fff;
		font-size: 13px;
	}
	.post-tags-wrap a:hover {
		border-color: #00bfe6;
	}
	.post-tags-wrap {
		display: flex;
		flex-wrap: wrap;
		min-height: 44px;
		align-items: center;
	}
</style>
