/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	CardBody,
	CardHeader,
	TextControl,
	BaseControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { ExportCard } from '../styles';

function ExportSettings() {
	const [dateFrom, setDateFrom] = useState(getFirstDateOfMonth);
	const [dateTo, setDateTo] = useState(getLastDateOfMonth);
	const [downloadLink, setDownloadLink] = useState(null);
	const [isGenerating, setIsGenerating] = useState(false);

	function onDateFromChange(newDate) {
		setDateFrom(newDate);
	}

	function onDateToChange(newDate) {
		setDateTo(newDate);
	}

	async function handleExportCSV() {
		if (!dateFrom || !dateTo) {
			// eslint-disable-next-line no-alert
			window.alert(
				__('Please select both Date From and Date To.', 'LION')
			);
			return;
		}

		setIsGenerating(true);

		try {
			const response = await apiFetch({
				path: `/solidwp-mail/v1/logs/export-csv?date_from=${dateFrom}&date_to=${dateTo}`,
				method: 'GET',
				parse: false, // We want to handle the raw response ourselves
			});

			const blob = await response.blob();
			const url = window.URL.createObjectURL(blob);

			// Set the download link state
			setDownloadLink(url);
		} catch (error) {
			// eslint-disable-next-line no-console
			console.error('Failed to export CSV:', error);
			// eslint-disable-next-line no-alert
			window.alert(__('Failed to export CSV. Please try again.', 'LION'));
		} finally {
			setIsGenerating(false); // Reset state once the CSV is ready or an error occurs
		}
	}

	function handleDownloadLinkClick() {
		// Cleanup the object URL after the download
		setTimeout(() => {
			window.URL.revokeObjectURL(downloadLink);
			setDownloadLink(null);
		}, 1000);
	}

	return (
		<ExportCard>
			<CardHeader>
				<h2>{__('Export Email Logs', 'LION')}</h2>
			</CardHeader>
			<CardBody>
				<TextControl
					type={'date'}
					label={__('Date From', 'LION')}
					value={dateFrom}
					onChange={onDateFromChange}
				/>
				<TextControl
					type={'date'}
					label={__('Date To', 'LION')}
					value={dateTo}
					onChange={onDateToChange}
				/>
				<BaseControl>
					<Button
						variant={'primary'}
						onClick={handleExportCSV}
						disabled={isGenerating}
					>
						{__('Export CSV', 'LION')}
					</Button>
				</BaseControl>
				{isGenerating && (
					<BaseControl>
						<p>{__('Generating your CSV…', 'LION')}</p>
					</BaseControl>
				)}
				{downloadLink && !isGenerating && (
					<BaseControl>
						<a
							href={downloadLink}
							download={`logs-${dateFrom}-to-${dateTo}.csv`}
							onClick={handleDownloadLinkClick}
						>
							{__('Download CSV', 'LION')}
						</a>
					</BaseControl>
				)}
			</CardBody>
		</ExportCard>
	);
}

function getFirstDateOfMonth() {
	const date = new Date();
	date.setDate(1);
	return date.toISOString().split('T')[0];
}

function getLastDateOfMonth() {
	const date = new Date();
	date.setMonth(date.getMonth() + 1); // Move to the next month
	date.setDate(0); // Set to the last day of the previous month
	return date.toISOString().split('T')[0];
}

export default ExportSettings;
