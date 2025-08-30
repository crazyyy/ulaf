<?php
/**
 * Template for displaying answer options of sorting choice question.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/sorting-choice/answer-options.php.
 *
 * @author   ThimPress
 * @package  LearnPress/Sorting-Choice/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

isset( $question ) || die( __( 'Invalid question', 'learnpress-sorting-choice' ) );

$course = LP_Global::course();
$user   = LP_Global::user();
$quiz   = LP_Global::course_item_quiz();
$question->setup_data( $quiz->get_id() );

if ( ! $answers = $question->get_answers() ) {
	return;
}
$checked = 'yes' === $question->show_correct_answers(); ?>

<ul id="answer-options-<?php echo $question->get_id(); ?>"
    class="answer-options sorting-choice<?php echo $checked ? ' checked' : ''; ?>">
	<?php if ( $answers ) {
		$position = 0;
		foreach ( $answers as $k => $answer ) { ?>
            <li <?php $answer->option_class(); ?>>
                <span class="sort-hand ui-sortable-handle"></span>
                <label>
                    <input type="hidden"
                           name="learn-press-question-<?php echo $question->get_id(); ?>[<?php echo $k; ?>]"
                           value="<?php echo $answer->get_value(); ?>"/>
                    <div class="option-title">
                        <div class="option-title-content">
							<?php echo $answer->get_title( 'display' ); ?>
                        </div>
                    </div>
                </label>
            </li>

			<?php if ( $checked ) { ?>
                <li class="correct-answer-label<?php echo $question->is_answer_true( $answer ) ? ' answered-correct' : ' answered-wrong'; ?>">
					<?php
					if ( ! $question->is_answer_true( $answer ) ) {
						$correct_answer = $question->get_answer_at( $position );
						printf( __( 'Correct answer: %s', 'learnpress-sorting-choice' ), $correct_answer->get_title( 'display' ) );
					} else {
						_e( 'Correct', 'learnpress-sorting-choice' );
					} ?>
                </li>
			<?php } ?>

			<?php $position ++;
		} ?>
	<?php } ?>
</ul>