/**
 * Canvas Editor JavaScript - Modular Refactor
 * 
 * Architecture:
 * - CanvasCore: Entry point, routing (List vs Editor)
 * - CanvasListPage: Handles dashboard actions
 * - CanvasEditor: Main controller for the visual editor
 *      - CanvasState: Manages data (slides, settings, history)
 *      - CanvasRenderer: Handles visual rendering on the stage
 *      - CanvasUI: Handles panels, toolbar, events
 *      - CanvasLayers: Handles the layer list
 *      - CanvasItems: Factory and logic for individual elements
 */
(function ($) {
    'use strict';

    // ==========================================
    // MODULE: Canvas State
    // ==========================================
    const CanvasState = {
        data: {
            slides: [],
            sliderSettings: {
                autoplay: false,
                autoplaySpeed: 3000,
                effect: 'slide',
                arrows: true,
                dots: true
            }
        },
        activeSlideIndex: 0,
        selectedItemId: null,
        clipboard: null,
        history: [],
        historyIndex: -1,
        isDirty: false,

        init: function () {
            this.history = [];
            this.historyIndex = -1;
            this.isDirty = false;
        },

        getCurrentSlide: function () {
            return this.data.slides[this.activeSlideIndex];
        },

        getItems: function () {
            const slide = this.getCurrentSlide();
            return slide ? slide.items : [];
        },

        getItem: function (id) {
            const items = this.getItems();
            return this.findItemRecursive(items, id);
        },

        findItemRecursive: function (items, id) {
            for (let item of items) {
                if (item.id === id) return item;
                if (item.children && item.children.length > 0) {
                    const found = this.findItemRecursive(item.children, id);
                    if (found) return found;
                }
            }
            return null;
        },

        // Helper to find parent of an item
        findParent: function (items, id) {
            for (let item of items) {
                if (item.children && item.children.length > 0) {
                    if (item.children.find(c => c.id === id)) return item;
                    const found = this.findParent(item.children, id);
                    if (found) return found;
                }
            }
            return null; // Top level or not found
        },

        pushHistory: function () {
            // Deep copy current state
            const state = JSON.parse(JSON.stringify(this.data));

            // Remove future history if we're in the middle
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(state);
            this.historyIndex++;

            // Limit history
            if (this.history.length > 50) {
                this.history.shift();
                this.historyIndex--;
            }

            CanvasUI.updateUndoRedoButtons();
        },

        undo: function () {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                this.data = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                return true;
            }
            return false;
        },

        redo: function () {
            if (this.historyIndex < this.history.length - 1) {
                this.historyIndex++;
                this.data = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                return true;
            }
            return false;
        }
    };

    // ==========================================
    // MODULE: Canvas Items Factory
    // ==========================================
    const CanvasItems = {
        // Allowed element types for security validation
        allowedTypes: ['image', 'video', 'text', 'button', 'shape', 'container'],

        // Sanitize text content to prevent XSS
        sanitizeText: function (text) {
            if (typeof text !== 'string') return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        // Validate and sanitize URL
        sanitizeUrl: function (url) {
            if (typeof url !== 'string') return '';
            // Remove javascript: and data: protocols (except images)
            const lowered = url.toLowerCase().trim();
            if (lowered.startsWith('javascript:') ||
                (lowered.startsWith('data:') && !lowered.startsWith('data:image/'))) {
                return '';
            }
            // Basic URL validation - allow relative and absolute URLs
            if (url.match(/^(https?:\/\/|\/\/|\/|\.\/|#)/i) || !url.includes(':')) {
                return url;
            }
            return '';
        },

        // Validate color values
        sanitizeColor: function (color) {
            if (typeof color !== 'string') return '#000000';
            // Allow hex, rgb, rgba, hsl, hsla, named colors
            if (/^(#[0-9A-Fa-f]{3,8}|rgba?\([^)]+\)|hsla?\([^)]+\)|[a-zA-Z]+)$/.test(color)) {
                return color;
            }
            return '#000000';
        },

        create: function (type, defaults = {}) {
            // Validate type for security
            if (!this.allowedTypes.includes(type)) {
                console.warn('Invalid element type:', type);
                return null;
            }

            const id = 'item_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

            const baseItem = {
                id: id,
                type: type,
                x: 50,
                y: 50,
                width: 200,
                height: 150,
                rotation: 0,
                z_index: CanvasState.getItems().length + 10,
                opacity: 1
            };

            let specificItem = {};

            switch (type) {
                case 'image':
                    specificItem = {
                        image_id: 0,
                        image_url: '',
                        alt_text: '',
                        link: '',
                        object_fit: 'cover' // cover, contain, fill, none
                    };
                    break;

                case 'video':
                    specificItem = {
                        video_url: '',           // Direct video URL (MP4, WebM)
                        video_id: 0,             // WordPress attachment ID
                        embed_url: '',           // YouTube/Vimeo embed URL
                        source_type: 'upload',   // upload, youtube, vimeo, embed
                        autoplay: true,
                        loop: true,
                        muted: true,
                        controls: false,
                        poster_url: '',          // Poster/thumbnail image
                        poster_id: 0,
                        width: 400,
                        height: 225              // 16:9 aspect ratio
                    };
                    break;

                case 'text':
                    specificItem = {
                        content: 'Double click to edit',
                        font_size: 24,
                        font_family: 'Arial, sans-serif',
                        color: '#333333',
                        text_align: 'left',
                        // Auto-size text initially
                        width: 300,
                        height: 'auto'
                    };
                    break;

                case 'button':
                    specificItem = {
                        text: 'Click Me',
                        link: '#',
                        bg_color: '#0073aa',
                        text_color: '#ffffff',
                        border_radius: 4,
                        width: 140,
                        height: 45
                    };
                    break;

                case 'shape':
                    specificItem = {
                        shape_type: 'rectangle', // rectangle, circle
                        fill_color: '#cccccc',
                        stroke_color: '#333333',
                        stroke_width: 0,
                        width: 100,
                        height: 100
                    };
                    break;

                case 'container':
                    specificItem = {
                        children: [],
                        display: 'flex',         // flex, grid
                        direction: 'row',        // row, column
                        justify: 'flex-start',
                        align: 'stretch',
                        gap: 10,
                        padding: 20,
                        bg_color: 'rgba(255,255,255,0.1)',
                        border_width: 1,
                        border_color: '#444',
                        border_radius: 0,
                        width: 400,
                        height: 300,
                        // Background media (Elementor/WPBakery-like)
                        bg_type: 'color',        // color, image, video
                        bg_image_url: '',
                        bg_image_id: 0,
                        bg_video_url: '',
                        bg_video_id: 0,
                        bg_position: 'center center',
                        bg_size: 'cover',        // cover, contain, auto
                        bg_repeat: 'no-repeat',
                        bg_overlay_color: '',    // Optional overlay color
                        bg_overlay_opacity: 0.5
                    };
                    break;
            }

            return $.extend(true, baseItem, specificItem, defaults);
        }
    };

    // ==========================================
    // MODULE: Canvas Utils (Helpers)
    // ==========================================
    const CanvasUtils = {
        // Check if value is a valid CSS unit string or number
        isValidUnit: function (val) {
            if (typeof val === 'number') return true;
            if (val === 'auto') return true;
            // Matches: 10, 10.5, -10, 10px, 10%, 10em, 10rem, 10vw, 10vh
            return /^-?\d+(\.\d+)?(px|%|em|rem|vh|vw|auto)?$/.test(val);
        },

        // Format for CSS output (defaults to px if number)
        formatUnit: function (val) {
            if (val === 'auto') return 'auto';
            if (typeof val === 'number') return val + 'px';
            if (typeof val === 'string' && !isNaN(parseFloat(val)) && isFinite(val)) {
                // It's a string number like "100", append px
                return val + 'px';
            }
            return val; // Assume it has unit or is valid
        },

        // Sanitize input from UI
        sanitizeInput: function (val) {
            if (val === 'auto') return 'auto';
            // If it's just a number, return as number
            if (!isNaN(Number(val)) && val !== '') return Number(val);
            // If valid unit string, return as string
            if (this.isValidUnit(val)) return val;
            // Fallback: parse float and return number (strips invalid unit)
            return parseFloat(val) || 0;
        }
    };

    // ==========================================
    // MODULE: Canvas Renderer
    // ==========================================
    const CanvasRenderer = {
        canvas: null,

        init: function () {
            this.canvas = $('#mgwpp-canvas');

            // Make Root Canvas Droppable
            this.canvas.droppable({
                accept: '.mgwpp-canvas-item, .mgwpp-tool-item',
                tolerance: 'pointer',
                drop: function (event, ui) {
                    // Check if dropping a new tool
                    if (ui.draggable.hasClass('mgwpp-tool-item')) {
                        const type = ui.draggable.data('type');

                        // Calculate position relative to canvas
                        const canvasOffset = $('#mgwpp-canvas').offset();
                        const dropLeft = ui.offset.left - canvasOffset.left;
                        const dropTop = ui.offset.top - canvasOffset.top;

                        // Create item at this position
                        const newItem = CanvasItems.create(type, {
                            x: dropLeft,
                            y: dropTop,
                            z_index: CanvasState.getItems().length + 10
                        });

                        CanvasState.getItems().push(newItem);
                        CanvasState.selectedItemId = newItem.id;
                        CanvasState.isDirty = true;
                        CanvasState.pushHistory(); // Add to history
                        CanvasEditor.refreshAll();
                        return;
                    }

                    // ... Existing Logic for moving items ...
                    // Only handle if not swallowed by a greedy child container
                    // (jQuery UI greedy checks valid for nested droppables)

                    const draggedId = ui.draggable.data('item-id');

                    // We need to check if the item is ALREADY root, if so, normal drag stop handles it.
                    // But if it was in a container, we need to reparent.
                    const slide = CanvasState.getCurrentSlide();
                    const isRoot = slide.items.find(i => i.id === draggedId);

                    if (!isRoot) {
                        // It was nested, now move to root
                        CanvasEditor.reparentItem(draggedId, 'root');

                        // Update position to where it was dropped relative to canvas
                        // This is tricky because drop event gives position relative to page/offset
                        const offset = ui.helper.offset();
                        const canvasOffset = $('#mgwpp-canvas').offset();
                        const item = CanvasState.getItem(draggedId);
                        item.x = offset.left - canvasOffset.left;
                        item.y = offset.top - canvasOffset.top;
                    }
                }
            });
        },

        render: function () {
            this.canvas.find('.mgwpp-canvas-item').remove();

            const items = CanvasState.getItems();
            // Sort by z-index for rendering order
            const sortedItems = [...items].sort((a, b) => a.z_index - b.z_index);

            sortedItems.forEach(item => {
                this.renderItem(item);
            });

            // Update global canvas styles
            const settings = CanvasState.data.canvas_settings || {};
            if (settings.width) this.canvas.css('width', settings.width + 'px');
            if (settings.height) this.canvas.css('height', settings.height + 'px');
            if (settings.background) this.canvas.css('background-color', settings.background);

            CanvasLayers.render();
        },

        // Updated signature to accept target container
        renderItem: function (item, $targetContainer = null) {
            const $container = $targetContainer || this.canvas; // Default to main canvas

            // Wrapper: Position, Size, Dragging, Selection
            const $el = $('<div>')
                .addClass('mgwpp-canvas-item')
                .addClass('mgwpp-canvas-item-' + item.type)
                .attr('data-item-id', item.id)
                .attr('data-element-type', item.type.charAt(0).toUpperCase() + item.type.slice(1));

            // Styles
            const styles = {
                width: CanvasUtils.formatUnit(item.width),
                height: CanvasUtils.formatUnit(item.height),
                zIndex: item.z_index,
                opacity: item.opacity
            };

            const isRoot = ($targetContainer === null || $targetContainer.is('#mgwpp-canvas'));

            if (isRoot) {
                styles.left = CanvasUtils.formatUnit(item.x);
                styles.top = CanvasUtils.formatUnit(item.y);
                styles.position = 'absolute';
            } else {
                // Inside a container
                styles.position = 'relative'; // Participate in flow
                styles.left = 'auto';
                styles.top = 'auto';
            }

            $el.css(styles);

            if (item.id === CanvasState.selectedItemId) {
                $el.addClass('selected');
            }

            // Inner: Rotation & Content
            const $inner = $('<div>')
                .addClass('mgwpp-item-inner')
                .css({
                    width: '100%',
                    height: '100%',
                    transform: 'rotate(' + (item.rotation || 0) + 'deg)',
                    transformOrigin: 'center center',
                    display: 'block'
                });

            // Internal Content
            let contentHtml = '';

            // Apply Content Styles
            switch (item.type) {
                case 'image':
                    if (item.image_url) {
                        const sanitizedUrl = CanvasItems.sanitizeUrl(item.image_url);
                        const sanitizedAlt = CanvasItems.sanitizeText(item.alt_text || '');
                        const objectFit = item.object_fit || 'cover';
                        contentHtml = `<img src="${sanitizedUrl}" alt="${sanitizedAlt}" style="width:100%;height:100%;object-fit:${objectFit};pointer-events:none;display:block;">`;
                    } else {
                        contentHtml = '<div class="mgwpp-placeholder" style="width:100%;height:100%;background:linear-gradient(135deg,#667eea22,#764ba222);display:flex;align-items:center;justify-content:center;border:2px dashed #667eea55;"><span class="dashicons dashicons-format-image" style="font-size:32px;color:#667eea;"></span></div>';
                    }
                    break;

                case 'video':
                    $inner.css({
                        overflow: 'hidden',
                        backgroundColor: '#000'
                    });

                    if (item.source_type === 'youtube' && item.embed_url) {
                        // YouTube embed
                        const youtubeId = this.extractYouTubeId(item.embed_url);
                        if (youtubeId) {
                            const autoplay = item.autoplay ? '1' : '0';
                            const loop = item.loop ? '1' : '0';
                            const mute = item.muted ? '1' : '0';
                            contentHtml = `<iframe src="https://www.youtube.com/embed/${youtubeId}?autoplay=${autoplay}&loop=${loop}&mute=${mute}&playlist=${youtubeId}&controls=${item.controls ? '1' : '0'}" style="width:100%;height:100%;border:none;" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
                        }
                    } else if (item.source_type === 'vimeo' && item.embed_url) {
                        // Vimeo embed
                        const vimeoId = this.extractVimeoId(item.embed_url);
                        if (vimeoId) {
                            const autoplay = item.autoplay ? '1' : '0';
                            const loop = item.loop ? '1' : '0';
                            const muted = item.muted ? '1' : '0';
                            contentHtml = `<iframe src="https://player.vimeo.com/video/${vimeoId}?autoplay=${autoplay}&loop=${loop}&muted=${muted}&controls=${item.controls ? '1' : '0'}" style="width:100%;height:100%;border:none;" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
                        }
                    } else if (item.video_url) {
                        // Native HTML5 video
                        const sanitizedVideoUrl = CanvasItems.sanitizeUrl(item.video_url);
                        const sanitizedPoster = item.poster_url ? CanvasItems.sanitizeUrl(item.poster_url) : '';
                        const attrs = [];
                        if (item.autoplay) attrs.push('autoplay');
                        if (item.loop) attrs.push('loop');
                        if (item.muted) attrs.push('muted');
                        if (item.controls) attrs.push('controls');
                        attrs.push('playsinline');

                        contentHtml = `<video ${attrs.join(' ')} style="width:100%;height:100%;object-fit:cover;"${sanitizedPoster ? ` poster="${sanitizedPoster}"` : ''}><source src="${sanitizedVideoUrl}" type="video/mp4">Your browser does not support video.</video>`;
                    } else {
                        // Placeholder
                        contentHtml = '<div class="mgwpp-placeholder" style="width:100%;height:100%;background:linear-gradient(135deg,#764ba222,#667eea22);display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed #764ba255;"><span class="dashicons dashicons-video-alt3" style="font-size:32px;color:#764ba2;margin-bottom:8px;"></span><span style="color:#764ba2;font-size:11px;">Add Video</span></div>';
                    }
                    break;

                case 'text':
                    $inner.css({
                        fontSize: CanvasUtils.formatUnit(item.font_size),
                        fontFamily: item.font_family,
                        color: item.color,
                        textAlign: item.text_align,
                        lineHeight: '1.4',
                        whiteSpace: 'pre-wrap', // Preserve formatting
                        wordBreak: 'break-word'
                    });
                    contentHtml = CanvasItems.sanitizeText(item.content);
                    break;

                case 'button':
                    const sanitizedBtnText = CanvasItems.sanitizeText(item.text);
                    const sanitizedBgColor = CanvasItems.sanitizeColor(item.bg_color);
                    const sanitizedTextColor = CanvasItems.sanitizeColor(item.text_color);
                    const btnStyle = `background:${sanitizedBgColor};color:${sanitizedTextColor};border-radius:${CanvasUtils.formatUnit(item.border_radius)};display:flex;align-items:center;justify-content:center;width:100%;height:100%;text-decoration:none;box-sizing:border-box;font-weight:500;`;
                    contentHtml = `<a href="#" style="${btnStyle}" onclick="return false;">${sanitizedBtnText}</a>`;
                    break;

                case 'shape':
                    $inner.css({
                        backgroundColor: CanvasItems.sanitizeColor(item.fill_color),
                        border: `${CanvasUtils.formatUnit(item.stroke_width)} solid ${CanvasItems.sanitizeColor(item.stroke_color)}`,
                        borderRadius: item.shape_type === 'circle' ? '50%' : '0',
                        boxSizing: 'border-box'
                    });
                    break;

                case 'container':
                    // Build container styles
                    const containerStyles = {
                        display: item.display,
                        flexDirection: item.direction,
                        justifyContent: item.justify,
                        alignItems: item.align,
                        gap: CanvasUtils.formatUnit(item.gap),
                        padding: CanvasUtils.formatUnit(item.padding),
                        border: `${CanvasUtils.formatUnit(item.border_width)} solid ${CanvasItems.sanitizeColor(item.border_color)}`,
                        borderRadius: CanvasUtils.formatUnit(item.border_radius || 0),
                        boxSizing: 'border-box',
                        position: 'relative',
                        overflow: 'hidden'
                    };

                    // Handle background based on bg_type (Elementor/WPBakery-like)
                    if (item.bg_type === 'image' && item.bg_image_url) {
                        const bgUrl = CanvasItems.sanitizeUrl(item.bg_image_url);
                        containerStyles.backgroundImage = `url('${bgUrl}')`;
                        containerStyles.backgroundSize = item.bg_size || 'cover';
                        containerStyles.backgroundPosition = item.bg_position || 'center center';
                        containerStyles.backgroundRepeat = item.bg_repeat || 'no-repeat';
                        containerStyles.backgroundColor = CanvasItems.sanitizeColor(item.bg_color);
                    } else if (item.bg_type === 'video' && item.bg_video_url) {
                        // Video background - we'll add the video element below
                        containerStyles.backgroundColor = '#000';
                    } else {
                        containerStyles.backgroundColor = CanvasItems.sanitizeColor(item.bg_color);
                    }

                    $inner.css(containerStyles);

                    // Add background video if set
                    if (item.bg_type === 'video' && item.bg_video_url) {
                        const bgVideoUrl = CanvasItems.sanitizeUrl(item.bg_video_url);
                        const $bgVideo = $(`<video class="mgwpp-container-bg-video" autoplay loop muted playsinline style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:0;pointer-events:none;"><source src="${bgVideoUrl}" type="video/mp4"></video>`);
                        $inner.append($bgVideo);
                    }

                    // Add overlay if set
                    if (item.bg_overlay_color && (item.bg_type === 'image' || item.bg_type === 'video')) {
                        const overlayColor = CanvasItems.sanitizeColor(item.bg_overlay_color);
                        const overlayOpacity = parseFloat(item.bg_overlay_opacity) || 0.5;
                        const $overlay = $(`<div class="mgwpp-container-overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;background:${overlayColor};opacity:${overlayOpacity};z-index:1;pointer-events:none;"></div>`);
                        $inner.append($overlay);
                    }

                    // Content wrapper for proper z-index stacking
                    const $contentWrapper = $('<div class="mgwpp-container-content" style="position:relative;z-index:2;display:flex;width:100%;height:100%;"></div>');
                    $contentWrapper.css({
                        flexDirection: item.direction,
                        justifyContent: item.justify,
                        alignItems: item.align,
                        gap: CanvasUtils.formatUnit(item.gap),
                        padding: CanvasUtils.formatUnit(item.padding),
                        boxSizing: 'border-box'
                    });

                    // Empty state visual
                    if (!item.children || item.children.length === 0) {
                        $inner.addClass('mgwpp-empty-container');
                        // Only show placeholder text if no background media is set
                        const hasBackgroundMedia = (item.bg_type === 'image' && item.bg_image_url) ||
                            (item.bg_type === 'video' && item.bg_video_url);
                        if (!hasBackgroundMedia) {
                            $contentWrapper.html('<div style="opacity:0.5;text-align:center;width:100%;padding:20px;display:flex;flex-direction:column;align-items:center;justify-content:center;"><span class="dashicons dashicons-plus-alt2" style="font-size:24px;margin-bottom:8px;color:#667eea;"></span><span style="font-size:12px;color:#888;">Drop Elements Here</span></div>');
                        }
                    }

                    $inner.append($contentWrapper);
                    // Store reference for child rendering
                    $el.data('$contentWrapper', $contentWrapper);
                    break;
            }

            // Only set HTML for non-containers (containers handle their own content)
            if (item.type !== 'container') {
                $inner.html(contentHtml);
            }

            $el.append($inner);
            $container.append($el);

            // Pass $el (the wrapper) to event handlers
            this.attachEvents($el, item, isRoot);

            // RECURSION logic for container children
            if (item.type === 'container' && item.children && item.children.length > 0) {
                // Get the content wrapper we stored earlier
                const $contentWrapper = $el.data('$contentWrapper');
                if ($contentWrapper) {
                    $contentWrapper.empty(); // Clear placeholder
                    const sortedChildren = [...item.children].sort((a, b) => a.z_index - b.z_index);
                    sortedChildren.forEach(child => {
                        this.renderItem(child, $contentWrapper);
                    });
                }
            }

            // Initialize Droppable on Container
            if (item.type === 'container') {
                const $contentWrapper = $el.data('$contentWrapper');
                $el.droppable({
                    accept: '.mgwpp-canvas-item, .mgwpp-tool-item',
                    greedy: true, // Prevents event from bubbling to parent container/canvas
                    tolerance: 'pointer',
                    over: function (event, ui) {
                        // Prevent self-drop highlight (though dnd prevents self-drop normally)
                        if (ui.draggable.hasClass('mgwpp-tool-item') || ui.draggable.data('item-id') !== item.id) {
                            $el.addClass('mgwpp-drag-over');
                            $inner.css('borderColor', '#58a5f7');
                        }
                    },
                    out: function (event, ui) {
                        $el.removeClass('mgwpp-drag-over');
                        $inner.css('borderColor', CanvasItems.sanitizeColor(item.border_color));
                    },
                    drop: function (event, ui) {
                        $el.removeClass('mgwpp-drag-over');
                        $inner.css('borderColor', CanvasItems.sanitizeColor(item.border_color));
                        event.stopPropagation(); // Explicitly stop bubbling

                        // HANDLE NEW TOOL DROP
                        if (ui.draggable.hasClass('mgwpp-tool-item')) {
                            const type = ui.draggable.data('type');
                            const newItem = CanvasItems.create(type, {
                                x: 0, // Reset pos for flow layout
                                y: 0
                            });

                            // Validate the new item was created successfully
                            if (!newItem) {
                                console.warn('Failed to create item of type:', type);
                                return;
                            }

                            if (!item.children) item.children = [];
                            item.children.push(newItem);

                            CanvasState.selectedItemId = newItem.id;
                            CanvasState.isDirty = true;
                            CanvasState.pushHistory();
                            CanvasEditor.refreshAll();
                            return;
                        }

                        // HANDLE EXISTING ITEM DROP (Reparenting)
                        const draggedId = ui.draggable.data('item-id');
                        if (draggedId === item.id) return;

                        // Prevent recursive drop (dropping parent into child)
                        // Simple check: if dragging container, ensure drop target is not a child of dragged
                        // This requires state traversal.
                        // For now, let's assume valid.

                        CanvasEditor.reparentItem(draggedId, item.id);
                    }
                });
            }
        },

        // Helper: Extract YouTube video ID from various URL formats
        extractYouTubeId: function (url) {
            if (!url || typeof url !== 'string') return null;
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        },

        // Helper: Extract Vimeo video ID from various URL formats
        extractVimeoId: function (url) {
            if (!url || typeof url !== 'string') return null;
            const regExp = /(?:vimeo)\.com.*(?:videos|video|channels|)\/([\d]+)/i;
            const match = url.match(regExp);
            return match ? match[1] : null;
        },

        attachEvents: function ($el, item, isRoot) {
            const self = this;

            // Select on click
            $el.on('click', function (e) {
                e.stopPropagation();
                CanvasEditor.selectItem(item.id);
            });

            // Draggable
            // If dragging from ID #mgwpp-canvas, it works fine for root items.
            // If dragging nested items, we need to ensure they can be dragged OUT or around.
            $el.draggable({
                // containment: 'div#mgwpp-canvas', // Removed containment
                scroll: false,
                cursor: 'move',
                distance: 3,
                revert: 'invalid', // Revert if not dropped on valid droppable
                start: function (e, ui) {
                    e.stopPropagation(); // Prevent parent container from dragging
                    CanvasEditor.selectItem(item.id);
                    $el.addClass('dragging');
                },
                drag: function (e, ui) {
                    // Update visuals
                },
                stop: function (e, ui) {
                    $el.removeClass('dragging');

                    if (isRoot) {
                        item.x = ui.position.left;
                        item.y = ui.position.top;
                        CanvasState.isDirty = true;
                        CanvasState.pushHistory();
                        CanvasUI.renderProperties();
                    } else {
                        // inside container
                        $el.css({ top: 'auto', left: 'auto', position: 'relative' });
                    }
                }
            });

            // Resizable: Only enable for non-auto size or check logic
            $el.resizable({
                handles: 'n, e, s, w, ne, se, sw, nw',
                start: function (e) {
                    e.stopPropagation();
                    CanvasEditor.selectItem(item.id);
                },
                stop: function (e, ui) {
                    item.width = ui.size.width;
                    item.height = ui.size.height;

                    if (isRoot) {
                        item.x = ui.position.left;
                        item.y = ui.position.top;
                    }

                    CanvasState.isDirty = true;
                    CanvasState.pushHistory();
                    CanvasUI.renderProperties();
                }
            });
        }
    };



    // ==========================================
    // MODULE: Canvas Layers
    // ==========================================
    const CanvasLayers = {
        list: null,

        init: function () {
            this.list = $('#mgwpp-layers-list');
        },

        render: function () {
            this.list.empty();
            const items = CanvasState.getItems();
            // Show highest z-index at top of list
            const sortedItems = [...items].sort((a, b) => b.z_index - a.z_index);

            if (sortedItems.length === 0) {
                this.list.html('<div style="padding:15px;text-align:center;color:#666;font-size:12px;">No items on this slide</div>');
                return;
            }

            this.renderRecursive(sortedItems, 0);
        },

        renderRecursive: function (items, level) {
            items.forEach(item => {
                const isActive = (item.id === CanvasState.selectedItemId) ? 'active' : '';
                const icon = this.getIcon(item);
                const name = this.getName(item);
                const indent = level * 15; // Indent for nested items

                const $layer = $(`
                    <div class="mgwpp-layer-item ${isActive}" data-id="${item.id}" style="padding-left:${10 + indent}px">
                        <div class="mgwpp-layer-icon">${icon}</div>
                        <div class="mgwpp-layer-name">${name}</div>
                        <div class="mgwpp-layer-actions">
                            <span class="dashicons dashicons-trash mgwpp-delete-layer" title="Delete"></span>
                        </div>
                    </div>
                `);

                $layer.on('click', (e) => {
                    e.stopPropagation();
                    CanvasEditor.selectItem(item.id);
                });
                $layer.find('.mgwpp-delete-layer').on('click', (e) => {
                    e.stopPropagation();
                    CanvasEditor.deleteItem(item.id);
                });

                this.list.append($layer);

                // Recursive render for children
                if (item.children && item.children.length > 0) {
                    // Sort children by z-index too? Or DOM order?
                    // Usually DOM order (flex) matches rendered order.
                    // Let's rely on array order for now, or sort if needed.
                    const children = [...item.children].sort((a, b) => b.z_index - a.z_index);
                    this.renderRecursive(children, level + 1);
                }
            });
        },

        getIcon: function (item) {
            const map = {
                'image': 'format-image',
                'video': 'video-alt3',
                'text': 'editor-textcolor',
                'button': 'button',
                'shape': 'admin-customizer',
                'container': 'layout'
            };
            return `<span class="dashicons dashicons-${map[item.type] || 'admin-generic'}"></span>`;
        },

        getName: function (item) {
            if (item.type === 'image') return 'Image ' + item.id.substr(-4);
            if (item.type === 'video') return 'Video ' + item.id.substr(-4);
            if (item.type === 'text') return (item.content || 'Text').substring(0, 15);
            if (item.type === 'container') return 'Container ' + item.id.substr(-4);
            if (item.type === 'button') return item.text || 'Button';
            return item.type.charAt(0).toUpperCase() + item.type.slice(1);
        }
    };

    // ==========================================
    // MODULE: Canvas UI (Sidebars, Properties)
    // ==========================================
    const CanvasUI = {
        init: function () {
            // Panels
            $('#mgwpp-toggle-left-panel').on('click', () => {
                $('#mgwpp-panel-left').toggleClass('collapsed');
                $(window).trigger('resize'); // Trigger resize for canvas centering
            });

            $('#mgwpp-toggle-right-panel').on('click', () => {
                $('#mgwpp-panel-right').toggleClass('collapsed');
                $(window).trigger('resize');
            });

            // Tabs in Properties
            $(document).on('click', '.mgwpp-prop-tab', function () {
                $('.mgwpp-prop-tab').removeClass('active');
                $(this).addClass('active');
                const tab = $(this).data('tab');
                $('.mgwpp-prop-content').removeClass('active');
                $('.mgwpp-prop-content[data-tab="' + tab + '"]').addClass('active');
            });

            // Add Element Buttons
            $('.mgwpp-add-item').on('click', function () {
                const type = $(this).data('type');
                CanvasEditor.addItem(type);
            });

            // Device Switcher
            $('.mgwpp-device-btn').on('click', function () {
                $('.mgwpp-device-btn').removeClass('active');
                $(this).addClass('active');
                CanvasEditor.setDeviceMode($(this).data('device'));
            });

            // Theme Toggle (Light/Dark Mode)
            $('#mgwpp-theme-toggle').on('click', function () {
                const $wrap = $('.mgwpp-canvas-editor-wrap');
                const $icon = $(this).find('.dashicons');

                // Cycle through: dark -> light -> auto -> dark
                if ($wrap.hasClass('light-theme')) {
                    // Switch to auto
                    $wrap.removeClass('light-theme').addClass('auto-theme');
                    $icon.removeClass('dashicons-admin-appearance').addClass('dashicons-visibility');
                    localStorage.setItem('mgwpp_canvas_theme', 'auto');
                } else if ($wrap.hasClass('auto-theme')) {
                    // Switch to dark
                    $wrap.removeClass('auto-theme');
                    $icon.removeClass('dashicons-visibility').addClass('dashicons-admin-appearance');
                    localStorage.setItem('mgwpp_canvas_theme', 'dark');
                } else {
                    // Switch to light
                    $wrap.addClass('light-theme');
                    $icon.removeClass('dashicons-admin-appearance').addClass('dashicons-lightbulb');
                    localStorage.setItem('mgwpp_canvas_theme', 'light');
                }
            });

            // Apply saved theme on load
            const savedTheme = localStorage.getItem('mgwpp_canvas_theme');
            if (savedTheme === 'light') {
                $('.mgwpp-canvas-editor-wrap').addClass('light-theme');
                $('#mgwpp-theme-toggle .dashicons').removeClass('dashicons-admin-appearance').addClass('dashicons-lightbulb');
            } else if (savedTheme === 'auto') {
                $('.mgwpp-canvas-editor-wrap').addClass('auto-theme');
                $('#mgwpp-theme-toggle .dashicons').removeClass('dashicons-admin-appearance').addClass('dashicons-visibility');
            }

            // History
            $('#mgwpp-undo').on('click', () => CanvasState.undo());
            $('#mgwpp-redo').on('click', () => CanvasState.redo());

            // Save
            $('#mgwpp-save-canvas').on('click', () => CanvasEditor.save());

            // Icon Toggles (Alignment etc)
            $(document).on('click', '.mgwpp-icon-toggle', function () {
                const $btn = $(this);
                const prop = $btn.closest('.mgwpp-prop-group').data('prop'); // e.g., 'textAlign'
                const val = $btn.data('value');

                // Visual update
                $btn.siblings().removeClass('active');
                $btn.addClass('active');

                // Update item
                if (prop) {
                    CanvasEditor.updateItemProperty(CanvasState.selectedItemId, prop, val);
                }
            });

            // Unit Selector Buttons (px, %, em, rem)
            $(document).on('click', '.mgwpp-unit-btn', function () {
                const $btn = $(this);
                const $selector = $btn.closest('.mgwpp-unit-selector');
                const $input = $selector.siblings('.mgwpp-dim-value');
                const prop = $input.data('prop');
                const unit = $btn.data('unit');

                // Visual update
                $selector.find('.mgwpp-unit-btn').removeClass('active');
                $btn.addClass('active');
                $selector.data('current', unit);

                // Update item value with new unit
                const numVal = $input.val();
                const item = CanvasState.getItem(CanvasState.selectedItemId);
                if (item && prop) {
                    item[prop] = numVal + unit;
                    CanvasState.isDirty = true;
                    CanvasRenderer.render();
                }
            });
        },

        bindDeviceSwitcher: function () {
            $('.mgwpp-device-btn').on('click', function () {
                $('.mgwpp-device-btn').removeClass('active');
                $(this).addClass('active');

                const device = $(this).data('device');
                const $viewport = $('.mgwpp-canvas-viewport');

                // Remove existing
                $viewport.removeClass('mobile-view tablet-view desktop-view');

                // Add new
                if (device !== 'desktop') {
                    $viewport.addClass(device + '-view');
                }
            });
        },

        bindToolbar: function () {
            $('.mgwpp-add-item').on('click', function () {
                const type = $(this).data('type');
                CanvasEditor.addItem(type);
            });
        },

        bindTopBar: function () {
            // Save
            $('#mgwpp-save-canvas').on('click', () => CanvasEditor.save());

            // Undo/Redo
            $('#mgwpp-undo').on('click', () => { if (CanvasState.undo()) CanvasRenderer.render(); });
            $('#mgwpp-redo').on('click', () => { if (CanvasState.redo()) CanvasRenderer.render(); });

            // Deselect on canvas click
            $('#mgwpp-canvas').on('click', function (e) {
                if (e.target === this) CanvasEditor.deselectAll();
            });
        },

        bindSlidesPanel: function () {
            // Render initial slides strip
            this.renderSlidesStrip();

            // Add Slide Button Logic is inside renderSlidesStrip usually or static HTML?
            // Assuming we need to add a button dynamically if not present
        },

        renderSlidesStrip: function () {
            const $strip = $('#mgwpp-slides-strip');
            $strip.empty();

            const slides = CanvasState.data.slides;

            slides.forEach((slide, index) => {
                const isActive = (index === CanvasState.activeSlideIndex) ? 'active' : '';
                const $slide = $(`
                    <div class="mgwpp-slide-thumb ${isActive}" data-index="${index}">
                        <span class="mgwpp-slide-num">${index + 1}</span>
                        <div class="mgwpp-slide-actions">
                            <span class="dashicons dashicons-trash mgwpp-delete-slide" title="Delete Slide"></span>
                        </div>
                    </div>
                `);

                $slide.on('click', () => CanvasEditor.switchSlide(index));
                $slide.find('.mgwpp-delete-slide').on('click', (e) => {
                    e.stopPropagation();
                    CanvasEditor.deleteSlide(index);
                });

                $strip.append($slide);
            });

            // Add New Slide Button
            const $addBtn = $('<div class="mgwpp-slide-add"><span class="dashicons dashicons-plus"></span></div>');
            $addBtn.on('click', () => CanvasEditor.addSlide());
            $strip.append($addBtn);
        },

        updateUndoRedoButtons: function () {
            $('#mgwpp-undo').prop('disabled', CanvasState.historyIndex <= 0);
            $('#mgwpp-redo').prop('disabled', CanvasState.historyIndex >= CanvasState.history.length - 1);
        },

        renderProperties: function () {
            const $container = $('#mgwpp-properties-content');
            const item = CanvasState.getItem(CanvasState.selectedItemId);

            if (!item) {
                this.renderGlobalSettings();
                return;
            }

            // Generate fields for Item Properties
            let html = '<div class="mgwpp-property-group"><h4>' + item.type.toUpperCase() + ' Properties</h4>';

            // Common Geometry - with unit selectors (px, %, em, rem)
            html += this.buildDimensionField('X Position', item.x, 'x');
            html += this.buildDimensionField('Y Position', item.y, 'y');
            html += this.buildDimensionField('Width', item.width, 'width');
            html += this.buildDimensionField('Height', item.height, 'height');
            html += '</div>';

            html += '<div class="mgwpp-property-group"><h4>Style</h4>';
            html += this.buildField('range', 'Opacity', item.opacity, 'opacity', { min: 0, max: 1, step: 0.1 });
            html += this.buildField('number', 'Rotation', item.rotation, 'rotation');
            html += this.buildField('number', 'Layer Z-Index', item.z_index, 'z_index');
            html += '</div>';

            // Specifics
            html += '<div class="mgwpp-property-group"><h4>Specifics</h4>';

            if (item.type === 'text') {
                html += this.buildField('textarea', 'Content', item.content, 'content');
                html += this.buildField('text', 'Font Size', item.font_size, 'font_size');
                html += this.buildField('color', 'Color', item.color, 'color');
                html += this.buildSelect('Align', item.text_align, 'text_align', ['left', 'center', 'right']);
            }
            else if (item.type === 'button') {
                html += this.buildField('text', 'Label', item.text, 'text');
                html += this.buildField('text', 'Link URL', item.link || '#', 'link');
                html += this.buildField('color', 'BG Color', item.bg_color, 'bg_color');
                html += this.buildField('color', 'Text Color', item.text_color, 'text_color');
                html += this.buildDimensionField('Border Radius', item.border_radius, 'border_radius');
            }
            else if (item.type === 'shape') {
                html += this.buildSelect('Shape', item.shape_type, 'shape_type', ['rectangle', 'circle']);
                html += this.buildField('color', 'Fill', item.fill_color, 'fill_color');
                html += this.buildField('color', 'Stroke', item.stroke_color, 'stroke_color');
                html += this.buildField('text', 'Stroke Width', item.stroke_width, 'stroke_width');
            }
            else if (item.type === 'image') {
                html += '<button class="button mgwpp-change-image" style="width:100%;margin-bottom:10px;">Change Image</button>';
                html += this.buildField('text', 'Alt Text', item.alt_text, 'alt_text');
                html += this.buildSelect('Object Fit', item.object_fit || 'cover', 'object_fit', ['cover', 'contain', 'fill', 'none']);
            }
            else if (item.type === 'video') {
                html += this.buildSelect('Source Type', item.source_type, 'source_type', ['upload', 'youtube', 'vimeo']);

                if (item.source_type === 'upload') {
                    html += '<button class="button mgwpp-change-video" style="width:100%;margin-bottom:10px;">Select Video</button>';
                    if (item.video_url) {
                        html += '<p style="font-size:11px;color:#888;margin-bottom:10px;word-break:break-all;">' + item.video_url.split('/').pop() + '</p>';
                    }
                    html += '<button class="button mgwpp-change-poster" style="width:100%;margin-bottom:10px;">Set Poster Image</button>';
                } else {
                    html += this.buildField('text', 'Video URL', item.embed_url || '', 'embed_url');
                }

                html += '</div><div class="mgwpp-property-group"><h4>Video Options</h4>';
                html += this.buildCheckbox('Autoplay', item.autoplay, 'autoplay');
                html += this.buildCheckbox('Loop', item.loop, 'loop');
                html += this.buildCheckbox('Muted', item.muted, 'muted');
                html += this.buildCheckbox('Show Controls', item.controls, 'controls');
            }
            else if (item.type === 'container') {
                // Layout Section
                html += this.buildSelect('Direction', item.direction, 'direction', ['row', 'column']);
                html += this.buildSelect('Justify', item.justify, 'justify', ['flex-start', 'center', 'flex-end', 'space-between', 'space-around']);
                html += this.buildSelect('Align', item.align, 'align', ['flex-start', 'center', 'flex-end', 'stretch']);
                html += this.buildField('text', 'Gap', item.gap, 'gap');
                html += this.buildField('text', 'Padding', item.padding, 'padding');
                html += this.buildField('text', 'Border Radius', item.border_radius || 0, 'border_radius');
                html += this.buildField('color', 'Border Color', item.border_color, 'border_color');
                html += this.buildField('text', 'Border Width', item.border_width, 'border_width');

                // Background Section (Elementor/WPBakery-like)
                html += '</div><div class="mgwpp-property-group"><h4>Background</h4>';
                html += this.buildSelect('Background Type', item.bg_type || 'color', 'bg_type', ['color', 'image', 'video']);
                html += this.buildField('color', 'Background Color', item.bg_color, 'bg_color');

                if (item.bg_type === 'image') {
                    html += '<button class="button mgwpp-change-bg-image" style="width:100%;margin-bottom:10px;">Set Background Image</button>';
                    if (item.bg_image_url) {
                        html += '<div style="width:100%;height:60px;margin-bottom:10px;background-image:url(' + item.bg_image_url + ');background-size:cover;background-position:center;border-radius:4px;"></div>';
                    }
                    html += this.buildSelect('Size', item.bg_size || 'cover', 'bg_size', ['cover', 'contain', 'auto']);
                    html += this.buildSelect('Position', item.bg_position || 'center center', 'bg_position', ['center center', 'top center', 'bottom center', 'left center', 'right center']);
                } else if (item.bg_type === 'video') {
                    html += '<button class="button mgwpp-change-bg-video" style="width:100%;margin-bottom:10px;">Set Background Video</button>';
                    if (item.bg_video_url) {
                        html += '<p style="font-size:11px;color:#888;margin-bottom:10px;word-break:break-all;">' + item.bg_video_url.split('/').pop() + '</p>';
                    }
                }

                // Overlay for image/video backgrounds
                if (item.bg_type === 'image' || item.bg_type === 'video') {
                    html += '</div><div class="mgwpp-property-group"><h4>Overlay</h4>';
                    html += this.buildField('color', 'Overlay Color', item.bg_overlay_color || '', 'bg_overlay_color');
                    html += this.buildField('range', 'Overlay Opacity', item.bg_overlay_opacity || 0.5, 'bg_overlay_opacity', { min: 0, max: 1, step: 0.1 });
                }
            }

            html += '</div>';

            // Delete button
            html += '<div class="mgwpp-property-group"><button class="button mgwpp-delete-item-btn" style="width:100%;background:#ff4757;color:#fff;border:none;">Delete Element</button></div>';

            $container.html(html);

            // Bind Events
            const self = this;
            $container.find('input, select, textarea').on('input change', (e) => {
                const prop = $(e.target).data('prop');
                let val = $(e.target).val();

                // Handle checkboxes
                if ($(e.target).attr('type') === 'checkbox') {
                    val = $(e.target).is(':checked');
                }
                // Conversions for numbers
                else if ($(e.target).attr('type') === 'number' || $(e.target).attr('type') === 'range') {
                    val = parseFloat(val);
                } else {
                    // Try to sanitize dimensions if it matches a dimension prop
                    val = CanvasUtils.sanitizeInput(val);
                }

                item[prop] = val;
                CanvasState.isDirty = true;
                CanvasRenderer.render(); // Re-render to show changes immediately

                // Re-render properties panel when bg_type or source_type changes
                // so the correct input fields appear
                if (prop === 'bg_type' || prop === 'source_type') {
                    self.renderProperties();
                }
            });

            // Special handlers - Image
            $container.find('.mgwpp-change-image').on('click', () => {
                CanvasEditor.openMediaLibrary((attachment) => {
                    item.image_url = attachment.url;
                    item.image_id = attachment.id;
                    CanvasState.isDirty = true;
                    CanvasRenderer.render();
                    this.renderProperties(); // Refresh properties panel
                });
            });

            // Special handlers - Video
            $container.find('.mgwpp-change-video').on('click', () => {
                CanvasEditor.openMediaLibrary((attachment) => {
                    item.video_url = attachment.url;
                    item.video_id = attachment.id;
                    CanvasState.isDirty = true;
                    CanvasRenderer.render();
                    this.renderProperties();
                }, 'video');
            });

            $container.find('.mgwpp-change-poster').on('click', () => {
                CanvasEditor.openMediaLibrary((attachment) => {
                    item.poster_url = attachment.url;
                    item.poster_id = attachment.id;
                    CanvasState.isDirty = true;
                    CanvasRenderer.render();
                    this.renderProperties();
                });
            });

            // Special handlers - Container Background Image
            $container.find('.mgwpp-change-bg-image').on('click', () => {
                CanvasEditor.openMediaLibrary((attachment) => {
                    item.bg_image_url = attachment.url;
                    item.bg_image_id = attachment.id;
                    CanvasState.isDirty = true;
                    CanvasRenderer.render();
                    this.renderProperties();
                });
            });

            // Special handlers - Container Background Video
            $container.find('.mgwpp-change-bg-video').on('click', () => {
                CanvasEditor.openMediaLibrary((attachment) => {
                    item.bg_video_url = attachment.url;
                    item.bg_video_id = attachment.id;
                    CanvasState.isDirty = true;
                    CanvasRenderer.render();
                    this.renderProperties();
                }, 'video');
            });

            // Delete handler
            $container.find('.mgwpp-delete-item-btn').on('click', () => {
                if (confirm('Are you sure you want to delete this element?')) {
                    CanvasEditor.deleteItem(item.id);
                }
            });
        },

        renderGlobalSettings: function () {
            const $container = $('#mgwpp-properties-content');
            const settings = CanvasState.data.sliderSettings;
            const canvasSettings = CanvasState.data.canvas_settings || { width: 1200, height: 800, background: '#ffffff' };

            let html = '<div class="mgwpp-property-group"><h4>Canvas & Slider</h4>';
            html += '<p style="color:#888;font-size:12px;margin-bottom:10px;">No item selected. Editing Global Settings.</p>';

            // Canvas Dimensions
            html += this.buildField('number', 'Canvas Width', canvasSettings.width, 'c_width');
            html += this.buildField('number', 'Canvas Height', canvasSettings.height, 'c_height');
            html += this.buildField('color', 'Background', canvasSettings.background, 'c_bg');

            html += '<hr style="margin:15px 0;border:0;border-top:1px solid #ddd;">';

            // Slider Settings
            html += this.buildSelect('Effect', settings.effect, 's_effect', ['slide', 'fade', 'cube', 'coverflow']);
            html += this.buildCheckbox('Autoplay', settings.autoplay, 's_autoplay');
            html += this.buildField('number', 'Speed (ms)', settings.autoplaySpeed, 's_speed');
            html += this.buildCheckbox('Show Arrows', settings.arrows, 's_arrows');
            html += this.buildCheckbox('Show Dots', settings.dots, 's_dots');

            html += '</div>';
            $container.html(html);

            // Bind Events
            $container.find('input, select').on('input change', (e) => {
                const prop = $(e.target).data('prop');
                let val = $(e.target).attr('type') === 'checkbox' ? $(e.target).is(':checked') : $(e.target).val();

                // Map back to structure
                if (prop === 'c_width') { canvasSettings.width = parseInt(val); CanvasState.data.canvas_settings = canvasSettings; }
                else if (prop === 'c_height') { canvasSettings.height = parseInt(val); CanvasState.data.canvas_settings = canvasSettings; }
                else if (prop === 'c_bg') { canvasSettings.background = val; CanvasState.data.canvas_settings = canvasSettings; }
                else if (prop === 's_effect') settings.effect = val;
                else if (prop === 's_autoplay') settings.autoplay = val;
                else if (prop === 's_speed') settings.autoplaySpeed = parseInt(val);
                else if (prop === 's_arrows') settings.arrows = val;
                else if (prop === 's_dots') settings.dots = val;

                CanvasState.isDirty = true;
                CanvasRenderer.render(); // Re-render canvas size/bg
            });
        },

        buildField: function (type, label, value, prop, attrs = {}) {
            let attrStr = '';
            for (let k in attrs) attrStr += `${k}="${attrs[k]}" `;
            return `
                <div class="mgwpp-property-row">
                    <label>${label}</label>
                    <input type="${type}" data-prop="${prop}" value="${value}" ${attrStr}>
                </div>
            `;
        },

        // Dimension field with unit selector (px, %, em, rem)
        buildDimensionField: function (label, value, prop) {
            // Parse current value to extract number and unit
            let numVal = value;
            let unit = 'px';

            if (typeof value === 'string') {
                const match = value.match(/^([\d.]+)(px|%|em|rem|vw|vh)?$/);
                if (match) {
                    numVal = match[1];
                    unit = match[2] || 'px';
                }
            }

            return `
                <div class="mgwpp-property-row mgwpp-dimension-row">
                    <label>${label}</label>
                    <div class="mgwpp-dimension-input">
                        <input type="text" data-prop="${prop}" value="${numVal}" class="mgwpp-dim-value">
                        <div class="mgwpp-unit-selector" data-current="${unit}">
                            <button type="button" class="mgwpp-unit-btn ${unit === 'px' ? 'active' : ''}" data-unit="px">px</button>
                            <button type="button" class="mgwpp-unit-btn ${unit === '%' ? 'active' : ''}" data-unit="%">%</button>
                            <button type="button" class="mgwpp-unit-btn ${unit === 'em' ? 'active' : ''}" data-unit="em">em</button>
                            <button type="button" class="mgwpp-unit-btn ${unit === 'rem' ? 'active' : ''}" data-unit="rem">rem</button>
                        </div>
                    </div>
                </div>
            `;
        },

        buildSelect: function (label, value, prop, options) {
            let opts = options.map(o => `<option value="${o}" ${o === value ? 'selected' : ''}>${o.charAt(0).toUpperCase() + o.slice(1)}</option>`).join('');
            return `
                <div class="mgwpp-property-row">
                    <label>${label}</label>
                    <select data-prop="${prop}">${opts}</select>
                </div>
            `;
        },

        buildCheckbox: function (label, checked, prop) {
            return `
                <div class="mgwpp-property-row">
                    <label>${label}</label>
                    <input type="checkbox" data-prop="${prop}" ${checked ? 'checked' : ''}>
                </div>
            `;
        }
    };

    // ==========================================
    // MODULE: Canvas Editor Controller
    // ==========================================
    const CanvasEditor = {
        init: function () {
            CanvasState.init();
            CanvasRenderer.init();
            CanvasLayers.init();
            CanvasUI.init();

            this.loadData();
        },

        loadData: function () {
            // Ajax Load
            $.ajax({
                url: mgwppCanvas.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mgwpp_load_canvas',
                    nonce: mgwppCanvas.nonce,
                    canvas_id: mgwppCanvas.canvasId
                },
                success: (res) => {
                    if (res.success) {
                        const d = res.data.data;

                        // Parse Slides
                        if (d.slides && Array.isArray(d.slides)) {
                            CanvasState.data.slides = d.slides;
                        } else {
                            // Legacy Convert
                            CanvasState.data.slides = [{ id: 'slide_1', items: d.items || [] }];
                        }

                        // Parse Settings
                        if (d.slider_settings) {
                            CanvasState.data.sliderSettings = $.extend(CanvasState.data.sliderSettings, d.slider_settings);
                        }
                        if (d.canvas_settings) {
                            CanvasState.data.canvas_settings = d.canvas_settings;
                        }

                        // Ensure 1 slide
                        if (CanvasState.data.slides.length === 0) {
                            CanvasState.data.slides.push({ id: 'slide_' + Date.now(), items: [] });
                        }

                        CanvasState.activeSlideIndex = 0;
                        CanvasState.pushHistory(); // Initial state

                        this.refreshAll();
                    }
                }
            });
        },

        save: function () {
            const $btn = $('#mgwpp-save-canvas');
            $btn.text('Saving...').prop('disabled', true);

            const payload = {
                slides: CanvasState.data.slides,
                slider_settings: CanvasState.data.sliderSettings,
                canvas_settings: CanvasState.data.canvas_settings
            };

            $.ajax({
                url: mgwppCanvas.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mgwpp_save_canvas',
                    nonce: mgwppCanvas.nonce,
                    canvas_id: mgwppCanvas.canvasId,
                    title: $('#mgwpp-canvas-title').val(),
                    canvas_data: JSON.stringify(payload)
                },
                success: (res) => {
                    $btn.text('Saved!').prop('disabled', false);
                    CanvasState.isDirty = false;
                    setTimeout(() => $btn.html('<span class="dashicons dashicons-saved"></span> Save'), 2000);
                },
                error: () => {
                    $btn.text('Error');
                    setTimeout(() => $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save'), 2000);
                }
            });
        },

        addItem: function (type) {
            const newItem = CanvasItems.create(type);

            // Validate the item was created successfully
            if (!newItem) {
                console.warn('Failed to create item of type:', type);
                return;
            }

            CanvasState.getItems().push(newItem);

            CanvasState.selectedItemId = newItem.id;
            CanvasState.isDirty = true;
            CanvasState.pushHistory();

            this.refreshAll();
        },

        deleteItem: function (id) {
            const slide = CanvasState.getCurrentSlide();

            // Try to find parent first
            const parent = CanvasState.findParent(slide.items, id);

            if (parent) {
                // Remove from parent's children
                parent.children = parent.children.filter(i => i.id !== id);
            } else {
                // Must be root level
                slide.items = slide.items.filter(i => i.id !== id);
            }

            if (CanvasState.selectedItemId === id) CanvasState.selectedItemId = null;

            CanvasState.isDirty = true;
            CanvasState.pushHistory();
            this.refreshAll();
        },

        reparentItem: function (itemId, newParentId) {
            const slide = CanvasState.getCurrentSlide();
            const item = CanvasState.getItem(itemId);
            if (!item) return;

            // Remove from old parent
            const oldParent = CanvasState.findParent(slide.items, itemId);
            if (oldParent) {
                oldParent.children = oldParent.children.filter(i => i.id !== itemId);
            } else {
                // Root
                slide.items = slide.items.filter(i => i.id !== itemId);
            }

            // Add to new parent
            if (newParentId === null || newParentId === 'root') {
                slide.items.push(item);
                // Update item props for root
                item.x = 100;
                item.y = 100;
            } else {
                const newParent = CanvasState.getItem(newParentId);
                if (newParent && newParent.type === 'container') {
                    if (!newParent.children) newParent.children = [];
                    newParent.children.push(item);
                    // Reset props for container
                    item.x = 0;
                    item.y = 0;
                }
            }

            CanvasState.isDirty = true;
            CanvasState.pushHistory();
            this.refreshAll();
        },

        selectItem: function (id) {
            CanvasState.selectedItemId = id;

            // OPTIMIZATION: Do not full re-render. Just toggle classes.
            $('.mgwpp-canvas-item').removeClass('selected');
            const $el = $('.mgwpp-canvas-item[data-item-id="' + id + '"]');
            $el.addClass('selected');

            // Move selected to TOP visually (z-index is handled by CSS .selected but DOM order helps)
            // But changing DOM order might kill drag if we move it?
            // Actually, CSS z-index: 1000 !important on .selected (added previously) is sufficient.

            CanvasLayers.render();   // Highlight layer
            CanvasUI.renderProperties();
        },

        deselectAll: function () {
            CanvasState.selectedItemId = null;
            $('.mgwpp-canvas-item').removeClass('selected');

            // CanvasRenderer.render(); // Avoid full re-render
            CanvasLayers.render();
            CanvasUI.renderProperties(); // Shows global settings
        },

        addSlide: function () {
            CanvasState.data.slides.push({
                id: 'slide_' + Date.now(),
                items: []
            });
            CanvasState.activeSlideIndex = CanvasState.data.slides.length - 1;
            this.refreshAll();
        },

        switchSlide: function (index) {
            CanvasState.activeSlideIndex = index;
            CanvasState.selectedItemId = null;
            this.refreshAll();
        },

        deleteSlide: function (index) {
            if (CanvasState.data.slides.length <= 1) {
                alert("Cannot delete the only slide.");
                return;
            }
            if (!confirm("Delete this slide?")) return;

            CanvasState.data.slides.splice(index, 1);
            if (CanvasState.activeSlideIndex >= CanvasState.data.slides.length) {
                CanvasState.activeSlideIndex = CanvasState.data.slides.length - 1;
            }
            this.refreshAll();
        },

        refreshAll: function () {
            CanvasRenderer.render();
            CanvasLayers.render();
            CanvasUI.renderSlidesStrip();
            CanvasUI.renderProperties();
        },

        openMediaLibrary: function (cb, mediaType = 'image') {
            const isVideo = mediaType === 'video';
            const frame = wp.media({
                title: isVideo ? 'Select Video' : 'Select Image',
                button: { text: isVideo ? 'Use Video' : 'Use Image' },
                multiple: false,
                library: { type: mediaType }
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                cb(attachment);
            });

            frame.open();
        }
    };

    // ==========================================
    // MODULE: Canvas List Page
    // ==========================================
    // ==========================================
    // MODULE: Canvas List Page
    // ==========================================
    const CanvasListPage = {
        init: function () {
            // Bindings
            $('#mgwpp-new-canvas-btn').on('click', () => this.openCreateModal());
            $('.mgwpp-modal-close, .mgwpp-modal-cancel').on('click', () => this.closeModals());
            $('#mgwpp-create-confirm').on('click', () => this.handleCreate());

            // Delete & Preview bindings
            $('.mgwpp-delete-canvas').on('click', this.deleteCanvas.bind(this));
            $('.mgwpp-preview-canvas').on('click', this.openPreviewModal.bind(this));
            $('.mgwpp-copy-canvas-shortcode').on('click', this.copyShortcode.bind(this));

            // Theme toggle for list page
            $('#mgwpp-list-theme-toggle').on('click', () => this.toggleTheme());

            // Apply saved theme on load
            this.loadSavedTheme();

            // Close modal on outside click
            $(window).on('click', (e) => {
                if ($(e.target).hasClass('mgwpp-modal-overlay')) {
                    this.closeModals();
                }
            });
        },

        toggleTheme: function () {
            const $wrap = $('#mgwpp-canvas-list-wrap');
            const $btn = $('#mgwpp-list-theme-toggle');

            if ($wrap.hasClass('light-mode')) {
                $wrap.removeClass('light-mode');
                $btn.find('.dashicons').removeClass('dashicons-lightbulb').addClass('dashicons-admin-appearance');
                localStorage.setItem('mgwpp_list_theme', 'dark');
            } else {
                $wrap.addClass('light-mode');
                $btn.find('.dashicons').removeClass('dashicons-admin-appearance').addClass('dashicons-lightbulb');
                localStorage.setItem('mgwpp_list_theme', 'light');
            }
        },

        loadSavedTheme: function () {
            const savedTheme = localStorage.getItem('mgwpp_list_theme');
            if (savedTheme === 'light') {
                $('#mgwpp-canvas-list-wrap').addClass('light-mode');
                $('#mgwpp-list-theme-toggle .dashicons').removeClass('dashicons-admin-appearance').addClass('dashicons-lightbulb');
            }
        },

        openCreateModal: function () {
            $('#mgwpp-new-title').val('');
            $('#mgwpp-create-modal').css('display', 'flex').hide().fadeIn(200);
            $('#mgwpp-new-title').focus();
        },

        closeModals: function () {
            $('.mgwpp-modal-overlay').fadeOut(200);
            $('#mgwpp-preview-content').html('<div class="mgwpp-loading-spinner"></div>'); // Reset preview
        },

        handleCreate: function () {
            const title = $('#mgwpp-new-title').val().trim();
            if (!title) {
                alert('Please enter a title');
                return;
            }

            const $btn = $('#mgwpp-create-confirm');
            $btn.prop('disabled', true).text('Creating...');

            $.ajax({
                url: mgwppCanvas.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mgwpp_create_canvas',
                    nonce: mgwppCanvas.nonce,
                    title: title
                },
                success: function (res) {
                    if (res.success) {
                        window.location.href = res.data.edit_url;
                    } else {
                        alert(res.data.message);
                        $btn.prop('disabled', false).text('Create Canvas');
                    }
                },
                error: function () {
                    alert('Server Error');
                    $btn.prop('disabled', false).text('Create Canvas');
                }
            });
        },

        openPreviewModal: function (e) {
            const $btn = $(e.currentTarget);
            const id = $btn.data('canvas-id');
            const $modal = $('#mgwpp-preview-modal');
            const $content = $('#mgwpp-preview-content');

            $modal.css('display', 'flex').hide().fadeIn(200);

            $.ajax({
                url: mgwppCanvas.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mgwpp_render_preview',
                    nonce: mgwppCanvas.nonce,
                    canvas_id: id
                },
                success: function (res) {
                    if (res.success) {
                        $content.html(res.data.html);
                        // Trigger any JS init for the slider (if script not already running)
                        // Note: If scripts were enqueued globally, it might just work. 
                        // If scripts are scoped, we might need to manually init logic.
                        // For now, let's assume global enqueue or inline script execution.
                    } else {
                        $content.html('<p style="color:red;padding:20px;">' + res.data.message + '</p>');
                    }
                },
                error: function () {
                    $content.html('<p style="color:red;padding:20px;">Error loading preview.</p>');
                }
            });
        },

        deleteCanvas: function (e) {
            if (!confirm(mgwppCanvas.i18n.confirmDelete)) return;
            const $btn = $(e.currentTarget);
            const id = $btn.data('canvas-id');
            // Assuming card structure: btn -> card-actions -> card-preview -> canvas-card
            const $card = $btn.closest('.mgwpp-canvas-card');

            $.ajax({
                url: mgwppCanvas.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mgwpp_delete_canvas',
                    nonce: mgwppCanvas.nonce,
                    canvas_id: id
                },
                success: function (res) {
                    if (res.success) $card.fadeOut(function () { $(this).remove(); });
                }
            });
        },

        copyShortcode: function (e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const shortcode = $btn.data('shortcode');

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shortcode).then(function () {
                    $btn.find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
                    setTimeout(() => $btn.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard'), 1500);
                });
            } else {
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(shortcode).select();
                document.execCommand('copy');
                $temp.remove();
                $btn.find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
                setTimeout(() => $btn.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard'), 1500);
            }
        }
    };

    // ==========================================
    // MODULE: Core Entry
    // ==========================================
    const CanvasCore = {
        init: function () {
            if ($('#mgwpp-canvas').length) {
                CanvasEditor.init();
            } else {
                CanvasListPage.init();
            }
        }
    };

    // Start
    $(document).ready(function () {
        CanvasCore.init();
    });

})(jQuery);
