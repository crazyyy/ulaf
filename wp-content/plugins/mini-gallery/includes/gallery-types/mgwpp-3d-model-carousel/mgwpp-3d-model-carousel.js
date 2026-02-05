/**
 * Mini Gallery 3D Model Marquee Grid
 * Multiple 3D models in auto-scrolling grid with lazy loading
 * Features: Auto-rotation, lazy loading, draggable, hover focus, clickable, model rotation
 */

class MGWPP3DModelMarquee {
    constructor(container) {
        this.container = container;
        this.modelsData = this.loadModelsData();

        // Viewport instances - tracks which models are currently visible
        this.viewportInstances = new Map(); // canvas element -> instance data
        this.modelPool = new Map(); // model index -> loaded model cache

        // Settings
        this.settings = JSON.parse(container.getAttribute('data-settings') || '{}');
        this.rotationAngle = 75 * (Math.PI / 180); // 75 degrees in radians
        this.rotationSpeed = 0.005;

        // Marquee settings
        this.marqueeSpeed = this.settings.marquee_speed || 1; // pixels per frame
        this.scrollPosition = 0;
        this.isPaused = false;
        this.hoveredItem = null;

        // Drag to scroll
        this.isDragging = false;
        this.startX = 0;
        this.scrollLeft = 0;
        this.velocity = 0;
        this.lastX = 0;
        this.lastTime = 0;

        // Model interaction
        this.isModelDragging = false;
        this.activeCanvas = null;

        // Performance
        this.maxLoadedModels = 8; // Keep max 8 models in memory
        this.isInitialized = false;

        // Animation frame
        this.animationFrameId = null;

        this.init();
    }

    /**
     * Initialize the marquee
     */
    async init() {
        try {
            if (!this.checkWebGL()) {
                this.showError('WebGL is not supported in your browser.');
                return;
            }

            await this.waitForThreeJS();

            // Duplicate models for seamless loop
            this.duplicateModelsForLoop();

            // Setup grid and canvases
            this.setupGrid();

            // Setup intersection observer for lazy loading
            this.setupIntersectionObserver();

            // Setup event listeners
            this.setupEventListeners();

            // Start animation loop
            this.animate();

            this.isInitialized = true;

        } catch (error) {
            console.error('Failed to initialize 3D marquee:', error);
            this.showError(`Failed to initialize: ${error.message}`);
        }
    }

    /**
     * Wait for Three.js to load
     */
    waitForThreeJS() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 50;

            const checkInterval = setInterval(() => {
                attempts++;
                if (typeof THREE !== 'undefined' && typeof THREE.GLTFLoader !== 'undefined' && typeof THREE.DRACOLoader !== 'undefined') {
                    clearInterval(checkInterval);
                    resolve();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    reject(new Error('Three.js failed to load'));
                }
            }, 100);
        });
    }

    /**
     * Check WebGL support
     */
    checkWebGL() {
        try {
            const canvas = document.createElement('canvas');
            return !!(window.WebGLRenderingContext &&
                (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
        } catch (e) {
            return false;
        }
    }

    /**
     * Load models data from hidden containers
     */
    loadModelsData() {
        const dataElements = this.container.querySelectorAll('.mgwpp-3d-model-data');
        const models = [];

        dataElements.forEach(element => {
            try {
                const modelData = JSON.parse(element.getAttribute('data-model'));
                models.push(modelData);
            } catch (error) {
                console.error('Failed to parse model data:', error);
            }
        });

        return models;
    }

    /**
     * Duplicate models array to create seamless loop
     */
    duplicateModelsForLoop() {
        const originalCount = this.modelsData.length;
        // Duplicate the array 2-3 times for smooth infinite scroll
        this.modelsData = [...this.modelsData, ...this.modelsData, ...this.modelsData];
        console.log(`Models duplicated: ${originalCount} -> ${this.modelsData.length}`);
    }

    /**
     * Setup grid structure
     */
    setupGrid() {
        const gridContainer = this.container.querySelector('.mgwpp-3d-grid');
        if (!gridContainer) return;

        // Clear existing content
        gridContainer.innerHTML = '';

        // Create canvas for each model
        this.modelsData.forEach((modelData, index) => {
            const item = document.createElement('div');
            item.className = 'mgwpp-3d-grid-item';
            item.setAttribute('data-model-index', index);

            // Add link if available
            if (modelData.link) {
                item.setAttribute('data-link', modelData.link);
                item.classList.add('clickable');
            }

            const canvas = document.createElement('canvas');
            canvas.className = 'mgwpp-3d-canvas';
            canvas.width = 400;
            canvas.height = 400;

            const info = document.createElement('div');
            info.className = 'mgwpp-3d-item-info';
            info.innerHTML = `
                <div class="mgwpp-3d-item-title">${modelData.title || `Model ${(index % (this.modelsData.length / 3)) + 1}`}</div>
                ${modelData.caption ? `<div class="mgwpp-3d-item-caption">${modelData.caption}</div>` : ''}
                ${modelData.link ? '<div class="mgwpp-3d-item-link">🔗 Click to view</div>' : ''}
            `;

            // Add drag hint
            const dragHint = document.createElement('div');
            dragHint.className = 'mgwpp-3d-drag-hint';
            dragHint.innerHTML = '🔄 Drag to rotate';

            item.appendChild(canvas);
            item.appendChild(info);
            item.appendChild(dragHint);
            gridContainer.appendChild(item);
        });
    }

    /**
     * Setup intersection observer for lazy loading
     */
    setupIntersectionObserver() {
        const options = {
            root: this.container,
            rootMargin: '200px', // Start loading before element is visible
            threshold: 0.01
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const canvas = entry.target.querySelector('.mgwpp-3d-canvas');
                if (!canvas) return;

                if (entry.isIntersecting) {
                    // Load model when entering viewport
                    this.loadModelForCanvas(canvas);
                } else {
                    // Unload model when leaving viewport
                    this.unloadModelFromCanvas(canvas);
                }
            });
        }, options);

        // Observe all grid items
        const items = this.container.querySelectorAll('.mgwpp-3d-grid-item');
        items.forEach(item => this.observer.observe(item));
    }

    /**
     * Load model for a specific canvas
     */
    async loadModelForCanvas(canvas) {
        if (this.viewportInstances.has(canvas)) {
            return; // Already loaded
        }

        const item = canvas.closest('.mgwpp-3d-grid-item');
        const modelIndex = parseInt(item.getAttribute('data-model-index'));
        const modelData = this.modelsData[modelIndex];

        try {
            // Create Three.js scene for this canvas
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(this.settings.background_color || '#1a1a1a');

            // Camera
            const camera = new THREE.PerspectiveCamera(
                45,
                canvas.clientWidth / canvas.clientHeight,
                0.1,
                1000
            );
            camera.position.set(4, 3, 5);
            camera.lookAt(0, 0, 0);

            // Renderer
            const renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                antialias: true,
                alpha: false,
                powerPreference: 'high-performance'
            });
            renderer.setSize(canvas.clientWidth, canvas.clientHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
            renderer.outputColorSpace = THREE.SRGBColorSpace;
            renderer.toneMapping = THREE.ACESFilmicToneMapping;

            // Lighting
            this.setupLighting(scene);

            // Load or get cached model
            const model = await this.getModel(modelIndex, modelData);
            const modelClone = model.clone();

            // Set initial rotation
            modelClone.rotation.y = this.rotationAngle;

            scene.add(modelClone);

            // Store instance
            this.viewportInstances.set(canvas, {
                scene,
                camera,
                renderer,
                model: modelClone,
                modelIndex,
                rotationSpeed: this.rotationSpeed + (Math.random() * 0.003 - 0.0015), // Slight variation
                isDragging: false,
                previousMousePosition: { x: 0, y: 0 }
            });

            // Add loading indicator removal
            item.classList.add('loaded');

        } catch (error) {
            console.error(`Failed to load model for canvas:`, error);
            item.classList.add('error');
        }
    }

    /**
     * Get model (from cache or load new)
     */
    async getModel(index, modelData) {
        // Check if model is already in pool
        if (this.modelPool.has(index)) {
            const cached = this.modelPool.get(index);
            cached.lastUsed = Date.now();
            return cached.model;
        }

        // Load new model
        const loadedModel = await this.loadGLTFModel(modelData);
        this.centerAndScaleModel(loadedModel);

        // Add to pool
        this.modelPool.set(index, {
            model: loadedModel,
            lastUsed: Date.now()
        });

        // Cleanup old models if pool is too large
        this.cleanupModelPool();

        return loadedModel;
    }

    /**
     * Load GLTF model
     */
    async loadGLTFModel(modelData) {
        return new Promise((resolve, reject) => {
            const loader = new THREE.GLTFLoader();

            // Configure DRACOLoader for compressed models
            const dracoLoader = new THREE.DRACOLoader();
            dracoLoader.setDecoderPath(mgwpp_3d_settings.draco_path || 'https://www.gstatic.com/draco/v1/decoders/');
            loader.setDRACOLoader(dracoLoader);

            loader.load(
                modelData.url,
                (gltf) => {
                    if (!gltf.scene) {
                        reject(new Error('Model scene is empty'));
                        return;
                    }
                    resolve(gltf.scene);
                },
                undefined,
                (error) => {
                    console.error('GLTFLoader error:', error);
                    reject(error);
                }
            );
        });
    }

    /**
     * Center and scale model
     */
    centerAndScaleModel(model) {
        const box = new THREE.Box3().setFromObject(model);
        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());

        const maxDim = Math.max(size.x, size.y, size.z);
        const scale = 3 / maxDim;
        model.scale.setScalar(scale);

        const newBox = new THREE.Box3().setFromObject(model);
        const newCenter = newBox.getCenter(new THREE.Vector3());
        model.position.sub(newCenter);
    }

    /**
     * Setup lighting for scene
     */
    setupLighting(scene) {
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);

        const directionalLight1 = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight1.position.set(5, 5, 5);
        scene.add(directionalLight1);

        const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.3);
        directionalLight2.position.set(-5, 3, -5);
        scene.add(directionalLight2);
    }

    /**
     * Unload model from canvas
     */
    unloadModelFromCanvas(canvas) {
        const instance = this.viewportInstances.get(canvas);
        if (!instance) return;

        // Dispose renderer
        instance.renderer.dispose();

        // Remove from viewport instances
        this.viewportInstances.delete(canvas);

        // Remove loaded class
        const item = canvas.closest('.mgwpp-3d-grid-item');
        if (item) {
            item.classList.remove('loaded');
        }
    }

    /**
     * Cleanup model pool
     */
    cleanupModelPool() {
        if (this.modelPool.size <= this.maxLoadedModels) return;

        const entries = Array.from(this.modelPool.entries());
        entries.sort((a, b) => b[1].lastUsed - a[1].lastUsed);

        // Remove oldest models
        while (this.modelPool.size > this.maxLoadedModels) {
            const [index] = entries.pop();
            const cached = this.modelPool.get(index);
            this.disposeModel(cached.model);
            this.modelPool.delete(index);
        }
    }

    /**
     * Dispose model resources
     */
    disposeModel(model) {
        model.traverse((child) => {
            if (child.geometry) {
                child.geometry.dispose();
            }
            if (child.material) {
                if (Array.isArray(child.material)) {
                    child.material.forEach(mat => mat.dispose());
                } else {
                    child.material.dispose();
                }
            }
        });
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        const grid = this.container.querySelector('.mgwpp-3d-grid');

        // Drag to scroll marquee
        this.container.addEventListener('mousedown', (e) => this.onMarqueeMouseDown(e));
        this.container.addEventListener('mousemove', (e) => this.onMarqueeMouseMove(e));
        this.container.addEventListener('mouseup', (e) => this.onMarqueeMouseUp(e));
        this.container.addEventListener('mouseleave', (e) => this.onMarqueeMouseUp(e));

        // Touch events for mobile
        this.container.addEventListener('touchstart', (e) => this.onMarqueeTouchStart(e), { passive: false });
        this.container.addEventListener('touchmove', (e) => this.onMarqueeTouchMove(e), { passive: false });
        this.container.addEventListener('touchend', (e) => this.onMarqueeTouchEnd(e));

        // Hover focus on items
        const items = this.container.querySelectorAll('.mgwpp-3d-grid-item');
        items.forEach(item => {
            item.addEventListener('mouseenter', (e) => this.onItemHover(e, item));
            item.addEventListener('mouseleave', (e) => this.onItemLeave(e, item));

            // Click to navigate
            item.addEventListener('click', (e) => this.onItemClick(e, item));

            // Model drag to rotate
            const canvas = item.querySelector('.mgwpp-3d-canvas');
            if (canvas) {
                canvas.addEventListener('mousedown', (e) => this.onModelMouseDown(e, canvas));
                canvas.addEventListener('mousemove', (e) => this.onModelMouseMove(e, canvas));
                canvas.addEventListener('mouseup', (e) => this.onModelMouseUp(e, canvas));
                canvas.addEventListener('mouseleave', (e) => this.onModelMouseUp(e, canvas));

                // Touch events for model rotation
                canvas.addEventListener('touchstart', (e) => this.onModelTouchStart(e, canvas), { passive: false });
                canvas.addEventListener('touchmove', (e) => this.onModelTouchMove(e, canvas), { passive: false });
                canvas.addEventListener('touchend', (e) => this.onModelTouchEnd(e, canvas));
            }
        });

        // Handle resize
        window.addEventListener('resize', () => this.handleResize());
    }

    /**
     * Marquee drag to scroll - Mouse down
     */
    onMarqueeMouseDown(e) {
        // Don't start marquee drag if clicking on a canvas (model interaction)
        if (e.target.classList.contains('mgwpp-3d-canvas')) {
            return;
        }

        this.isDragging = true;
        this.startX = e.pageX;
        this.scrollLeft = this.scrollPosition;
        this.lastX = e.pageX;
        this.lastTime = Date.now();
        this.velocity = 0;
        this.container.classList.add('dragging');
    }

    /**
     * Marquee drag to scroll - Mouse move
     */
    onMarqueeMouseMove(e) {
        if (!this.isDragging) return;

        e.preventDefault();
        const x = e.pageX;
        const walk = (x - this.startX) * 2; // Multiply for faster scrolling
        this.scrollPosition = this.scrollLeft - walk;

        // Calculate velocity
        const now = Date.now();
        const dt = now - this.lastTime;
        if (dt > 0) {
            this.velocity = (x - this.lastX) / dt;
        }
        this.lastX = x;
        this.lastTime = now;
    }

    /**
     * Marquee drag to scroll - Mouse up
     */
    onMarqueeMouseUp(e) {
        if (!this.isDragging) return;

        this.isDragging = false;
        this.container.classList.remove('dragging');

        // Apply momentum
        this.applyMomentum();
    }

    /**
     * Apply momentum after drag release
     */
    applyMomentum() {
        const friction = 0.95;

        const momentum = () => {
            if (Math.abs(this.velocity) < 0.01) {
                return;
            }

            this.scrollPosition -= this.velocity * 20;
            this.velocity *= friction;

            requestAnimationFrame(momentum);
        };

        momentum();
    }

    /**
     * Touch events for mobile
     */
    onMarqueeTouchStart(e) {
        if (e.target.classList.contains('mgwpp-3d-canvas')) return;

        this.isDragging = true;
        this.startX = e.touches[0].pageX;
        this.scrollLeft = this.scrollPosition;
    }

    onMarqueeTouchMove(e) {
        if (!this.isDragging) return;

        e.preventDefault();
        const x = e.touches[0].pageX;
        const walk = (x - this.startX) * 2;
        this.scrollPosition = this.scrollLeft - walk;
    }

    onMarqueeTouchEnd(e) {
        this.isDragging = false;
    }

    /**
     * Item hover - pause and focus
     */
    onItemHover(e, item) {
        this.isPaused = true;
        this.hoveredItem = item;
        item.classList.add('focused');
    }

    /**
     * Item leave - resume
     */
    onItemLeave(e, item) {
        this.isPaused = false;
        this.hoveredItem = null;
        item.classList.remove('focused');
    }

    /**
     * Item click - navigate to link
     */
    onItemClick(e, item) {
        // Don't navigate if user was dragging
        if (Math.abs(this.velocity) > 0.1) {
            return;
        }

        // Don't navigate if clicking on canvas (model interaction)
        if (e.target.classList.contains('mgwpp-3d-canvas')) {
            return;
        }

        const link = item.getAttribute('data-link');
        if (link) {
            window.open(link, '_blank');
        }
    }

    /**
     * Model mouse down - start rotating
     */
    onModelMouseDown(e, canvas) {
        e.stopPropagation(); // Prevent marquee drag

        const instance = this.viewportInstances.get(canvas);
        if (!instance) return;

        this.isModelDragging = true;
        this.activeCanvas = canvas;
        instance.isDragging = true;
        instance.previousMousePosition = {
            x: e.clientX,
            y: e.clientY
        };

        canvas.classList.add('dragging');
    }

    /**
     * Model mouse move - rotate model
     */
    onModelMouseMove(e, canvas) {
        const instance = this.viewportInstances.get(canvas);
        if (!instance || !instance.isDragging) return;

        e.stopPropagation();

        const deltaMove = {
            x: e.clientX - instance.previousMousePosition.x,
            y: e.clientY - instance.previousMousePosition.y
        };

        if (instance.model) {
            instance.model.rotation.y += deltaMove.x * 0.01;
            instance.model.rotation.x += deltaMove.y * 0.01;
        }

        instance.previousMousePosition = {
            x: e.clientX,
            y: e.clientY
        };
    }

    /**
     * Model mouse up - stop rotating
     */
    onModelMouseUp(e, canvas) {
        const instance = this.viewportInstances.get(canvas);
        if (!instance) return;

        this.isModelDragging = false;
        this.activeCanvas = null;
        instance.isDragging = false;
        canvas.classList.remove('dragging');
    }

    /**
     * Model touch events
     */
    onModelTouchStart(e, canvas) {
        e.stopPropagation();

        const instance = this.viewportInstances.get(canvas);
        if (!instance) return;

        instance.isDragging = true;
        instance.previousMousePosition = {
            x: e.touches[0].clientX,
            y: e.touches[0].clientY
        };
    }

    onModelTouchMove(e, canvas) {
        const instance = this.viewportInstances.get(canvas);
        if (!instance || !instance.isDragging) return;

        e.stopPropagation();
        e.preventDefault();

        const deltaMove = {
            x: e.touches[0].clientX - instance.previousMousePosition.x,
            y: e.touches[0].clientY - instance.previousMousePosition.y
        };

        if (instance.model) {
            instance.model.rotation.y += deltaMove.x * 0.01;
            instance.model.rotation.x += deltaMove.y * 0.01;
        }

        instance.previousMousePosition = {
            x: e.touches[0].clientX,
            y: e.touches[0].clientY
        };
    }

    onModelTouchEnd(e, canvas) {
        const instance = this.viewportInstances.get(canvas);
        if (!instance) return;

        instance.isDragging = false;
    }

    /**
     * Handle window resize
     */
    handleResize() {
        this.viewportInstances.forEach((instance, canvas) => {
            const width = canvas.clientWidth;
            const height = canvas.clientHeight;

            instance.camera.aspect = width / height;
            instance.camera.updateProjectionMatrix();
            instance.renderer.setSize(width, height);
        });
    }

    /**
     * Animation loop
     */
    animate() {
        this.animationFrameId = requestAnimationFrame(() => this.animate());

        // Update marquee scroll (if not paused and not dragging)
        if (!this.isPaused && !this.isDragging) {
            this.updateMarqueeScroll();
        }

        // Render all visible scenes
        this.viewportInstances.forEach((instance) => {
            // Rotate model only if not being dragged
            if (instance.model && !instance.isDragging) {
                instance.model.rotation.y += instance.rotationSpeed;
            }

            // Render scene
            instance.renderer.render(instance.scene, instance.camera);
        });
    }

    /**
     * Update marquee scroll position
     */
    updateMarqueeScroll() {
        const grid = this.container.querySelector('.mgwpp-3d-grid');
        if (!grid) return;

        this.scrollPosition += this.marqueeSpeed;

        // Get grid width for loop calculation
        const firstItem = grid.querySelector('.mgwpp-3d-grid-item');
        if (!firstItem) return;

        const itemWidth = firstItem.offsetWidth;
        const gap = 20; // Match CSS gap
        const totalWidth = (itemWidth + gap) * (this.modelsData.length / 3); // Original count

        // Reset when we've scrolled through one set
        if (this.scrollPosition >= totalWidth) {
            this.scrollPosition = 0;
        }

        grid.style.transform = `translateX(-${this.scrollPosition}px)`;
    }

    /**
     * Show error message
     */
    showError(message) {
        this.container.innerHTML = `
            <div class="mgwpp-3d-error">
                <div class="mgwpp-3d-error-icon">⚠️</div>
                <p>${message}</p>
            </div>
        `;
    }

    /**
     * Destroy and cleanup
     */
    destroy() {
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }

        if (this.observer) {
            this.observer.disconnect();
        }

        // Cleanup all viewport instances
        this.viewportInstances.forEach((instance, canvas) => {
            this.unloadModelFromCanvas(canvas);
        });

        // Cleanup model pool
        this.modelPool.forEach((cached) => {
            this.disposeModel(cached.model);
        });
        this.modelPool.clear();

        window.removeEventListener('resize', () => this.handleResize());
    }
}

// Initialize all marquees on page load
document.addEventListener('DOMContentLoaded', () => {
    const marquees = document.querySelectorAll('.mgwpp-3d-model-marquee');
    const instances = [];

    marquees.forEach(container => {
        try {
            const instance = new MGWPP3DModelMarquee(container);
            instances.push(instance);
        } catch (error) {
            console.error('Failed to initialize 3D marquee:', error);
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        instances.forEach(instance => {
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
        });
    });
});