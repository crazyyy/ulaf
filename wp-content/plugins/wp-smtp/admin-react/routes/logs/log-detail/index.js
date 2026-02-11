/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useState, createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Text, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import ConfirmationDialog from '../../../components/confirmation-dialog';
import { Logo } from '../../../components/icons';
import { store as connectionsStore } from '../../../data/src/connections';
import { STORE_NAME as LogsStore } from '../../../data/src/logs/constants';
import Message from '../message';

import {
	Body,
	Empty,
	Header,
	HeaderRow,
	StyledNotice,
	StyledSurface,
} from './styles';

/**
 * Component for displaying the details of a log.
 */
function LogDetail() {
	const { selectedLog, currentPage } = useSelect(
		(select) => ({
			selectedLog: select(LogsStore).getSelectedLog(),
			currentPage: select(LogsStore).getCurrentPage(),
		}),
		[]
	);
	const connectorDisplayName = useSelect(
		(select) => {
			if (selectedLog.connection_id === 'external') {
				return __('Without Solid Mail connection', 'LION');
			}

			const connection = select(connectionsStore).getConnectionById(
				selectedLog.connection_id
			);
			return connection
				? select(connectionsStore).getConnectorDisplayName(
						connection.name
					)
				: '';
		},
		[selectedLog.connection_id]
	);

	const { deleteLog } = useDispatch(LogsStore);
	const [isDialogOpen, setIsDialogOpen] = useState(false);
	const [isDeleting, setIsDeleting] = useState(false);

	// Handle delete confirmation
	const handleDelete = () => {
		setIsDialogOpen(true);
	};

	// Confirm deletion and proceed with log deletion
	const handleConfirmDelete = async () => {
		setIsDeleting(true);
		await deleteLog([selectedLog.mail_id], currentPage);
		setIsDialogOpen(false);
		setIsDeleting(false);
	};

	// Cancel deletion
	const handleCancelDelete = () => {
		setIsDialogOpen(false);
	};

	if (selectedLog === null || selectedLog === undefined) {
		return (
			<Empty>
				<Logo />
			</Empty>
		);
	}
	return (
		<>
			{selectedLog.error !== null && selectedLog.error.length > 0 && (
				<StyledNotice text={selectedLog.error} type={'danger'} />
			)}
			<Header>
				<HeaderRow>
					{selectedLog.from_name && selectedLog.from_email && (
						<Text
							text={createInterpolateElement(
								sprintf(
									/* translators: %1$s - from name, %2$s - from email  */
									__('From: <b>%1$s <%2$s></b>', 'LION'),
									selectedLog.from_name,
									selectedLog.from_email
								),
								{ b: <strong /> }
							)}
						/>
					)}
					<Text
						weight={TextWeight.HEAVY}
						text={connectorDisplayName}
					/>
				</HeaderRow>
				<HeaderRow>
					<Text
						text={createInterpolateElement(
							sprintf(
								/* translators: %1$s - comma-separated email addresses  */
								__('To: <b>%1$s</b>', 'LION'),
								selectedLog.to.join(', ')
							),
							{ b: <strong /> }
						)}
					/>
					<Text weight={TextWeight.HEAVY}>
						{selectedLog.timestamp}
					</Text>
				</HeaderRow>
			</Header>
			<Body>
				<Text
					as="p"
					text={createInterpolateElement(
						sprintf(
							/* translators: %1$s - email subject  */
							__('Subject: <b>%1$s</b>', 'LION'),
							selectedLog.subject
						),
						{ b: <strong /> }
					)}
				/>
				<Text variant={TextVariant.DARK} as={'p'}>
					{__('Body', 'LION')}
				</Text>
				<StyledSurface>
					<Message email={selectedLog} key={selectedLog.mail_id} />
				</StyledSurface>
				<Button
					variant={'secondary'}
					onClick={handleDelete}
					icon={'trash'}
				>
					{__('Delete', 'LION')}
				</Button>
			</Body>
			{isDialogOpen && (
				<ConfirmationDialog
					onCancel={handleCancelDelete} // Cancel action
					onContinue={handleConfirmDelete} // Confirm and delete
					title={__('Confirm Deletion', 'LION')}
					body={__(
						'Are you sure you want to delete this log?',
						'LION'
					)}
					continueText={__('Delete', 'LION')}
					isBusy={isDeleting}
				/>
			)}
		</>
	);
}

export default LogDetail;
