<div class="col-md-4 col-sm-6">
	<div <?php post_class('stm-single-post-loop'); ?>>
		<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">

			<?php if(has_post_thumbnail()): ?>
				<div class="image">
					<div class="stm-plus"></div>
					<?php the_post_thumbnail('stm-570-250', array('class' => 'img-responsive')); ?>
				</div>
			<?php endif; ?>


			<div class="date <?php echo (!splash_is_layout("af")) ? "heading-font" : "normal_font"; ?>">
                <?php echo esc_attr(get_the_date()); ?>
            </div>

            <div class="title heading-font">
                <?php the_title(); ?>
            </div>
		</a>

		<div class="content">
			<?php the_excerpt(); ?>
		</div>

		<div class="post-meta <?php echo (splash_is_layout("bb")) ? "heading-font" : "normal_font"; ?>">
			<?php $comments_num = get_comments_number(get_the_id()); ?>
			<?php if($comments_num): ?>
				<div class="comments-number">
					<a href="<?php the_permalink() ?>#comments">
						<i class="fa fa-commenting"></i>
						<span><?php echo esc_attr($comments_num); ?></span>
					</a>
				</div>
			<?php else: ?>
				<div class="comments-number">
					<a href="<?php the_permalink() ?>#comments">
						<i class="fa fa-commenting"></i>
						<span>0</span>
					</a>
				</div>
			<?php endif; ?>

			<?php $posttags = get_the_tags();
			if ($posttags): ?>
				<div class="post_list_item_tags">
					<?php $count = 0; foreach($posttags as $tag): $count++; ?>
						<?php if($count == 1): ?>
							<a href="<?php echo get_tag_link($tag->term_id); ?>">
								<i class="fa fa-tag"></i>
								<?php echo esc_html($tag->name); ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

	</div>
</div>