jQuery(function($){
	var oldSearch = '',
		filtering = false;

	var filterActions = function(refresh) {
		if ( filtering && !refresh ) {
			return;
		}
		filtering = true;
		var searchString = $('.render-hooks-search').val();
		if ( searchString !== oldSearch || refresh ) {
			oldSearch = searchString;
			if (!searchString) {
				$('.hook').show();
			} else {
				$('.hook').hide();
				$('.hook[data-hookname*="' + searchString + '"]').show();
			}

			filterActionGroups();
		}
		filtering = false;
	};


	var filterActionGroups = function() {
		$('.render-hooks-filter:checked').each(function(){
			$('.hook-group-' + $(this).val()).hide();
		});
	};


	var filterInterval = setInterval(filterActions, 500);

	$('.render-hooks-filter').click(function(){
		filterActions(true);
	});

	$('.expand-hooks').click(function(){
		console.log('here');
		$('.hook-args').show();
	});

	$('.collapse-hooks').click(function(){
		console.log('there');
		$('.hook-args').hide();
	});

	$('.hook-title').click(function() {
		$(this).parent().find('.hook-args').toggle();
	});

	$('.hook-args').hide();
});