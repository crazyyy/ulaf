<?php
	$service = false; 
	if(is_page_template('home-service-layout.php')) {
		$service = true;
	}
?>

<div id="frontend_customizer" style="left: -276px;">
	<div class="customizer_wrapper">

		<h3><?php esc_html_e('Demos', 'splash'); ?></h3>
		<div class="customizer_element __demos">
			<select name="demos_switcher" id="demos_switcher" data-class="demos_switcher">
				<option class="demo_one" value="https://splash.stylemixthemes.com/basketball"><?php esc_html_e( 'Basketball', 'splash' ); ?></option>
                <option class="demo_four" value="https://splash.stylemixthemes.com/basketball-two/"><?php esc_html_e( 'Basketball Two', 'splash' ); ?></option>
				<option class="demo_two" value="https://splash.stylemixthemes.com/football/"><?php esc_html_e( 'Football', 'splash' ); ?></option>
				<option class="demo_three" value="https://splash.stylemixthemes.com/soccer/"><?php esc_html_e( 'Soccer', 'splash' ); ?></option>
				<option class="demo_five" value="https://splash.stylemixthemes.com/soccer-two/"><?php esc_html_e( 'Soccer Two', 'splash' ); ?></option>
				<option class="demo_four" value="https://splash.stylemixthemes.com/baseball/"><?php esc_html_e( 'Baseball', 'splash' ); ?></option>
				<option class="demo_five" value="https://splash.stylemixthemes.com/magazine-one/"><?php esc_html_e( 'Club News', 'splash' ); ?></option>
				<option class="demo_five" value="https://splash.stylemixthemes.com/magazine-two/"><?php esc_html_e( 'Football Magazine', 'splash' ); ?></option>
                <option class="demo_five" value="https://splash.stylemixthemes.com/soccer-news/"><?php esc_html_e( 'Soccer News', 'splash' ); ?></option>
                <option class="demo_five" value="https://splash.stylemixthemes.com/hockey/"><?php esc_html_e( 'Hockey', 'splash' ); ?></option>
                <option class="demo_five" value="https://splash.stylemixthemes.com/esport/"><?php esc_html_e( 'eSport', 'splash' ); ?></option>
                <option class="demo_five" value="https://splash.stylemixthemes.com/volleyball/"><?php esc_html_e( 'Volleyball', 'splash' ); ?></option>
			</select>
		</div>

		<?php if(splash_is_layout('bb')): ?>
		<h3><?php esc_html_e('Nav Mode', 'splash'); ?></h3>

		<div class="customizer_element">
			<div class="stm_switcher active" id="navigation_type">
				<div class="switcher_label disable"><?php esc_html_e('Static', 'splash'); ?></div>
				<div class="switcher_nav"></div>
				<div class="switcher_label enable"><?php esc_html_e('Sticky', 'splash'); ?></div>
			</div>
		</div>
		<?php endif; ?>
		
		<h3><?php esc_html_e('Layout', 'splash'); ?></h3>

		<div class="customizer_element">
			<div class="stm_switcher active" id="layout_mode">
				<div class="switcher_label disable"><?php esc_html_e('Boxed', 'splash'); ?></div>
				<div class="switcher_nav"></div>
				<div class="switcher_label enable"><?php esc_html_e('Wide', 'splash'); ?></div>
			</div>
		</div>

		<div class="customizer_boxed_background">
			<h3><?php esc_html_e( 'Background Image', 'splash' ); ?></h3>

			<div class="customizer_element">
				<div class="customizer_colors" id="background_image">
					<span id="boxed_fifth_bg" class="active" data-image="box_img_5"></span>
					<span id="boxed_first_bg" data-image="box_img_1"></span>
					<span id="boxed_second_bg" data-image="box_img_2"></span>
					<span id="boxed_third_bg" data-image="box_img_3"></span>
					<span id="boxed_fourth_bg" data-image="box_img_4"></span>

				</div>
			</div>
		</div>

		<?php if(!splash_is_layout('af') && !splash_is_layout('baseball') && !splash_is_layout('esport')): ?>
		<h3><?php esc_html_e( 'Color Skin', 'splash' ); ?></h3>

		<div class="customizer_element">
			<div class="customizer_colors" id="skin_color">
				<?php if(splash_is_layout('bb')): ?>
					<span id="site_style_default" class="active"></span>
					<span id="blue"></span>
					<span id="blue-violet"></span>
					<span id="choco"></span>
					<span id="gold"></span>
					<span id="green"></span>
					<span id="orange"></span>
					<span id="sky-blue"></span>
					<span id="turquose"></span>
					<span id="violet-red"></span>
				<?php elseif(splash_is_layout('sccr')): ?>
					<span id="site_style_default" class="active sccr_blue"></span>
					<span id="sccr-orange"></span>
					<span id="sccr-red"></span>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<div id="frontend_customizer_button"><i class="fa fa-cog"></i></div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
		"use strict";
		
		
		$("select[name='demos_switcher']").on("change", function (e) {
			var $sitePreloader = $(".stm-site-preloader");
			window.location.href = $(this).val();
			$('body').addClass("stm-demo-changed");
			if( $sitePreloader.length && ! $sitePreloader.hasClass("active") ) {
				$sitePreloader.addClass("active");
			}
		});
		
		$("select[name='demos_switcher']").on('select2:open', function(e){
			var selectClass = e.currentTarget.dataset.class;
			if(typeof(selectClass) != 'undefined') {
				$('.select2-container--open').addClass(selectClass);
			}
		});
		
		$("select[name='demos_switcher']").on('select2:close', function(e) {
			if($(".customizer-demos").length) {
				$(".customizer-demos").removeClass("active");
			}
		});

        var activeDemo = window.location.href;
        $("#demos_switcher option").each(function() {
			if( $(this).val() === activeDemo ) {
                $(this).prop("selected", true);
            }
        });

        $(document).on("mouseover", ".select2-container.demos_switcher .select2-results__option", function() {
			if($(".customizer-demos").length) {
				$(".customizer-demos").css("top", $(this).position().top+"px").addClass("active");
				
				var $choosedDemo = $(".customizer-demos [demo-number="+$(this).find('span').attr('class')+"]");
				if( $choosedDemo.length ) {
					$choosedDemo.addClass("active").siblings().removeClass("active");
				}
			}
		});
		
		$(document).on("mouseout", ".select2-container.demos_switcher .select2-results__option", function() {
			if($(".customizer-demos").length) {
				$(".customizer-demos").removeClass("active");
			}
			
			if( $(".customizer-demos [demo-number]").length ) {
				$(".customizer-demos [demo-number]").removeClass("active");
			}
		});

		$(window).load(function () {
			$("#frontend_customizer").animate({left: -233}, 300);
		});

		$("#frontend_customizer_button").on('click', function () {
			if ($("#frontend_customizer").hasClass('open')) {
				$("#frontend_customizer").animate({left: -233}, 300);
				$("#frontend_customizer").removeClass('open');
			} else {
				$("#frontend_customizer").animate({left: 0}, 300);
				$("#frontend_customizer").addClass('open');
			}
		});

		$('body').on('click', function (kik) {
			if (!$(kik.target).is('#frontend_customizer, #frontend_customizer *') && $('#frontend_customizer').is(':visible')) {
				$("#frontend_customizer").animate({left: -233}, 300);
				$("#frontend_customizer").removeClass('open');
			}
		});

		var body_class = '';

		$("#skin_color span").on('click', function () {

			$('body').removeClass(body_class);

			var style_id = $(this).attr('id');
			body_class = 'skin-' + style_id;

			$('body').addClass(body_class);
			
			<?php if(splash_is_layout('bb')):?>
			var logo_url = '<?php echo esc_url(get_template_directory_uri().'/assets/images/tmp/logo_'); ?>' + style_id + '.svg';
			<?php elseif(splash_is_layout('sccr')): ?>
			var logo_url = '<?php echo esc_url(get_template_directory_uri().'/assets/images/tmp/sccr/logo_'); ?>' + style_id + '.svg';
			<?php endif; ?>
			
			$("#skin_color .active").removeClass("active");
			
			$(this).addClass("active");
			
			$("#custom_style").remove();
			
			if( style_id != 'site_style_default' ){
				$('#custom_style').remove();
				$("head").append('<link rel="stylesheet" id="custom_style" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/css/skins/skin-custom-'+style_id+'.css?v=' + Math.floor((Math.random() * 100) + 1) + '" type="text/css" media="all">');

				$('.stm-header .logo-main img, .stm-small-logo').attr('src', logo_url);
			} else {
				$('.stm-header .logo-main img, .stm-small-logo').attr('src', logo_url);
			}
		});


		$("#navigation_type").on("click", function () {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');

				$('.stm-header').removeClass('stm-header-fixed-mode stm-header-fixed stm-header-fixed-intermediate');
			} else {
				$(this).addClass('active');

				$('.stm-header').addClass('stm-header-fixed-mode');
			}
		});

		$("#layout_mode").on("click", function () {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');

				$('body').addClass('stm-boxed');
				$('.customizer_boxed_background').slideDown();
				
				$('body').addClass('stm-background-customizer-box_img_5');
			} else {
				$(this).addClass('active');

				$('body').removeClass('stm-boxed');
				$('.customizer_boxed_background').slideUp();
				
				$('body').addClass('stm-background-customizer-box_img_5');
			}
		});
		
		$('#background_image span').on('click', function(){
			$('#background_image span').removeClass('active');
			$(this).addClass('active');
			
			var img_src = $(this).data('image');
			
			$('body').removeClass('stm-background-customizer-box_img_1 stm-background-customizer-box_img_2 stm-background-customizer-box_img_3 stm-background-customizer-box_img_4 stm-background-customizer-box_img_5');
			
			$('body').addClass('stm-background-customizer-' + img_src);
		});

	});

</script>