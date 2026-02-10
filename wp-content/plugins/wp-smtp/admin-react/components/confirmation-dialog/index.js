/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledActions, StyledBody, StyledModel } from './styles';

/**
 * @typedef ConfirmationDialogProps
 * @type {Object}
 * @property {string}   title        - The dialog title.
 * @property {string}   body         - The dialog body.
 * @property {Function} onContinue   - The function to call when the continue button is clicked.
 * @property {Function} onCancel     - The function to call when the cancel button is clicked.
 * @property {string}   continueText - The text for the continue button.
 * @property {string}   cancelText   - The text for the cancel button.
 * @property {boolean}  isBusy       - Whether the dialog is busy.
 */

/**
 * Renders a confirmation dialog with customizable actions.
 *
 * @param {ConfirmationDialogProps} props
 * @return {JSX.Element} The confirmation dialog component.
 */
function ConfirmationDialog({
	title,
	body,
	onContinue,
	onCancel,
	continueText,
	cancelText = __('Cancel', 'LION'),
	isBusy,
}) {
	return (
		<StyledModel
			title={title}
			onRequestClose={onCancel}
			focusOnMount
			closeButtonLabel={cancelText}
		>
			<StyledBody>
				<Text as="p" variant={TextVariant.DARK} text={body} />
				<StyledActions>
					<Button
						text={cancelText}
						onClick={onCancel}
						variant="secondary"
					/>
					<Button
						text={continueText}
						onClick={onContinue}
						variant="primary"
						isBusy={isBusy}
					/>
				</StyledActions>
			</StyledBody>
		</StyledModel>
	);
}

export default ConfirmationDialog;
