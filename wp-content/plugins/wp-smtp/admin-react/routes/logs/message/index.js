/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';
import { useMemo, memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import HTMLTab from './html-tab';
import { StyledMessageTab, StyledRawMessage } from './styles';

const getTabs = (email) => {
	const tabs = [];

	if (email.content_type === 'text/html') {
		tabs.push({
			name: 'html',
			title: __('HTML', 'LION'),
			email,
		});
	}

	tabs.push({
		name: 'text',
		title: __('Plain Text', 'LION'),
		email,
	});

	return tabs;
};

function renderTab(tab) {
	if (tab.name === 'html') {
		return <HTMLTab email={tab.email} />;
	}

	return (
		<StyledMessageTab variant="info">
			<StyledRawMessage>{tab.email.message.trim()}</StyledRawMessage>
		</StyledMessageTab>
	);
}

function Message({ email }) {
	const tabs = useMemo(() => getTabs(email), [email]);

	return <TabPanel tabs={tabs}>{renderTab}</TabPanel>;
}

export default memo(Message);
