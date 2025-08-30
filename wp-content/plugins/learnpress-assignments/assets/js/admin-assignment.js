;(function ($) {
	function _ready() {

		var max_mark = parseInt($('#_lp_mark').val());
		$('#_lp_passing_grade').on('change keydown paste input', function () {
			var pass_grade = parseInt($('#_lp_passing_grade').val());
			if (pass_grade > max_mark) {
				$('#_lp_passing_grade').val(max_mark);
			}
		});

		$('.admin_page_assignment-student .wp-list-table td.column-actions .send-mail').on('click', function (e) {
			e.preventDefault();

			var _self = $(this),
				_actions = _self.parent('.assignment-students-actions'),
				_user_id = _actions.data('user_id'),
				_assignment_id = _actions.data('assignment_id');

			if (confirm(lp_assignment_students.resend_evaluated_mail)) {
				$.ajax({
					url : ajaxurl,
					type: 'POST',
					data: {
						action       : 'lp_assignment_send_evaluated_mail',
						user_id      : _user_id,
						assignment_id: _assignment_id
					}
				}).complete(function (response) {
					if (response.status === 200) {
						var _data = LP.parseJSON(response.responseText);
						alert(_data['message']);
					}
				});
			}
		});

		$('.admin_page_assignment-student .wp-list-table td.column-actions .delete').on('click', function (e) {
			e.preventDefault();

			var _self = $(this),
				_actions = _self.parent('.assignment-students-actions'),
				_user_id = _actions.data('user_id'),
				_assignment_id = _actions.data('assignment_id');

			if (confirm(lp_assignment_students.delete_submission)) {
				$.ajax({
					url : ajaxurl,
					type: 'POST',
					data: {
						action       : 'lp_assignment_delete_submission',
						user_id      : _user_id,
						assignment_id: _assignment_id
					}
				}).complete(function (response) {
					if (response.status === 200) {
						var _data = LP.parseJSON(response.responseText);
						if (_data['status'] === 'fail') {
							alert(_data['message']);
						}
						location.reload();
					}
				});
			}
		});

		$('.admin_page_assignment-student .wp-list-table td.column-actions .reset').on('click', function (e) {
			e.preventDefault();

			var _self = $(this),
				_actions = _self.parent('.assignment-students-actions'),
				_user_item_id = _actions.data('user-item-id');

			if (confirm(lp_assignment_students.reset_result)) {
				$.ajax({
					url : ajaxurl,
					type: 'POST',
					data: {
						action      : 'lp_assignment_reset_result',
						user_item_id: _user_item_id
					}
				}).complete(function (response) {
					if (response.status === 200) {
						var _data = LP.parseJSON(response.responseText);
						if (_data['status'] === 'fail') {
							alert(_data['message']);
						}
						location.reload();
					}
				});
			}
		});
	}

	$(document).ready(_ready);
})(jQuery);