<?php
defined( 'ABSPATH' ) || exit;

$network_viewer_auto_refresh_interval = $settings['network_viewer_auto_refresh_interval'] ?? 10;  // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables, not globals
?>
<div class="col-xs-12 p-t-20">
	<div class="network-viewer-container" data-parent="network-viewer-enabled">
		<p><strong><?php esc_html_e( 'Network viewer', 'adminease' ); ?></strong></p>
		
		<div class="actions m-b-10">
			<button type="button" class="button button-secondary button-small" id="refresh-network-viewer"><?php esc_html_e( 'Refresh', 'adminease' ); ?></button>
			
			<button type="button" class="button button-secondary button-small" id="clear-network-viewer"><?php esc_html_e( 'Clear', 'adminease' ); ?></button>
			
			<button type="button" class="button button-secondary button-small auto-refresh-toggle" data-action="network-viewer" data-interval="<?php echo esc_attr( $network_viewer_auto_refresh_interval ); ?>">
				<input type="checkbox" id="auto-refresh-toggle-network-viewer">
				<?php /* translators: %s: number of seconds for the auto-refresh interval */ ?>
				<label for="auto-refresh-toggle-network-viewer"><?php echo sprintf( esc_html__( 'Auto-refresh every %s seconds', 'adminease' ), esc_html( $network_viewer_auto_refresh_interval ) ); ?></label>
			</button>
		</div>
		
		<!-- Filters -->
		<div class="network-filters m-t-10">
			<div class="row">
				<div class="col-lg-8 col-xs-12 col">
					<div class="network-filter-group">
						<label for="filter-method"><?php esc_html_e( 'Method:', 'adminease' ); ?></label>
						<select id="filter-method" class="filter-select">
							<option value=""><?php esc_html_e( 'All', 'adminease' ); ?></option>
							<option value="GET">GET</option>
							<option value="POST">POST</option>
							<option value="PUT">PUT</option>
							<option value="DELETE">DELETE</option>
							<option value="PATCH">PATCH</option>
							<option value="HEAD">HEAD</option>
						</select>
					</div>
					
					<div class="network-filter-group">
						<label for="filter-ip"><?php esc_html_e( 'Search IP:', 'adminease' ); ?></label>
						<input type="text" id="filter-ip" class="filter-input" placeholder="<?php esc_attr_e( 'Enter IP address', 'adminease' ); ?>">
					</div>
					
					<div class="network-filter-group">
						<label for="filter-per-page"><?php esc_html_e( 'Per page:', 'adminease' ); ?></label>
						<select id="filter-per-page" class="filter-select">
							<option value="25">25</option>
							<option value="50" selected>50</option>
							<option value="100">100</option>
							<option value="200">200</option>
							<option value="400">400</option>
							<option value="600">600</option>
							<option value="800">800</option>
							<option value="1000">1000</option>
						</select>
					</div>
					
					<button id="apply-filters" class="button button-secondary button-small">
						<?php esc_html_e( 'Apply Filters', 'adminease' ); ?>
					</button>
				</div>
				
				<div class="col-lg-4 col-xs-12 col end-xs">
					<?php do_action( 'adminease_network_viewer_network_viewer_filters' ); ?>
				</div>
			</div>
		</div>
		
		<!-- Table -->
		<div class="adminease-table-wrapper network-viewer-table-wrapper m-t-10">
			<table id="network-viewer-table" class="adminease-table network-viewer-table">
				<thead>
				<tr>
					<th class="col-time"><?php esc_html_e( 'Time', 'adminease' ); ?></th>
					<th class="col-method"><?php esc_html_e( 'Method', 'adminease' ); ?></th>
					<th class="col-status"><?php esc_html_e( 'Status', 'adminease' ); ?></th>
					<th class="col-type"><?php esc_html_e( 'Type', 'adminease' ); ?></th>
					<th class="col-location"><?php esc_html_e( 'Location', 'adminease' ); ?></th>
					<th class="col-ip"><?php esc_html_e( 'IP', 'adminease' ); ?></th>
					<th class="col-path"><?php esc_html_e( 'Path', 'adminease' ); ?></th>
					<th class="col-visitor"><?php esc_html_e( 'Visitor', 'adminease' ); ?></th>
					<th class="col-view"><?php esc_html_e( 'View', 'adminease' ); ?></th>
				</tr>
				</thead>
				<tbody id="network-viewer-table-body">
				<!-- Data will be populated via AJAX -->
				</tbody>
			</table>
		</div>
		
		<!-- Connection count (muted text below table) -->
		<div id="network-connection-count" class="network-connection-count m-t-10"></div>
		
		<!-- Empty state -->
		<div id="network-empty-state" class="network-empty-state" style="display: none;">
			<span class="dashicons dashicons-networking"></span>
			<p><?php esc_html_e( 'No network connections logged yet.', 'adminease' ); ?></p>
			<p class="description"><?php esc_html_e( 'Enable the Network Viewer feature in the Debug settings and connections will appear here.', 'adminease' ); ?></p>
		</div>
		
		<!-- Pagination -->
		<div id="network-pagination" class="network-pagination" style="display: none;">
			<button id="prev-page" class="button button-small" disabled>
				<span class="dashicons dashicons-arrow-left-alt2"></span>
				<?php esc_html_e( 'Previous', 'adminease' ); ?>
			</button>
			
			<span id="pagination-info" class="pagination-info"></span>
			
			<button id="next-page" class="button button-small">
				<?php esc_html_e( 'Next', 'adminease' ); ?>
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</button>
		</div>
		
		<?php
		if( !defined( 'ADMINEASE_PRO_VERSION' ) ) {
			?>
			<div class="network-viewer-upgrade-notice">
				<p>
					<?php esc_html_e( 'Upgrade to Pro to view detailed insights for each connection including request burst rates, error counts, recent IP activity, and export your entire network log to CSV.', 'adminease' ); ?>
					<a href="https://precisionwp.net/product/adminease/" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Upgrade to AdminEasePro', 'adminease' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
		?>
		
		<!-- Status messages -->
		<div id="network-status" class="network-status m-t-10"></div>
		
		<!-- Modal for connection details -->
		<div id="connection-details-modal" class="adminease-modal" style="display:none;">
			<div class="adminease-modal-overlay"></div>
			<div class="adminease-modal-content">
				<div class="adminease-modal-header">
					<h2><?php esc_html_e( 'Connection Details', 'adminease' ); ?></h2>
					<button class="adminease-modal-close" type="button">&times;</button>
				</div>
				<div class="adminease-modal-body">
					<!-- Content will be populated by JavaScript -->
				</div>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'adminease_network_viewer_log_after' ); ?>