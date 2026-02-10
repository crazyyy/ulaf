/**
 * External dependencies
 */
import { useNavigate } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { Tooltip } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { info } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Button, Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import ConfirmationDialog from '../../../components/confirmation-dialog';
import { STORE_NAME } from '../../../data/src/connections/constants';
import ConnectionItem from '../connection-item';
import ConnectionsStatus from '../connections-status';

import {
	ConnectionsTable,
	ConnectionsTableRow,
	StyledFlex,
	StyledText,
} from './styles';

/**
 * Component to display a list of connections with actions to add, set active, edit, or delete connections.
 *
 * @param {Object} props             - Component properties.
 * @param {Array}  props.connections - Array of connection objects.
 * @return {JSX.Element} The ConnectionsList component.
 */
function ConnectionsList({ connections }) {
	const { updateConnection, deleteConnection } = useDispatch(STORE_NAME);
	const [deleteConnectionID, setDeleteConnectionID] = useState(false);
	const [confirmationDialogState, setConfirmDialogState] = useState('closed');
	const [isDeleting, setIsDeleting] = useState(false);

	const navigate = useNavigate();
	const onChangeActive = (id, value) => {
		updateConnection(id, { is_active: value });
	};

	const onChangeDefault = (id, value) => {
		updateConnection(id, { is_default: value });
	};

	const onDeleteButtonClick = (id) => {
		setDeleteConnectionID(id);
		setConfirmDialogState('open');
	};

	const onEditButtonClick = (id) => {
		navigate('edit/' + id);
	};

	const confirmDelete = async () => {
		setIsDeleting(true);
		await deleteConnection(deleteConnectionID);
		setConfirmDialogState('close');
		setDeleteConnectionID(false);
		setIsDeleting(false);
	};

	const cancelDelete = () => {
		setConfirmDialogState('close');
		setDeleteConnectionID(false);
	};

	return (
		<>
			<StyledFlex justify={'space-between'} wrap>
				<div>
					<ConnectionsStatus />
				</div>
				<Button
					variant={'primary'}
					icon={'plus'}
					onClick={() => {
						navigate('add');
					}}
				>
					{__('Add new Connection', 'LION')}
				</Button>
			</StyledFlex>
			<Surface variant="primary">
				<ConnectionsTable>
					<ConnectionsTableRow hideOnSmall>
						<StyledText weight={500}>
							{__('Provider', 'LION')}
						</StyledText>
						<StyledText weight={500}>
							{__('Active Connection', 'LION')}
						</StyledText>
						<Tooltip
							text={__(
								'Default connection is used when an outgoing email address does not match any active connection',
								'LION'
							)}
						>
							<StyledText
								weight={500}
								icon={info}
								iconPosition="right"
							>
								{__('Default Connection', 'LION')}
							</StyledText>
						</Tooltip>
						<StyledText weight={500}>
							{__('Actions', 'LION')}
						</StyledText>
					</ConnectionsTableRow>
					{connections.map((item, index) => (
						<ConnectionItem
							connection={item}
							key={index}
							onChangeActive={onChangeActive}
							onChangeDefault={onChangeDefault}
							onDeleteButtonClick={onDeleteButtonClick}
							onEditButtonClick={onEditButtonClick}
						/>
					))}
				</ConnectionsTable>
			</Surface>
			{confirmationDialogState === 'open' &&
				deleteConnectionID !== false && (
					<ConfirmationDialog
						onCancel={cancelDelete}
						onContinue={confirmDelete}
						title={__('Confirm Deletion', 'LION')}
						body={__(
							'Are you sure you want to delete this item?',
							'LION'
						)}
						isBusy={isDeleting}
						continueText={__('Delete', 'LION')}
					/>
				)}
		</>
	);
}

export default ConnectionsList;
