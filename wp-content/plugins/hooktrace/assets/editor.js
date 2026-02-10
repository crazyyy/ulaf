/**
 * Editor auto-scroll functionality for HookTrace.
 */

(function() {
	'use strict';

	// Check if data is available
	if (typeof hookTraceEditor === 'undefined') {
		return;
	}

	const { lineNumber } = hookTraceEditor;

	if (!lineNumber || lineNumber <= 0) {
		return;
	}

	// Wait for wp.themePluginEditor to be available
	if (typeof wp === 'undefined' || typeof wp.themePluginEditor === 'undefined') {
		return;
	}

	// Override initCodeEditor to add auto-scroll
	(function(originalInitCodeEditor) {
		wp.themePluginEditor.initCodeEditor = function() {
			originalInitCodeEditor.apply(this, arguments);
			
			if (this.instance && this.instance.codemirror) {
				// Set cursor to line (line numbers are 0-indexed in CodeMirror)
				this.instance.codemirror.doc.setCursor(lineNumber - 1, 0);
				// Scroll to line with animation
				this.instance.codemirror.scrollIntoView(
					{ line: lineNumber - 1, ch: 0 },
					200
				);
			}
		};
	})(wp.themePluginEditor.initCodeEditor);

})();

