
<div class="col-md-12">
	<div <?php post_class('stm-single-post-loop stm-single-post-loop-list'); ?>>
		<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">

			<?php if(has_post_thumbnail()): ?>
				<div class="image <?php if(get_post_format(get_the_ID())) echo get_post_format(get_the_ID()); ?>">
					<div class="stm-plus"></div>
					<?php
					$imgSize = "stm-350-250";

					the_post_thumbnail($imgSize, array('class' => 'img-responsive')); ?>
					<?php if(is_sticky(get_the_id())): ?>
						<div class="stm-sticky-post heading-font"><?php esc_html_e('Sticky Post','splash'); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="date">
				<span class="stm-date-day heading-font"><?php echo esc_attr(get_the_date("d")); ?></span>
				<span class="stm-date-month normal_font"><?php echo esc_attr(get_the_date("M")); ?></span>
			</div>
		</a>

		<div class="stm-post-content-inner">
			<?php if(get_post_format(get_the_ID())): ?>
				<span class="stm-post-format stm-post-format-<?php echo esc_html(get_post_format(get_the_ID())); ?>">
					<?php echo esc_html(get_post_format(get_the_ID())); ?>
				</span>
			<?php endif; ?>

			<a href="<?php the_permalink() ?>">
				<div class="title heading-font">
					<?php the_title(); ?>
				</div>
			</a>

			<div class="content">
				<?php the_excerpt(); ?>
			</div>

			<div class="post-meta normal_font">
					<?php $cat = wp_get_post_terms(get_the_ID(), "category"); ?>
					<?php
					if(count($cat) > 0) :
						$catList = "<ul>";
						foreach ($cat as $k => $val) {
							$catList = $catList . "<li><a href='" . get_term_link($val->term_id) . "'>" . $val->name;
							if(($k + 1) < count($cat)) $catList = $catList . ", ";
							$catList = $catList . "</a></li>";
						}
						$catList = $catList . "</ul>";
						?>

						<div class="stm-cat-list-wrapp">
							<i class="fa fa-folder-o" aria-hidden="true"></i>
							<?php echo splash_sanitize_text_field($catList); ?>
						</div>
					<?php endif; ?>

					<?php $comments_num = get_comments_number(get_the_id()); ?>
					<?php if($comments_num): ?>
						<div class="comments-number">
							<a href="<?php the_permalink() ?>#comments">
								<i class="fa fa-comment-o" aria-hidden="true"></i>
								<span><?php echo esc_attr($comments_num); ?> <?php if(splash_is_af()) esc_html_e('comments', 'splash'); ?></span>
							</a>
						</div>
					<?php else: ?>
						<div class="comments-number">
							<a href="<?php the_permalink() ?>#comments">
								<i class="fa fa-comment-o" aria-hidden="true"></i>
								<span>0 <?php if(splash_is_af()) esc_html_e('comments', 'splash');?></span>
							</a>
						</div>
					<?php endif; ?>
				</div>
		</div>
	</div>
</div>