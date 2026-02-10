/**
 * WordPress dependencies
 */
import {
	TextControl,
	SelectControl,
	TextareaControl,
	RadioControl,
	ToggleControl,
} from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { FormControl, FormControlInput, FormControlLabel } from './styles';

/**
 * FormTextInput Component
 *
 * @param {Object}   props               - The component props.
 * @param {string}   props.label         - The label for the input.
 * @param {string}   props.name          - The name attribute for the input.
 * @param {string}   [props.type="text"] - The type attribute for the input.
 * @param {string}   [props.value=""]    - The value of the input.
 * @param {Function} props.onChange      - The function to call when the input value changes.
 * @param {string}   [props.error]       - The error message to display.
 * @param {string}   [props.help]        - The description/help text for the input.
 */
function FormTextInput({
	label,
	name,
	type = 'text',
	value = '',
	onChange,
	error,
	help,
}) {
	return (
		<FormControl>
			<FormControlLabel>{label}</FormControlLabel>
			<FormControlInput>
				<TextControl
					help={help}
					name={name}
					type={type}
					value={value}
					onChange={onChange}
				/>
				{error && <Text variant={'danger'}>{error}</Text>}
			</FormControlInput>
		</FormControl>
	);
}

/**
 * FormRadioGroup Component
 *
 * @param {Object}   props              - The component props.
 * @param {string}   props.label        - The label for the radio group.
 * @param {string}   props.name         - The name attribute for the radio group.
 * @param {string}   [props.value=""]   - The selected value.
 * @param {Array}    [props.options=[]] - The options for the radio group.
 * @param {Function} props.onChange     - The function to call when the selected value changes.
 * @param {string}   [props.error]      - The error message to display.
 * @param {string}   [props.help]       - The help/help text for the radio group.
 */
function FormRadioGroup({
	label,
	name,
	value = '',
	options = [],
	onChange,
	error,
	help,
}) {
	return (
		<FormControl>
			<FormControlLabel>{label}</FormControlLabel>
			<FormControlInput>
				<RadioControl
					help={help}
					selected={value}
					options={options}
					onChange={onChange}
					name={name}
				/>
				{error && <Text variant={'danger'}>{error}</Text>}
			</FormControlInput>
		</FormControl>
	);
}

/**
 * FormSelect Component
 *
 * @param {Object}   props              - The component props.
 * @param {string}   props.label        - The label for the select control.
 * @param {string}   props.name         - The name attribute for the select control.
 * @param {Array}    [props.options=[]] - The options for the select control.
 * @param {string}   [props.value=""]   - The selected value.
 * @param {Function} props.onChange     - The function to call when the selected value changes.
 * @param {string}   [props.error]      - The error message to display.
 * @param {string}   [props.help]       - The help/help text for the select control.
 */
function FormSelect({
	label,
	name,
	options = [],
	value = '',
	onChange,
	error,
	help,
}) {
	return (
		<FormControl>
			<FormControlLabel>{label}</FormControlLabel>
			<FormControlInput>
				<SelectControl
					value={value}
					options={options}
					help={help}
					onChange={onChange}
					name={name}
					__nextHasNoMarginBottom
				/>
				{error && <Text variant={'danger'}>{error}</Text>}
			</FormControlInput>
		</FormControl>
	);
}

/**
 * FormTextarea Component
 *
 * @param {Object}   props               - The component props.
 * @param {string}   props.label         - The label for the textarea control.
 * @param {string}   props.name          - The name attribute for the textarea control.
 * @param {string}   [props.type="text"] - The type attribute for the textarea control.
 * @param {string}   [props.value=""]    - The value of the textarea control.
 * @param {Function} props.onChange      - The function to call when the textarea value changes.
 * @param {string}   [props.error]       - The error message to display.
 * @param {string}   [props.help]        - The help/help text for the textarea control.
 */
function FormTextarea({
	label,
	name,
	type = 'text',
	value = '',
	onChange,
	error,
	help,
}) {
	return (
		<FormControl>
			<FormControlLabel>{label}</FormControlLabel>
			<FormControlInput>
				<TextareaControl
					help={help}
					type={type}
					name={name}
					value={value}
					onChange={onChange}
				/>
				{error && <Text variant={'danger'}>{error}</Text>}
			</FormControlInput>
		</FormControl>
	);
}

/**
 * FormToggle Component
 *
 * @param {Object}   props               - The component props.
 * @param {string}   props.label         - The label for the toggle control.
 * @param {string}   props.name          - The name attribute for the toggle control.
 * @param {boolean}  [props.value=false] - The value of the toggle control.
 * @param {Function} props.onChange      - The function to call when the toggle value changes.
 * @param {string}   [props.error]       - The error message to display.
 * @param {string}   [props.help]        - The help/help text for the toggle control.
 */
function FormToggle({ label, name, value = false, onChange, error, help }) {
	return (
		<FormControl>
			<FormControlLabel>{label}</FormControlLabel>
			<FormControlInput>
				<ToggleControl
					label={''}
					help={help}
					checked={value}
					onChange={onChange}
					name={name}
				/>
				{error && <Text variant={'danger'}>{error}</Text>}
			</FormControlInput>
		</FormControl>
	);
}

export { FormTextInput, FormRadioGroup, FormSelect, FormTextarea, FormToggle };
