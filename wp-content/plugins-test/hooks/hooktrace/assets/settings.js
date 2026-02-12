/**
 * Settings page JavaScript for HookTrace.
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		// Toggle custom protocol field visibility based on editor type
		const editorType = document.getElementById('hooktrace-editor-type');
		const customProtocol = document.getElementById('hooktrace-custom-protocol');
		
		if (editorType && customProtocol) {
			const customProtocolRow = customProtocol.closest('tr');
			
			function toggleCustomProtocol() {
				if (editorType.value === 'custom') {
					if (customProtocolRow) {
						customProtocolRow.style.display = '';
					}
					customProtocol.style.display = '';
				} else {
					if (customProtocolRow) {
						customProtocolRow.style.display = 'none';
					}
					customProtocol.style.display = 'none';
				}
			}
			
			editorType.addEventListener('change', toggleCustomProtocol);
			toggleCustomProtocol(); // Initial check
		}
	});

})();

