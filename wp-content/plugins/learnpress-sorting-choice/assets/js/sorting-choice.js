;(function ($) {
    $(document).ready(function () {
        if (typeof LP === 'undefined' && typeof LearnPress !== 'undefined') {
            window.LP = LearnPress;
        }
        if (typeof LP === 'undefined') {
            return;
        }
        LP.Hook.addAction('learn_press_check_question', function (response, $view) {
            var $question = $view.model.current();
            if (!$question || $question.get('type') !== 'sorting_choice') {
                return;
            }
            var $content = $($question.get('content')).addClass('checked');
            $content.find('.learn-press-question-options').replaceWith($(response.checked).find('.learn-press-question-options'));
            $question.set({
                content: $content
            });
        });
        LP.Hook.addFilter('learn_press_question_answer_data', function (data, $form, question) {
            if (question.get('type') === 'sorting_choice') {
                data = $form.serialize();
            }
            return data;
        });

        jQuery(function ($) {
            $('.answer-options.sorting-choice:not(.checked)').each(function () {
                var $el = $(this),
                    sortingChoice = $el.data('sortingChoice');
                if (sortingChoice) {
                    return;
                }

                $el.sortable({
                    axis: 'y',
                    handle: '.sort-hand'
                });

                $el.data('sortingChoice', true);
            });
        });
    });
})(jQuery);