(function($) {
	$.fn.SocialCounter = function(options) {
		var settings = $.extend({
			// These are the defaults.
			facebook_user:'',
			facebook_token:'',
			instagram_user:'',
			instagram_user_sandbox:'',
			instagram_token:'',
			google_plus_id:'',
			google_plus_key:''
		}, options);

		function facebook(){
			//Facebook API
			//60 Day Access Token - Regenerate a new one after two months
			//https://smashballoon.com/custom-facebook-feed/access-token/
			$.ajax({
				url: 'https://graph.facebook.com/v3.0/'+settings.facebook_user,
				cache: true,
				dataType: 'json',
				type: 'GET',
				data: {
					access_token:settings.facebook_token,
					fields:'fan_count'
				},
				success: function(data) {
					var followers = parseInt(data.fan_count);
					var k = kFormatter(followers);
					if ($('.btn-social-counter--fb .btn-social-counter__count-num').is(':empty')){
						$('.btn-social-counter--fb .btn-social-counter__count-num').append(k);
					}
				}
			});
			$('.btn-social-counter--fb').attr('href','https://facebook.com/'+settings.facebook_user);
		}
		function instagram(){
			//Create access tokens
			//https://www.youtube.com/watch?v=LkuJtIcXR68
			//http://instagram.pixelunion.net
			//http://dmolsen.com/2013/04/05/generating-access-tokens-for-instagram
			//http://ka.lpe.sh/2015/12/24/this-request-requires-scope-public_content-but-this-access-token-is-not-authorized-with-this-scope/
			$.ajax({
				url: 'https://api.instagram.com/v1/users/self/',
				dataType: 'jsonp',
				type: 'GET',
				data: {
					access_token: settings.instagram_token
				},
				success: function(data) {
					var followers = parseInt(data.data.counts.followed_by);
					var k = kFormatter(followers);
					if ($('.btn-social-counter--instagram .btn-social-counter__count-num').is(':empty')) {
						$('.btn-social-counter--instagram .btn-social-counter__count-num').append(k);
					}
				}
			});
			$('.btn-social-counter--instagram').attr('href','https://instagram.com/'+settings.instagram_user);
		}
		function instagram_sandbox(){
			 $.ajax({
				 url: 'https://api.instagram.com/v1/users/search?q='+settings.instagram_user_sandbox,
				 dataType: 'jsonp',
				 type: 'GET',
				 data: {
					 access_token: settings.instagram_token
				 },
				 success: function(data) {
					 $.each(data.data, function(i, item) {
						 if(settings.instagram_user_sandbox == item.username){
							 $.ajax({
								 url: "https://api.instagram.com/v1/users/" + item.id,
								 dataType: 'jsonp',
								 type: 'GET',
								 data: {
									 access_token: settings.instagram_token
								 },
								 success: function(data) {
									var followers = parseInt(data.data.counts.followed_by);
									var k = kFormatter(followers);
									$('.btn-social-counter--instagram .btn-social-counter__count-num').append(k);
								 }
							 });
						 }
					 });
				 }
			 });
			 $('.btn-social-counter--instagram').attr('href','https://instagram.com/'+settings.instagram_user_sandbox);
		 }
		function google(){
			//Google+ API
			$.ajax({
				url: 'https://www.googleapis.com/plus/v1/people/' + settings.google_plus_id,
				type: "GET",
				dataType: "json",
				data:{
					key:settings.google_plus_key
				},
				success: function (data) {
					var followers = parseInt(data.circledByCount);
					var k = kFormatter(followers);
					if ($(".btn-social-counter--gplus .btn-social-counter__count-num").is(':empty')) {
						$(".btn-social-counter--gplus .btn-social-counter__count-num").append(k);
					}
				}
			});
			$('.btn-social-counter--gplus').attr('href','https://plus.google.com/'+settings.google_plus_id);
		}
		//Function to add commas to the thousandths
		$.fn.digits = function(){
			return this.each(function(){
				$(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") );
			})
		}
		//Function to add K to thousands
		function kFormatter(num) {
			return num > 999 ? (num/1000).toFixed(1) + 'k' : num;
		}
		//Call Functions
		if(settings.facebook_user!='' && settings.facebook_token!=''){
			facebook();
		} if(settings.instagram_user!='' && settings.instagram_token!=''){
			instagram();
		} if(settings.instagram_user_sandbox!='' && settings.instagram_token!=''){
			instagram_sandbox();
		}if(settings.google_plus_id!='' && settings.google_plus_key!=''){
			google();
		}
	};
}(jQuery));
