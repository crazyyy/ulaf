<?php
$top_bar_enable_ticker = get_theme_mod('top_bar_enable_ticker', true);

if($top_bar_enable_ticker):
	wp_enqueue_script('stm-theme-ticker');

	$ticker_args = array(
		'post_type' => 'post',
		'posts_per_page' => '5',
		'post_status' => 'publish'
	);

	$ticker_query = new WP_Query($ticker_args);?>

	<?php if($ticker_query->have_posts()): ?>
		<?php $top_bar_ticker_title = get_theme_mod('top_bar_ticker_title', esc_html__('Breaking news', 'splash')); ?>

		<?php if(!empty($top_bar_ticker_title)):
			$title = '';
			$top_bar_ticker_title = explode(' ', $top_bar_ticker_title);
			$title = '<span class="stm-red">' . $top_bar_ticker_title[0] . '</span> ';
			array_shift($top_bar_ticker_title);
			$top_bar_ticker_title = implode(' ', $top_bar_ticker_title);
			$title .= $top_bar_ticker_title;
		?>

			<div class="heading-font stm-ticker-title"><?php echo wp_kses_post($title); ?></div>
		<?php endif; ?>
		<ol class="stm-ticker">
			<?php while($ticker_query->have_posts()): $ticker_query->the_post(); ?>
                <li>
                <?php if(get_theme_mod('top_bar_link_ticker', false)) { ?><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                <?php } else { the_title(); } ?>
			<?php endwhile; ?>
		</ol>
	<?php wp_reset_postdata(); ?>
	<?php endif; ?>

    <script type="text/javascript">
        jQuery(document).ready(function () {
            var args = {};

            <?php if(get_theme_mod('top_bar_link_ticker', false)) :?>
            args= {
                typewriter: false
            };
            <?php endif; ?>

            jQuery('.stm-ticker').Ticker( args );
        });
    </script>

<?php endif; ?>
