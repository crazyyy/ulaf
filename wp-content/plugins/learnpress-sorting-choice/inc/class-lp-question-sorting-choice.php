<?php
/**
 * Question sorting choice question class.
 *
 * @author   ThimPress
 * @package  LearnPress/Sorting-Choice/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Question_Sorting_Choice' ) ) {

	/**
	 * Class LP_Question_Sorting_Choice
	 */
	class LP_Question_Sorting_Choice extends LP_Question {

		/**
		 * @var string
		 */
		protected $_question_type = 'sorting_choice';

		/**
		 * LP_Question_Sorting_Choice constructor.
		 *
		 * @param null $the_question
		 * @param null $args
		 *
		 * @throws Exception
		 */
		public function __construct( $the_question = null, $args = null ) {
			parent::__construct( $the_question, $args );

			if ( ! has_filter( 'learn-press/question/answer-option/classes' ) ) {
				add_filter( 'learn-press/question/answer-option/classes', array( $this, 'option_classes' ), 10, 2 );
			}
		}

		/**
		 * @param array                     $classes
		 * @param LP_Question_Answer_Option $answer
		 *
		 * @return mixed
		 */
		public function option_classes( $classes, $answer ) {
			if ( $answer->get_question_id() != $this->get_id() ) {
				return $classes;
			}
			$type = $answer->get_question()->get_type();
			if( $type !== 'sorting_choice' ) {
				return $classes;
			}

			foreach ( array( 'answer-correct', 'answered-correct', 'answered-wrong' ) as $class ) {
				if ( false !== ( $pos = array_search( $class, $classes ) ) ) {
					array_splice( $classes, $pos, 1 );
				}
			}

			if ( $answered = $this->get_answered() ) {
				if ( $this->show_correct_answers() === 'yes' ) {
					if ( $this->is_answer_true( $answer ) ) {
						$classes[] = 'answered-correct';
					} else {
						$classes[] = 'answered-wrong';
					}
				}
			}

			return $classes;
		}

		/**
		 * Get answer at position
		 *
		 * @since 3.0.0
		 *
		 * @param int $at
		 *
		 * @return LP_Question_Answer_Option|mixed
		 */
		public function get_answer_at( $at ) {
			return parent::get_answers()->get_answer_at( $at );
		}

		/**
		 * Check if an option is in correct position
		 *
		 * @since 3.0.0
		 *
		 * @param LP_Question_Answer_Option $answer
		 *
		 * @return bool
		 */
		public function is_answer_true( $answer ) {
			$is_true = false;
			if ( $answered = $this->get_answered() ) {
				if ( $answer_ids = parent::get_answers()->get_ids() ) {
					$answer_index = array_search( $answer->get_id(), $answer_ids );

					$answered_ids = array();

					foreach ( $answered as $id => $value ) {
						$answered_ids[] = $id;
					}

					if ( ! empty( $answered_ids[ $answer_index ] ) && $answered_ids[ $answer_index ] == $answer->get_id() ) {
						$is_true = true;
					}
				}
			}

			return $is_true;
		}

		/**
		 * Get option answers for question
		 *
		 * @since 3.0.0
		 *
		 * @param string $field
		 * @param string $exclude
		 *
		 * @return array|LP_Question_Answers
		 */
		public function get_answers( $field = null, $exclude = null ) {
			if ( $answers = parent::get_answers() ) {
				if ( ! $_answer_keys = $this->get_answered() ) {
					$_answer_keys = array();
					foreach ( $answers as $id => $value ) {
						$_answer_keys[] = $id;
					}
					$_random_answer_keys = $_answer_keys;

					// Prevent loop
					$i = 0;

					do {
						shuffle( $_random_answer_keys );
					} while ( ( $i ++ < 10 ) && array_intersect_assoc( $_random_answer_keys, $_answer_keys ) );

					$_answer_keys = $_random_answer_keys;
				} else {
					$_answer_keys = array_keys( $_answer_keys );
				}

				$_answers = array();
				foreach ( $_answer_keys as $id ) {
					if ( isset( $answers[ $id ] ) ) {
						$_answers[ $id ] = $answers[ $id ];
					}
				}

				// Overwrite options
				$answers->clear_answer_options();
				$answers->set_answer_option( $_answers );
			}

			return $answers;
		}

		/**
		 * Get default question list answers.
		 *
		 * @return array|bool
		 */
		public function get_default_answers() {
			$answers = array(
				array(
					'is_true' => 'yes',
					'value'   => '',
					'text'    => __( 'First option', 'learnpress-sorting-choice' )
				),
				array(
					'is_true' => 'yes',
					'value'   => '',
					'text'    => __( 'Second option', 'learnpress-sorting-choice' )
				),
				array(
					'is_true' => 'yes',
					'value'   => '',
					'text'    => __( 'Third option', 'learnpress-sorting-choice' )
				)
			);

			return $answers;
		}

		/**
		 * Prints the question in frontend user.
		 *
		 * @param bool $args
		 */
		public function render( $args = false ) {
			learn_press_sorting_choice_get_template( 'answer-options.php', array( 'question' => $this ) );
		}

		/**
		 * Check user answer.
		 *
		 * @param null $user_answer
		 *
		 * @return array
		 */
		public function check( $user_answer = null ) {
			if ( $return = $this->_get_checked( $user_answer ) ) {
				return $return;
			}

			$return = parent::check();

			if ( $answers = parent::get_answers() ) {
				$position          = 0;
				$return['correct'] = true;
				foreach ( $answers as $answer ) {
					if ( $ans = $this->get_answer_at( $position ) ) {
						if ( $ans && $ans->get_id() != $answer->get_id() ) {
							$return['correct'] = false;
							break;
						}
					}
					$position ++;
				}
			}

			if ( $return['correct'] ) {
				$return['mark'] = $this->get_mark();
			} else {
				$return['mark'] = 0;
			}

			return $return;
		}
	}
}