jQuery(function ($) {
    let editor, currentFile = null, currentDir = AFE_Ajax.base, { __ } = wp.i18n;
    let expandedFolders = new Set(); // Track expanded folder paths

    // CodeMirror init
    const settings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
    settings.codemirror = _.extend({}, settings.codemirror, {
        lineNumbers: true,
        styleActiveLine: true,
        mode: "php",
        indentUnit: 4,
        tabSize: 4,
        foldGutter: true,
        gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
        autoCloseTags: true,
        theme: 'cobalt',
        highlightSelectionMatches: { showToken: /\w/, annotateScrollbar: true },
        matchTags: { bothTags: true },
    });
    editor = wp.codeEditor.initialize($('#wfe-editor'), settings).codemirror;

    // Function to get all currently expanded folder paths
    function getExpandedFolders() {
        const expanded = [];
        $('#wfe-tree .wfe-item.dir').each(function() {
            const $li = $(this);
            const $sub = $li.children('ul');
            if ($sub.length && $sub.is(':visible')) {
                expanded.push($li.data('path'));
            }
        });
        return expanded;
    }

    // Smart refresh that preserves folder states
    function smartRefresh() {
        const expanded = getExpandedFolders();
        $('#wfe-tree').empty();
        loadDir(AFE_Ajax.base, $('#wfe-tree'), function() {
            // After tree is loaded, restore expanded folders in hierarchical order
            if (expanded.length > 0) {
                // Sort paths by depth (number of separators) to expand parent folders first
                expanded.sort((a, b) => {
                    const aDepth = (a.match(/\//g) || []).length;
                    const bDepth = (b.match(/\//g) || []).length;
                    return aDepth - bDepth;
                });
                
                // Restore folders sequentially to ensure parent folders are loaded before children
                restoreFoldersSequentially(expanded, 0);
            }
        });
    }

    // Recursively restore folders in order
    function restoreFoldersSequentially(paths, index) {
        if (index >= paths.length) return;
        
        const path = paths[index];
        const $folder = $('#wfe-tree .wfe-item.dir').filter(function() {
            return $(this).data('path') === path;
        });
        
        if ($folder.length) {
            const $sub = $folder.children('ul');
            if ($sub.length) {
                // Folder already has content, just expand it
                $sub.show();
                $folder.children('.toggle').text('▼');
                expandedFolders.add(path);
                // Continue with next folder
                restoreFoldersSequentially(paths, index + 1);
            } else {
                // Need to load folder content first
                loadDir(path, $folder, function() {
                    $folder.children('ul').show();
                    $folder.children('.toggle').text('▼');
                    expandedFolders.add(path);
                    // Continue with next folder after this one loads
                    restoreFoldersSequentially(paths, index + 1);
                });
            }
        } else {
            // Folder not found, skip to next
            restoreFoldersSequentially(paths, index + 1);
        }
    }

    // Function to expand folders by path
    function expandFolderByPath(path) {
        const $folder = $('#wfe-tree .wfe-item.dir').filter(function() {
            return $(this).data('path') === path;
        });
        
        if ($folder.length) {
            const $sub = $folder.children('ul');
            if ($sub.length && !$sub.is(':visible')) {
                $sub.show();
                $folder.children('.toggle').text('▼');
            }
        }
    }

    // Load directory
    function loadDir(dir, container, callback) {
        $.post(AFE_Ajax.ajax_url, { action: 'advan_file_editor_list_dir', dir, _ajax_nonce: AFE_Ajax.nonce }, (res) => {
            if (res.success) {
                const ul = $('<ul></ul>');
                res.data.forEach(item => {
                    const li = $('<li></li>').addClass('wfe-item').addClass(item.type)
                        .text(item.type === 'dir' ? '📁 ' + item.name : '📄 ' + item.name)
                        .data('path', item.path);

                    if (item.type === 'dir') li.prepend('<span class="toggle">▶</span>');
                    ul.append(li);
                });
                container.append(ul);                if (callback) callback();            }
        });
    }

    loadDir(AFE_Ajax.base, $('#wfe-tree'));

    // Folder toggle
    $('#wfe-tree').on('click', '.wfe-item.dir', function (e) {
        e.stopPropagation();
        const li = $(this);
        const path = li.data('path');
        currentDir = path;
        const sub = li.children('ul');
        if (sub.length) {
            sub.toggle();
            const isVisible = sub.is(':visible');
            li.children('.toggle').text(isVisible ? '▼' : '▶');
            // Track expanded state
            if (isVisible) {
                expandedFolders.add(path);
            } else {
                expandedFolders.delete(path);
            }
        } else {
            li.children('.toggle').text('▼');
            loadDir(path, li);
            expandedFolders.add(path);
        }
    });

    // File open
    $('#wfe-tree').on('click', '.wfe-item.file', function (e) {
        e.stopPropagation();
        const $li = $(this);
        const path = $li.data('path');
        currentDir = path.substring(0, path.lastIndexOf('/'));
        $.post(AFE_Ajax.ajax_url, { action: 'advan_file_editor_get_file', file: path, _ajax_nonce: AFE_Ajax.nonce }, (res) => {
            if (res.success) {
                editor.setValue(res.data.content);
                currentFile = path;
                $('#wfe-filename').text(path);
                $('#wfe-diff').hide();
                $('#wfe-tree .wfe-item.file.active').removeClass('active');
                $li.addClass('active');
                listBackups();
            } else alert(res.data);
        });
    });

    // Search
    $('#wfe-search').on('input', function () {
        const val = $(this).val().toLowerCase();
        $('#wfe-tree li').each(function () {
            const match = $(this).text().toLowerCase().includes(val);
            $(this).toggle(match);
        });
    });

    // Diff preview
    $('#wfe-show-diff').on('click', function () {
        if (!currentFile) return alert('No file open.');
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_diff',
            file: currentFile,
            new_content: editor.getValue(),
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            if (res.success) {
                $('#wfe-diff').html(res.data.diff).css('display', 'flex');
            }
            else alert(res.data);
        });
    });

    // Save file
    $('#wfe-save').on('click', function () {
        if (!currentFile) return alert('No file open.');
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_save_file',
            file: currentFile,
            content: editor.getValue(),
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => { alert(res.success ? __('✅ Saved.', '0-day-analytics') : '❌ ' + res.data); listBackups(); });
    });

    // Create file/folder
    $('#wfe-new-file, #wfe-new-folder').on('click', function () {
        const type = $(this).is('#wfe-new-folder') ? 'folder' : 'file';
        const name = prompt(__('Enter new ' + type + ' name:', '0-day-analytics'));
        if (!name) return;
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_create',
            dir: currentDir,
            name,
            type,
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            alert(res.success ? __('✅ Created.', '0-day-analytics') : '❌ ' + res.data);
            smartRefresh();
        });
    });

    // Delete
    $('#wfe-delete').on('click', function () {
        if (!currentFile) return alert('No file selected.');
        if (!confirm('Are you sure you want to delete this file/folder?\n' + currentFile)) return;
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_delete',
            path: currentFile,
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            alert(res.success ? __('🗑️ Deleted.', '0-day-analytics') : '❌ ' + res.data);
            smartRefresh();
            $('#wfe-filename').text(__('No file selected', '0-day-analytics'));
            editor.setValue('');
            $('#wfe-tree .wfe-item.file.active').removeClass('active');
        });
    });

    // --- Undo Delete ---
    $('#wfe-undo').on('click', function () {
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_restore',
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            alert(res.success ? '♻ ' + res.data.msg : '❌ ' + res.data);
            smartRefresh();
        });
    });
    // --- Empty Trash ---
    $('#wfe-empty-trash').on('click', function () {
        if (!confirm(`${__('⚠️ This will permanently delete all items in trash. Continue?', '0-day-analytics')}`)) return;
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_empty_trash',
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            alert(res.success ? '🧹 ' + res.data : '❌ ' + res.data);
        });
    });
    // --- List Backups ---
    $('#wfe-list-backups').on('click', function () {
        listBackups();
    });

    // --- Restore Backup ---
    $('#wfe-backups').on('click', '.restore-backup', function () {
        const backup = $(this).closest('.wfe-backup-item').data('backup');
        const file = currentFile;
        if (!confirm(`${__('Restore', '0-day-analytics')} ${backup}? ${__('This will overwrite the current file.', '0-day-analytics')}`)) return;
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_restore_backup',
            file: file,
            backup: backup,
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            alert(res.success ? '✅ ' + res.data : '❌ ' + res.data);
            if (res.success && file) {
                // Reload the file content into the editor to reflect restored version
                $.post(AFE_Ajax.ajax_url, {
                    action: 'advan_file_editor_get_file',
                    file: file,
                    _ajax_nonce: AFE_Ajax.nonce
                }, (res2) => {
                    if (res2.success) {
                        editor.setValue(res2.data.content);
                        $('#wfe-diff').hide();
                    }
                });
                // Refresh backups list
                listBackups();
            }
        });
    });

    // --- Delete Backup ---
    $('#wfe-backups').on('click', '.delete-backup', function () {
        const backup = $(this).closest('.wfe-backup-item').data('backup');
        const file = currentFile;
        if (!confirm(`${__('Delete', '0-day-analytics')} ${backup}? ${__('This action cannot be undone.', '0-day-analytics')}`)) return;
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_delete_backup',
            file: file,
            backup: backup,
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            alert(res.success ? '✅ ' + res.data : '❌ ' + res.data);
            listBackups();
        });
    });

    // --- Compare Backup ---
    $('#wfe-backups').on('click', '.compare-backup', function () {
        const backup = $(this).closest('.wfe-backup-item').data('backup');
        const file = currentFile;
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_compare_backup',
            file: file,
            backup: backup,
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            if (res.success) {
                $('#wfe-diff').html(res.data.diff).css('display', 'flex');;
                window.scrollTo({ top: $('#wfe-diff').offset().top - 80, behavior: 'smooth' });
            } else alert('❌ ' + res.data);
        });
    });

    function listBackups() {
        const file = currentFile;
        if (!file) { alert('No file selected'); return; }

        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_list_backups',
            file: file,
            _ajax_nonce: AFE_Ajax.nonce
        }, (res) => {
            if (res.success) {
                const list = res.data.map(b => `
                    <div class="wfe-backup-item" data-backup="${b}">
                        🕒 ${b}
                        <div>
                            <a href="${AFE_Ajax.ajax_url}?action=advan_file_editor_download_backup&_ajax_nonce=${AFE_Ajax.nonce}&file=${encodeURIComponent(file)}&backup=${encodeURIComponent(b)}" class="button" title="${__('Download Backup', '0-day-analytics')}">⬇️</a>
                            <button class="button compare-backup" title="${__('Compare Backup', '0-day-analytics')}">🔍</button>
                            <button class="button restore-backup" title="${__('Restore Backup', '0-day-analytics')}">♻</button>
                            <button class="button delete-backup" title="${__('Delete Backup', '0-day-analytics')}">🗑️</button>
                        </div>
                    </div>`).join('');
                $('#wfe-backups').html(list || '<em>' + __('No backups found', '0-day-analytics') + '</em>');
            } else alert(res.data);
        });
    }

    // --- Resizable Sidebar ---
    const $sidebar = $('.wfe-sidebar');
    const $resizer = $('.wfe-resizer');
    const $container = $('.wfe-container');
    const isReverse = $container.css('flex-direction') === 'row-reverse';
    const savedWidth = localStorage.getItem('wfeSidebarWidth');
    if (savedWidth && parseInt(savedWidth, 10) > 0) {
        $sidebar.css('width', parseInt(savedWidth, 10) + 'px');
    }

    // --- Collapsible Sidebar Toggle (persisted) ---
    const $sidebarToggle = $('.wfe-sidebar-toggle');
    const collapsedKey = 'wfeSidebarCollapsed';
    const isCollapsed = localStorage.getItem(collapsedKey) === '1';
    function applyCollapsedState(collapsed) {
        $sidebar.attr('data-collapsed', collapsed ? 'true' : 'false');
        $sidebarToggle.attr('aria-expanded', collapsed ? 'false' : 'true');
        if (collapsed) {
            // store current width before collapsing
            const cur = parseInt($sidebar.width(), 10) || 0;
            if (cur > 72) localStorage.setItem('wfeSidebarWidth', cur);
            $sidebar.css('width', '72px');
            $sidebarToggle.text(__('Expand sidebar','0-day-analytics'));
        } else {
            // restore width
            const w = localStorage.getItem('wfeSidebarWidth');
            if (w && parseInt(w,10) > 0) $sidebar.css('width', parseInt(w,10)+'px');
            $sidebarToggle.text(__('Collapse sidebar','0-day-analytics'));
        }
    }

    if ($sidebarToggle.length) {
        applyCollapsedState(isCollapsed);
        $sidebarToggle.on('click', function () {
            const cur = $sidebar.attr('data-collapsed') === 'true';
            const next = !cur;
            applyCollapsedState(next);
            localStorage.setItem(collapsedKey, next ? '1' : '0');
        });
    }

    let startX = 0, startWidth = 0, dragging = false;

    function onMouseMove(e) {
        if (!dragging) return;
        const dx = e.pageX - startX;
        const adjust = isReverse ? -dx : dx; // row-reverse flips direction
        let newWidth = startWidth + adjust;
        const min = 160;
        const max = 720;
        if (newWidth < min) newWidth = min;
        if (newWidth > max) newWidth = max;
        $sidebar.css('width', newWidth + 'px');
    }

    function stopDrag() {
        if (!dragging) return;
        dragging = false;
        $('body').removeClass('wfe-resizing');
        $(document).off('mousemove.wfeResize mouseup.wfeResize');
        const finalWidth = parseInt($sidebar.width(), 10);
        if (finalWidth) {
            localStorage.setItem('wfeSidebarWidth', finalWidth);
        }
    }

    $resizer.on('mousedown', function (e) {
        e.preventDefault();
        startX = e.pageX;
        startWidth = parseInt($sidebar.width(), 10) || 280;
        dragging = true;
        $('body').addClass('wfe-resizing');
        $(document).on('mousemove.wfeResize', onMouseMove).on('mouseup.wfeResize', stopDrag);
    });

    // Keyboard accessibility: left/right arrows
    $resizer.on('keydown', function(e){
        const key = e.key;
        let width = parseInt($sidebar.width(), 10) || 280;
        const step = (e.shiftKey ? 40 : 20);
        if (key === 'ArrowLeft' || key === 'ArrowRight') {
            const dirFactor = (key === 'ArrowRight' ? 1 : -1) * (isReverse ? -1 : 1);
            width += step * dirFactor;
            if (width < 160) width = 160;
            if (width > 720) width = 720;
            $sidebar.css('width', width + 'px');
            localStorage.setItem('wfeSidebarWidth', width);
            e.preventDefault();
        }
    });

    // --- File Context Menu (Download) ---
    let $ctxMenu = null;
    function hideContextMenu(){
        if($ctxMenu){
            $ctxMenu.remove();
            $ctxMenu = null;
        }
        // Remove temporary listeners
        $(document).off('.wfeCtx');
        $(window).off('.wfeCtx');
    }
    function showContextMenu(e, filePath){
        hideContextMenu();
        $ctxMenu = $('<div class="wfe-context-menu" role="menu"></div>');
        // Download
        const $downloadBtn = $('<button type="button" role="menuitem">⬇️ '+__('Download','0-day-analytics')+'</button>');
        $downloadBtn.on('click', function(){
            const url = `${AFE_Ajax.ajax_url}?action=advan_file_editor_download_file&_ajax_nonce=${AFE_Ajax.nonce}&file=${encodeURIComponent(filePath)}`;
            hideContextMenu();
            window.location.href = url;
        });
        // Rename
        const $renameBtn = $('<button type="button" role="menuitem">✏️ '+__('Rename','0-day-analytics')+'</button>');
        $renameBtn.on('click', function(){
            const currentBase = filePath.substring(filePath.lastIndexOf('/'));
            const newName = prompt(__('Enter new name:','0-day-analytics'), currentBase.replace('/',''));
            if(!newName){ return; }
            $.post(AFE_Ajax.ajax_url, { action:'advan_file_editor_rename', file:filePath, new_name:newName, _ajax_nonce: AFE_Ajax.nonce }, function(res){
                alert(res.success ? '✅ '+__('Renamed','0-day-analytics') : '❌ '+res.data);
                if(res.success){
                    if(currentFile === filePath){
                        currentFile = res.data.new_path;
                        $('#wfe-filename').text(res.data.new_path);
                    }
                    smartRefresh();
                }
            });
            hideContextMenu();
        });
        // Copy Path
        const $copyPathBtn = $('<button type="button" role="menuitem">📋 '+__('Copy Path','0-day-analytics')+'</button>');
        $copyPathBtn.on('click', function(){
            navigator.clipboard.writeText(filePath).then(()=>{
                alert('📋 '+__('Path copied','0-day-analytics'));
            }).catch(()=>{ alert('❌ '+__('Unable to copy path','0-day-analytics')); });
            hideContextMenu();
        });
        // Delete
        const $deleteBtn = $('<button type="button" role="menuitem">🗑️ '+__('Delete','0-day-analytics')+'</button>');
        $deleteBtn.on('click', function(){
            if(!confirm(__('Delete file?','0-day-analytics')+'\n'+filePath)) { return; }
            $.post(AFE_Ajax.ajax_url, { action:'advan_file_editor_delete', path:filePath, _ajax_nonce:AFE_Ajax.nonce }, function(res){
                alert(res.success ? '🗑️ '+__('Deleted','0-day-analytics') : '❌ '+res.data);
                if(res.success){
                    if(currentFile === filePath){
                        currentFile = null;
                        editor.setValue('');
                        $('#wfe-filename').text(__('No file selected','0-day-analytics'));
                    }
                    smartRefresh();
                }
            });
            hideContextMenu();
        });
        // Duplicate
        const $duplicateBtn = $('<button type="button" role="menuitem">🧬 '+__('Duplicate','0-day-analytics')+'</button>');
        $duplicateBtn.on('click', function(){
            $.post(AFE_Ajax.ajax_url, { action:'advan_file_editor_duplicate', file:filePath, _ajax_nonce:AFE_Ajax.nonce }, function(res){
                alert(res.success ? '🧬 '+__('Duplicated','0-day-analytics')+': '+res.data.new_name : '❌ '+res.data);
                if(res.success){
                    smartRefresh();
                }
            });
            hideContextMenu();
        });
        
        // Check if file is a ZIP archive
        const isZipFile = filePath.toLowerCase().endsWith('.zip');
        
        // Extract Archive (only for ZIP files)
        let $extractBtn = null;
        if (isZipFile) {
            $extractBtn = $('<button type="button" role="menuitem">📂 '+__('Extract Archive','0-day-analytics')+'</button>');
            $extractBtn.on('click', function(){
                hideContextMenu();
                extractArchive(filePath);
            });
        }
        
        // Create Archive (only for non-ZIP files)
        let $archiveBtn = null;
        if (!isZipFile) {
            $archiveBtn = $('<button type="button" role="menuitem">📦 '+__('Create Archive','0-day-analytics')+'</button>');
            $archiveBtn.on('click', function(){
                hideContextMenu();
                createArchive(filePath);
            });
        }
        
        // Build menu items array
        const menuItems = [$downloadBtn, $renameBtn, $duplicateBtn];
        if ($extractBtn) menuItems.push($extractBtn);
        if ($archiveBtn) menuItems.push($archiveBtn);
        menuItems.push($copyPathBtn, $deleteBtn);
        
        $ctxMenu.append(...menuItems);
        $('body').append($ctxMenu);
        const x = e.pageX;
        const y = e.pageY;
        $ctxMenu.css({ top: y + 'px', left: x + 'px' });
        // Delay binding to avoid immediate self-close from originating event
        setTimeout(function(){
            $(document).on('mousedown.wfeCtx', function(ev){
                if(!$ctxMenu) return;
                if(!$(ev.target).closest('.wfe-context-menu').length){ hideContextMenu(); }
            });
            $(document).on('contextmenu.wfeCtx', function(ev){
                if(!$ctxMenu) return;
                if(!$(ev.target).closest('.wfe-context-menu').length){ hideContextMenu(); }
            });
            $(document).on('keydown.wfeCtx', function(ev){ if(ev.key === 'Escape'){ hideContextMenu(); } });
            $(window).on('scroll.wfeCtx resize.wfeCtx', hideContextMenu);
        },0);
        // Focus first item for accessibility
        $downloadBtn.focus();
    }
    $('#wfe-tree').on('contextmenu', '.wfe-item.file', function(e){
        e.preventDefault();
        e.stopPropagation();
        const path = $(this).data('path');
        showContextMenu(e, path);
    });

    // Context menu for directories
    $('#wfe-tree').on('contextmenu', '.wfe-item.dir', function(e){
        e.preventDefault();
        e.stopPropagation();
        const path = $(this).data('path');
        showDirectoryContextMenu(e, path);
    });

    // Directory-specific context menu
    function showDirectoryContextMenu(e, dirPath){
        hideContextMenu();
        $ctxMenu = $('<div class="wfe-context-menu" role="menu"></div>');
        
        // Rename
        const $renameBtn = $('<button type="button" role="menuitem">✏️ '+__('Rename','0-day-analytics')+'</button>');
        $renameBtn.on('click', function(){
            const currentBase = dirPath.substring(dirPath.lastIndexOf('/'));
            const newName = prompt(__('Enter new name:','0-day-analytics'), currentBase.replace('/',''));
            if(!newName){ return; }
            $.post(AFE_Ajax.ajax_url, { action:'advan_file_editor_rename', file:dirPath, new_name:newName, _ajax_nonce: AFE_Ajax.nonce }, function(res){
                alert(res.success ? '✅ '+__('Renamed','0-day-analytics') : '❌ '+res.data);
                if(res.success){
                    smartRefresh();
                }
            });
            hideContextMenu();
        });
        
        // Copy Path
        const $copyPathBtn = $('<button type="button" role="menuitem">📋 '+__('Copy Path','0-day-analytics')+'</button>');
        $copyPathBtn.on('click', function(){
            navigator.clipboard.writeText(dirPath).then(()=>{
                alert('📋 '+__('Path copied','0-day-analytics'));
            }).catch(()=>{ alert('❌ '+__('Unable to copy path','0-day-analytics')); });
            hideContextMenu();
        });
        
        // Delete Directory (Recursive)
        const $deleteBtn = $('<button type="button" role="menuitem" style="color: #d63638; font-weight: 600;">🗑️ '+__('Delete Recursively','0-day-analytics')+'</button>');
        $deleteBtn.on('click', function(){
            if(!confirm(__('⚠️ WARNING: This will RECURSIVELY delete the entire directory and all its contents!','0-day-analytics')+'\n\n'+__('Directory:','0-day-analytics')+' '+dirPath+'\n\n'+__('This action cannot be undone. Are you absolutely sure?','0-day-analytics'))) { 
                return; 
            }
            // Double confirmation
            if(!confirm(__('FINAL CONFIRMATION: Delete','0-day-analytics')+' '+dirPath+' '+__('and ALL its contents?','0-day-analytics'))) {
                return;
            }
            $.post(AFE_Ajax.ajax_url, { action:'advan_file_editor_delete', path:dirPath, _ajax_nonce:AFE_Ajax.nonce }, function(res){
                alert(res.success ? '🗑️ '+__('Directory deleted','0-day-analytics') : '❌ '+res.data);
                if(res.success){
                    smartRefresh();
                }
            });
            hideContextMenu();
        });
        
        // Duplicate
        const $duplicateBtn = $('<button type="button" role="menuitem">🧬 '+__('Duplicate','0-day-analytics')+'</button>');
        $duplicateBtn.on('click', function(){
            $.post(AFE_Ajax.ajax_url, { action:'advan_file_editor_duplicate', file:dirPath, _ajax_nonce:AFE_Ajax.nonce }, function(res){
                alert(res.success ? '🧬 '+__('Duplicated','0-day-analytics')+': '+res.data.new_name : '❌ '+res.data);
                if(res.success){
                    smartRefresh();
                }
            });
            hideContextMenu();
        });
        
        // Create Archive
        const $archiveBtn = $('<button type="button" role="menuitem">📦 '+__('Create Archive','0-day-analytics')+'</button>');
        $archiveBtn.on('click', function(){
            hideContextMenu();
            createArchive(dirPath);
        });
        
        $ctxMenu.append($renameBtn, $duplicateBtn, $archiveBtn, $copyPathBtn, $deleteBtn);
        $('body').append($ctxMenu);
        const x = e.pageX;
        const y = e.pageY;
        $ctxMenu.css({ top: y + 'px', left: x + 'px' });
        
        // Delay binding to avoid immediate self-close
        setTimeout(function(){
            $(document).on('mousedown.wfeCtx', function(ev){
                if(!$ctxMenu) return;
                if(!$(ev.target).closest('.wfe-context-menu').length){ hideContextMenu(); }
            });
            $(document).on('contextmenu.wfeCtx', function(ev){
                if(!$ctxMenu) return;
                if(!$(ev.target).closest('.wfe-context-menu').length){ hideContextMenu(); }
            });
            $(document).on('keydown.wfeCtx', function(ev){ if(ev.key === 'Escape'){ hideContextMenu(); } });
            $(window).on('scroll.wfeCtx resize.wfeCtx', hideContextMenu);
        },0);
        
        $renameBtn.focus();
    }
    
    // Global listeners now added dynamically per open; fallback in case menu injected elsewhere
    // (No always-on handlers needed here.)

    // --- File Upload Functionality ---
    const $fileInput = $('#wfe-file-input');
    const $uploadBtn = $('#wfe-upload-file');
    const $tree = $('#wfe-tree');
    const $dropZone = $('#wfe-drop-zone');

    // Upload button click handler
    $uploadBtn.on('click', function() {
        $fileInput.click();
    });

    // File input change handler
    $fileInput.on('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            uploadFiles(files);
        }
        // Reset the input so the same file can be selected again
        $fileInput.val('');
    });

    // Drag and drop handlers
    $tree.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropZone.addClass('wfe-drop-active');
    });

    $tree.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Only hide if leaving the tree completely
        if (e.target === $tree[0]) {
            $dropZone.removeClass('wfe-drop-active');
        }
    });

    $tree.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropZone.removeClass('wfe-drop-active');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            uploadFiles(files);
        }
    });

    // Function to upload files
    function uploadFiles(files) {
        if (!files || files.length === 0) return;

        const totalFiles = files.length;
        let uploadedCount = 0;
        let failedCount = 0;
        const errors = [];
        const archivesToExtract = [];

        // Show progress bar
        const $progressOverlay = $('#wfe-upload-progress');
        const $progressTitle = $('#wfe-progress-title');
        const $progressBar = $('#wfe-progress-bar');
        const $progressText = $('#wfe-progress-text');
        const $progressStatus = $('#wfe-progress-status');
        const $cancelBtn = $('#wfe-progress-cancel');
        
        $progressTitle.text(__('Uploading Files', '0-day-analytics'));
        $progressOverlay.fadeIn(200);
        $progressBar.css('width', '0%').text('');
        $progressText.text('0 / ' + totalFiles);
        $progressStatus.text(__('Preparing upload...', '0-day-analytics')).removeClass('success error warning');
        $cancelBtn.hide();

        Array.from(files).forEach(file => {
            const formData = new FormData();
            formData.append('action', 'advan_file_editor_upload_file');
            formData.append('_ajax_nonce', AFE_Ajax.nonce);
            formData.append('dir', currentDir);
            formData.append('file', file);

            $.ajax({
                url: AFE_Ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    uploadedCount++;
                    if (!res.success) {
                        failedCount++;
                        errors.push(file.name + ': ' + res.data);
                    } else if (res.data.is_archive) {
                        // Track archives for extraction
                        archivesToExtract.push({
                            path: res.data.path,
                            name: res.data.name
                        });
                    }
                    updateProgress();
                },
                error: function() {
                    uploadedCount++;
                    failedCount++;
                    errors.push(file.name + ': ' + __('Network error', '0-day-analytics'));
                    updateProgress();
                }
            });
        });

        function updateProgress() {
            const percent = Math.round((uploadedCount / totalFiles) * 100);
            $progressBar.css('width', percent + '%').text(percent + '%');
            $progressText.text(uploadedCount + ' / ' + totalFiles);
            
            if (uploadedCount < totalFiles) {
                const successCount = uploadedCount - failedCount;
                $progressStatus.text(__('Uploading', '0-day-analytics') + ': ' + successCount + ' ' + __('successful', '0-day-analytics'));
            } else {
                // All uploads complete
                completeUpload();
            }
        }

        function completeUpload() {
            const successCount = totalFiles - failedCount;
            
            // Update status message
            if (failedCount === 0) {
                $progressStatus.text('✅ ' + __('All files uploaded successfully!', '0-day-analytics')).addClass('success');
            } else if (successCount === 0) {
                $progressStatus.html('❌ ' + __('All uploads failed', '0-day-analytics') + '<br><small>' + errors.slice(0, 3).join('<br>') + (errors.length > 3 ? '<br>...' : '') + '</small>').addClass('error');
            } else {
                $progressStatus.html('⚠️ ' + __('Partial success', '0-day-analytics') + ': ' + successCount + '/' + totalFiles + '<br><small>' + errors.slice(0, 2).join('<br>') + (errors.length > 2 ? '<br>...' : '') + '</small>').addClass('warning');
            }
            
            // Check if there are archives to extract
            if (archivesToExtract.length > 0) {
                // Ask user if they want to extract archives
                setTimeout(function() {
                    if (confirm(__('Would you like to extract the uploaded archive(s)?', '0-day-analytics') + '\n\n' + archivesToExtract.map(a => a.name).join('\n'))) {
                        extractArchivesSequentially(archivesToExtract, 0);
                    } else {
                        // Just refresh and close
                        smartRefresh();
                        setTimeout(function() {
                            $progressOverlay.fadeOut(300);
                        }, 2000);
                    }
                }, 500);
            } else {
                // Refresh the file tree with smart refresh
                smartRefresh();
                
                // Auto-hide progress bar after 2.5 seconds
                setTimeout(function() {
                    $progressOverlay.fadeOut(300);
                }, 2500);
            }
        }
    }
    
    // Function to extract archives sequentially
    function extractArchivesSequentially(archives, index) {
        if (index >= archives.length) {
            // All archives extracted
            smartRefresh();
            const $progressOverlay = $('#wfe-upload-progress');
            setTimeout(function() {
                $progressOverlay.fadeOut(300);
            }, 2000);
            return;
        }
        
        const archive = archives[index];
        extractArchive(archive.path, function() {
            // Continue with next archive
            extractArchivesSequentially(archives, index + 1);
        });
    }
    
    // Function to extract a single archive
    function extractArchive(archivePath, callback) {
        const $progressOverlay = $('#wfe-upload-progress');
        const $progressTitle = $('#wfe-progress-title');
        const $progressBar = $('#wfe-progress-bar');
        const $progressText = $('#wfe-progress-text');
        const $progressStatus = $('#wfe-progress-status');
        const $cancelBtn = $('#wfe-progress-cancel');
        
        // Derive target directory from archive path (directory where archive is located)
        const targetDir = archivePath.substring(0, archivePath.lastIndexOf('/')) || AFE_Ajax.base;
        
        // Reset and show modal
        $progressTitle.text('📦 ' + __('Extracting Archive', '0-day-analytics'));
        $progressBar.css('width', '0%').text('0%');
        $progressText.text(__('Initializing...', '0-day-analytics'));
        $progressStatus.text('').removeClass('success error warning');
        $cancelBtn.show();
        $progressOverlay.fadeIn(200);
        
        let extractData = null;
        let cancelled = false;
        const allErrors = [];

        // Cancel button handler
        $cancelBtn.off('click').on('click', function() {
            if (!cancelled && extractData) {
                cancelled = true;
                $cancelBtn.prop('disabled', true).text(__('Cancelling...', '0-day-analytics'));
                
                $.post(AFE_Ajax.ajax_url, {
                    action: 'advan_file_editor_extract_archive_cancel',
                    extract_dir: extractData.extract_dir,
                    _ajax_nonce: AFE_Ajax.nonce
                }, function(res) {
                    $progressStatus.html('✖ ' + __('Extraction cancelled', '0-day-analytics')).addClass('warning');
                    $cancelBtn.hide();
                    setTimeout(function() { 
                        $progressOverlay.fadeOut(300);
                        $cancelBtn.prop('disabled', false).text('✖ ' + __('Cancel', '0-day-analytics'));
                        if (callback) callback();
                    }, 2000);
                }).fail(function() {
                    $progressStatus.html('⚠️ ' + __('Failed to cancel cleanly', '0-day-analytics')).addClass('warning');
                    $cancelBtn.hide();
                    setTimeout(function() { 
                        $progressOverlay.fadeOut(300);
                        $cancelBtn.prop('disabled', false).text('✖ ' + __('Cancel', '0-day-analytics'));
                        if (callback) callback();
                    }, 2000);
                });
            }
        });
        
        // Step 1: Initialize extraction
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_extract_archive_start',
            archive_path: archivePath,
            target_dir: targetDir,
            _ajax_nonce: AFE_Ajax.nonce
        }, function(res) {
            if (!res.success) {
                $progressStatus.html('❌ ' + __('Error', '0-day-analytics') + ': ' + res.data).addClass('error');
                $cancelBtn.hide();
                setTimeout(function() { 
                    $progressOverlay.fadeOut(300);
                    if (callback) callback();
                }, 3000);
                return;
            }
            
            extractData = res.data;
            $progressText.text(__('Processing', '0-day-analytics') + ': 0 / ' + extractData.total_files + ' ' + __('files', '0-day-analytics'));
            
            // Step 2: Process files in batches
            processExtractionBatch(0);
        }).fail(function(jqXHR) {
            const errorMsg = jqXHR.responseJSON && jqXHR.responseJSON.data ? jqXHR.responseJSON.data : __('Failed to initialize extraction', '0-day-analytics');
            $progressStatus.html('❌ ' + __('Error', '0-day-analytics') + ': ' + errorMsg).addClass('error');
            $cancelBtn.hide();
            setTimeout(function() { 
                $progressOverlay.fadeOut(300);
                if (callback) callback();
            }, 3000);
        });
        
        function processExtractionBatch(processed) {
            if (cancelled) {
                return; // Stop processing if cancelled
            }
            
            $.post(AFE_Ajax.ajax_url, {
                action: 'advan_file_editor_extract_archive_batch',
                archive_path: extractData.archive_path,
                extract_dir: extractData.extract_dir,
                files: JSON.stringify(extractData.files),
                processed: processed,
                batch_size: 50,
                _ajax_nonce: AFE_Ajax.nonce
            }, function(res) {
                if (cancelled) {
                    return; // Stop if cancelled during request
                }
                
                if (!res.success) {
                    $progressStatus.html('❌ ' + __('Error', '0-day-analytics') + ': ' + res.data).addClass('error');
                    $cancelBtn.hide();
                    setTimeout(function() { 
                        $progressOverlay.fadeOut(300);
                        if (callback) callback();
                    }, 3000);
                    return;
                }
                
                // Track errors from this batch
                if (res.data.errors && res.data.errors.length > 0) {
                    allErrors.push(...res.data.errors);
                }
                
                const percent = Math.round((res.data.processed / res.data.total) * 100);
                $progressBar.css('width', percent + '%').text(percent + '%');
                $progressText.text(__('Processing', '0-day-analytics') + ': ' + res.data.processed + ' / ' + res.data.total + ' ' + __('files', '0-day-analytics'));
                
                // Show ongoing errors if any
                if (allErrors.length > 0) {
                    const errorPreview = allErrors.slice(0, 2).join('<br>');
                    const moreErrors = allErrors.length > 2 ? '<br>...' + (allErrors.length - 2) + ' ' + __('more errors', '0-day-analytics') : '';
                    $progressStatus.html('⚠️ ' + __('Extracting with errors', '0-day-analytics') + ':<br><small>' + errorPreview + moreErrors + '</small>').addClass('warning');
                }
                
                if (res.data.done) {
                    // Step 3: Finalize extraction
                    finalizeExtraction();
                } else {
                    // Continue with next batch
                    processExtractionBatch(res.data.processed);
                }
            }).fail(function(jqXHR) {
                if (!cancelled) {
                    const errorMsg = jqXHR.responseJSON && jqXHR.responseJSON.data ? jqXHR.responseJSON.data : __('Failed to process files', '0-day-analytics');
                    $progressStatus.html('❌ ' + __('Error', '0-day-analytics') + ': ' + errorMsg).addClass('error');
                    $cancelBtn.hide();
                    setTimeout(function() { 
                        $progressOverlay.fadeOut(300);
                        if (callback) callback();
                    }, 3000);
                }
            });
        }
        
        function finalizeExtraction() {
            if (cancelled) {
                return; // Stop if cancelled
            }
            
            $.post(AFE_Ajax.ajax_url, {
                action: 'advan_file_editor_extract_archive_finish',
                extract_dir: extractData.extract_dir,
                archive_path: extractData.archive_path,
                delete_archive: false,
                _ajax_nonce: AFE_Ajax.nonce
            }, function(res) {
                if (!res.success) {
                    $progressStatus.html('❌ ' + __('Error', '0-day-analytics') + ': ' + res.data).addClass('error');
                    $cancelBtn.hide();
                    setTimeout(function() { 
                        $progressOverlay.fadeOut(300);
                        if (callback) callback();
                    }, 3000);
                    return;
                }
                
                $progressBar.css('width', '100%').text('100%');
                
                // Show final status with error summary if applicable
                if (allErrors.length > 0) {
                    const errorSummary = allErrors.slice(0, 3).join('<br>');
                    const moreErrors = allErrors.length > 3 ? '<br>...' + (allErrors.length - 3) + ' ' + __('more errors', '0-day-analytics') : '';
                    $progressStatus.html('⚠️ ' + __('Extracted with errors', '0-day-analytics') + ': ' + res.data.extract_dir + ' (' + res.data.items + ' ' + __('items', '0-day-analytics') + ')<br><small>' + errorSummary + moreErrors + '</small>').addClass('warning');
                } else {
                    $progressStatus.html('✅ ' + __('Archive extracted', '0-day-analytics') + ': ' + res.data.extract_dir + ' (' + res.data.items + ' ' + __('items', '0-day-analytics') + ')').addClass('success');
                }
                $cancelBtn.hide();
                
                // Refresh the file tree
                smartRefresh();
                
                // Auto-hide after 3 seconds
                setTimeout(function() {
                    $progressOverlay.fadeOut(300);
                    if (callback) callback();
                }, 3000);
            }).fail(function(jqXHR) {
                const errorMsg = jqXHR.responseJSON && jqXHR.responseJSON.data ? jqXHR.responseJSON.data : __('Failed to finalize extraction', '0-day-analytics');
                $progressStatus.html('❌ ' + __('Error', '0-day-analytics') + ': ' + errorMsg).addClass('error');
                $cancelBtn.hide();
                setTimeout(function() { 
                    $progressOverlay.fadeOut(300);
                    if (callback) callback();
                }, 3000);
            });
        }
    }

    // --- Create Archive Functionality ---
    function createArchive(path) {
        if (!path) {
            alert('❌ ' + __('No path specified', '0-day-analytics'));
            return;
        }

        // Use existing modal
        const $progressOverlay = $('#wfe-upload-progress');
        const $progressTitle = $('#wfe-progress-title');
        const $progressBar = $('#wfe-progress-bar');
        const $progressText = $('#wfe-progress-text');
        const $progressStatus = $('#wfe-progress-status');
        const $cancelBtn = $('#wfe-progress-cancel');
        
        // Reset and show modal
        $progressTitle.text('📦 ' + __('Creating Archive', '0-day-analytics'));
        $progressBar.css('width', '0%').text('0%');
        $progressText.text(__('Initializing...', '0-day-analytics'));
        $progressStatus.text('').removeClass('success error warning');
        $cancelBtn.show();
        $progressOverlay.fadeIn(200);
        
        let archiveData = null;
        let cancelled = false;

        // Cancel button handler
        $cancelBtn.off('click').on('click', function() {
            if (!cancelled && archiveData) {
                cancelled = true;
                $cancelBtn.prop('disabled', true).text(__('Cancelling...', '0-day-analytics'));
                
                $.post(AFE_Ajax.ajax_url, {
                    action: 'advan_file_editor_create_archive_cancel',
                    archive_path: archiveData.archive_path,
                    _ajax_nonce: AFE_Ajax.nonce
                }, function(res) {
                    $progressStatus.text('✖ ' + __('Archive creation cancelled', '0-day-analytics')).addClass('warning');
                    $cancelBtn.hide();
                    setTimeout(function() { 
                        $progressOverlay.fadeOut(300);
                        $cancelBtn.prop('disabled', false).text('✖ ' + __('Cancel', '0-day-analytics'));
                    }, 2000);
                }).fail(function() {
                    $progressStatus.text('⚠️ ' + __('Failed to cancel cleanly', '0-day-analytics')).addClass('warning');
                    $cancelBtn.hide();
                    setTimeout(function() { 
                        $progressOverlay.fadeOut(300);
                        $cancelBtn.prop('disabled', false).text('✖ ' + __('Cancel', '0-day-analytics'));
                    }, 2000);
                });
            }
        });
        
        // Step 1: Initialize archive creation
        $.post(AFE_Ajax.ajax_url, {
            action: 'advan_file_editor_create_archive_start',
            path: path,
            _ajax_nonce: AFE_Ajax.nonce
        }, function(res) {
            if (!res.success) {
                $progressStatus.text('❌ ' + res.data).addClass('error');
                $cancelBtn.hide();
                setTimeout(function() { $progressOverlay.fadeOut(300); }, 2000);
                return;
            }
            
            archiveData = res.data;
            $progressText.text(__('Processing', '0-day-analytics') + ': 0 / ' + archiveData.total_files + ' ' + __('files', '0-day-analytics'));
            
            // Step 2: Process files in batches
            processArchiveBatch(0);
        }).fail(function() {
            $progressStatus.text('❌ ' + __('Failed to initialize archive', '0-day-analytics')).addClass('error');
            $cancelBtn.hide();
            setTimeout(function() { $progressOverlay.fadeOut(300); }, 2000);
        });
        
        function processArchiveBatch(processed) {
            if (cancelled) {
                return; // Stop processing if cancelled
            }
            
            $.post(AFE_Ajax.ajax_url, {
                action: 'advan_file_editor_create_archive_batch',
                source: archiveData.source,
                archive_path: archiveData.archive_path,
                files: JSON.stringify(archiveData.files),
                processed: processed,
                batch_size: 50,
                is_dir: archiveData.is_dir,
                _ajax_nonce: AFE_Ajax.nonce
            }, function(res) {
                if (cancelled) {
                    return; // Stop if cancelled during request
                }
                
                if (!res.success) {
                    $progressStatus.text('❌ ' + res.data).addClass('error');
                    $cancelBtn.hide();
                    setTimeout(function() { $progressOverlay.fadeOut(300); }, 2000);
                    return;
                }
                
                const percent = Math.round((res.data.processed / res.data.total) * 100);
                $progressBar.css('width', percent + '%').text(percent + '%');
                $progressText.text(__('Processing', '0-day-analytics') + ': ' + res.data.processed + ' / ' + res.data.total + ' ' + __('files', '0-day-analytics'));
                
                if (res.data.done) {
                    // Step 3: Finalize archive
                    finalizeArchive();
                } else {
                    // Continue with next batch
                    processArchiveBatch(res.data.processed);
                }
            }).fail(function() {
                if (!cancelled) {
                    $progressStatus.text('❌ ' + __('Failed to process files', '0-day-analytics')).addClass('error');
                    $cancelBtn.hide();
                    setTimeout(function() { $progressOverlay.fadeOut(300); }, 2000);
                }
            });
        }
        
        function finalizeArchive() {
            if (cancelled) {
                return; // Stop if cancelled
            }
            
            $.post(AFE_Ajax.ajax_url, {
                action: 'advan_file_editor_create_archive_finish',
                archive_path: archiveData.archive_path,
                _ajax_nonce: AFE_Ajax.nonce
            }, function(res) {
                if (!res.success) {
                    $progressStatus.text('❌ ' + res.data).addClass('error');
                    $cancelBtn.hide();
                    setTimeout(function() { $progressOverlay.fadeOut(300); }, 2000);
                    return;
                }
                
                $progressBar.css('width', '100%').text('100%');
                $progressStatus.text('✅ ' + __('Archive created', '0-day-analytics') + ': ' + res.data.archive + ' (' + res.data.size + ')').addClass('success');
                $cancelBtn.hide();
                
                // Refresh the file tree
                smartRefresh();
                
                // Auto-hide after 3 seconds
                setTimeout(function() {
                    $progressOverlay.fadeOut(300);
                }, 3000);
            }).fail(function() {
                $progressStatus.text('❌ ' + __('Failed to finalize archive', '0-day-analytics')).addClass('error');
                $cancelBtn.hide();
                setTimeout(function() { $progressOverlay.fadeOut(300); }, 2000);
            });
        }
    }
});
