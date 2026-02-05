=== Mini Gallery ===
Contributors: @omarashzeinhom
Tags: gallery, carousel, slider, 3d, shortcode
Requires at least: 6.0
Tested up to: 6.9
Version: 1.6.7
Stable tag: 1.6.7
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight and flexible WordPress gallery plugin with 17 gallery types including 3D model viewer. Works with any theme using simple shortcodes.

== Description ==

**Mini Gallery** is a simple, fast, and versatile gallery plugin that works with any WordPress theme using the standard shortcode system - the most widely accepted and compatible method for embedding content in WordPress.

**New in Version 1.6:**
* **3D Model Carousel** - Display GLTF/GLB 3D models with interactive controls
* **Enhanced security** for file uploads and 3D model handling
* **Preloader options** for better user experience
* **WebGL-powered** 3D rendering with fallback support

**Core Features:**

**17 Gallery Types:**
1. **Classic Gallery** - Traditional grid-based image gallery
2. **Full Page Slider** - Immersive full-screen slideshow
3. **Grid Gallery** - Responsive masonry-style layout
4. **Mega Slider** - Large format carousel with smooth transitions
5. **Multi Gallery** - Multiple carousels in one view
6. **Neon Carousel** - Modern carousel with neon effects
7. **Pro Carousel** - Advanced carousel with premium animations
8. **Single Gallery** - Minimalist single-image display
9. **Spotlight Carousel** - Highlighted center-focused carousel
10. **ThreeD Carousel** - 3D rotating carousel effect
11. **Testimonial Carousel** - Customer testimonials slider
12. **3D Model Carousel** - Interactive 3D model viewer (GLTF/GLB)
13. **Marquee Gallery** - Infinite scrolling marquee effect
14. **Vertical Marquee** - Vertical scrolling marquee
15. **3D Masonry Gallery** - 3D perspective masonry layout
16. **3D Horizontal Marquee** - 3D horizontal scrolling effect
17. **Canvas Editor** - Visual drag-and-drop gallery builder

**Page Builder Compatibility:**
- **Universal Shortcode Support** - Works with any page builder (Gutenberg, Elementor, Divi, WPBakery, etc.)
- Simple shortcode: `[mgwpp_gallery id="X"]`
- Works in posts, pages, widgets, and custom templates
- No plugin dependencies - pure WordPress compatibility

**Visual Canvas Editor:**
- Drag-and-drop interface for creating custom gallery layouts
- Add images, videos, text, buttons, shapes, and containers
- Real-time preview with undo/redo functionality
- Layer management and z-index control
- Responsive design controls

**Advanced Features:**
- **Analytics Dashboard** - Track gallery views and engagement
- **Album System** - Organize galleries into collections
- **Shortcode Support** - Easy embedding with `[mgwpp_gallery id="X"]`
- **Custom Post Types** - Dedicated gallery management
- **Media Library Integration** - Use existing WordPress media
- **Mobile Responsive** - Optimized for all devices
- **Lightweight** - Fast loading with minimal overhead

**3D Model Support:**
- GLTF and GLB file format support
- Interactive orbit controls
- Fullscreen viewing mode
- Thumbnail navigation
- Preloader options (spinner, progress bar, skeleton)
- WebGL fallback detection
- File size validation (10MB limit for security)

**Security Features:**
- Nonce verification for all AJAX operations
- User capability checks
- Input sanitization and validation
- Secure file upload handling
- XSS protection
- SQL injection prevention

**Developer Friendly:**
- Well-documented code
- WordPress coding standards compliant
- Extensible architecture
- Custom hooks and filters
- Translation ready (i18n)

🖼️ **Website:** [https://minigallery.andgowebsolutions.com](https://minigallery.andgowebsolutions.com)  
🎯 **Live Demo:** [https://minigallery.andgowebsolutions.com](https://minigallery.andgowebsolutions.com)

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to Gallery > Add New to create your first gallery.
4. Select a gallery type, add images from your Media Library.
5. Copy the shortcode `[mgwpp_gallery id="X"]` and paste it anywhere in your site.
6. For 3D models: Upload GLTF/GLB files through Media Library and select "3D Model Carousel" as gallery type.

== Changelog ==

= 1.6.7 =
* Fixed album editing "link expired" error caused by incorrect edit URL format

= 1.6.6 =
* Added customizable settings panel for Spotlight Carousel (colors, buttons, links, navigation)
* Added customizable settings panel for Full Page Slider (colors, overlay, buttons, transitions)
* Fixed WordPress Plugin Check lint errors (translators comments, escaping)
* Removed unexpected markdown files from plugin root
* CSS optimization and teal theme consistency improvements

= 1.6.5 =
* Update version number.

= 1.6.3 =
- Fixed WordPress Plugin Check compliance: scripts now properly enqueued via wp_add_inline_script()
- Fixed database query security warnings with improved table name handling
- Code quality improvements for PHPCS/Plugin Check standards

= 1.6.4 =
- CRITICAL FIX: Resolved issue where 3D Model gallery creation would get stuck after upload
- Improved 3D model file validation and error handling
- Fixed potential JavaScript error when creating 3D galleries with fresh uploads
- Added support for .obj extension in validation logic to match PHP filters
- Increased 3D model individual file upload limit to 20MB

= 1.6.2 =
- CRITICAL FIX: "Add New Gallery" button now works on fresh installs with no galleries
- Fixed script early-return that prevented modal from opening when gallery grid was empty

= 1.6.1 =
- Improved gallery creation workflow - gallery auto-creates after selecting images
- Added multi-select tooltip in media uploader for better UX
- Added success toast notification after gallery creation
- Modal now closes automatically after successful gallery creation
- Added manage_options fallback for fresh installs - works out of the box
- Improved database query security with proper caching
- Fixed PHPCS warnings and code quality improvements
- Updated plugin description to emphasize universal shortcode compatibility
- Auto-generated gallery titles now use DD/MM/YYYY HH:MM format

= 1.6 =
- Added 3D Model Carousel gallery type with GLTF/GLB support
- Added interactive 3D viewer with orbit controls
- Added preloader options (spinner, progress bar, skeleton)
- Added fullscreen mode for 3D models
- Added thumbnail navigation for 3D carousel
- Enhanced file upload security for 3D models
- Added WebGL fallback detection and error handling
- Updated dependencies for better performance

= 1.5 =
- Added security enhancements for file uploads
- Fixed compatibility issues with WordPress 6.9
- Improved mobile responsiveness for all gallery types

= 1.4 =
- Added Fix for `blockquote` in testimonials

= 1.3 =
- Fixed Pro Carousel Slide Animation 

= 1.2 =
- Added 11 new gallery types:
  1. Classic Gallery (Original Simple Gallery) - [Demo](https://minigallery.andgowebsolutions.com/gallery/grid-layout/)
  2. Full Page Slider - [Demo](https://minigallery.andgowebsolutions.com/gallery/full-page-slider/)
  3. Grid Gallery - [Demo](https://minigallery.andgowebsolutions.com/gallery/grid-layout/)
  4. Mega Slider - [Demo](https://minigallery.andgowebsolutions.com/gallery/mega-slider/)
  5. Multi Gallery - [Demo](https://minigallery.andgowebsolutions.com/gallery/multi-carousel-2/)
  6. Neon Carousel - [Demo](https://minigallery.andgowebsolutions.com/gallery/neon-carousel-2/)
  7. Pro Carousel - [Demo](https://minigallery.andgowebsolutions.com/gallery/pro-carousel/)
  8. Single Gallery - [Demo](https://minigallery.andgowebsolutions.com/gallery/single-gallery/)
  9. Spotlight Carousel - [Demo](https://minigallery.andgowebsolutions.com/gallery/spotlight-carousel/)
  10. ThreeD Carousel - [Demo](https://minigallery.andgowebsolutions.com/gallery/3d-carousel/)
  11. Testimonial Carousel - [Demo](https://minigallery.andgowebsolutions.com/gallery/testimonials-carousel/)
  12. 3D Model Carousel - [Demo](https://minigallery.andgowebsolutions.com/gallery/3d-model-carousel/) (NEW)
- Enhanced shortcode system for universal compatibility.
- Launched new website: [https://minigallery.andgowebsolutions.com](https://minigallery.andgowebsolutions.com)
- Live demo available at homepage.
- Updated tested WordPress version to 6.8.0.

= 1.1 =
- Updated tested WordPress version to 6.7.1.
- Updated description for image upload feature.

= 1.0 =
- Initial release.

== Frequently Asked Questions ==

= Can I use it with any page builder? =
Yes! Mini Gallery uses standard WordPress shortcodes which work with any theme or page builder including Gutenberg, Elementor, Divi, WPBakery, Beaver Builder, and more. Simply use `[mgwpp_gallery id="X"]` anywhere shortcodes are supported.

= How many gallery types are available? =
There are 17 gallery types included with Mini Gallery 1.6, all accessible via the simple shortcode `[mgwpp_gallery id="X"]`.

= What 3D file formats are supported? =
The plugin supports GLTF, GLB, OBJ, and FBX formats. GLTF and GLB are recommended for best performance.

= Do I need WebGL to use the 3D Model Carousel? =
Yes, WebGL is required for 3D rendering. Most modern browsers support WebGL. The plugin includes fallback messages for browsers without WebGL support.

= Is it safe to upload 3D models? =
Yes, the plugin includes security measures to validate and sanitize all uploaded 3D files. File sizes are limited to 10MB for security.

== Security Notes ==

* All 3D model uploads are validated for correct file types
* Maximum file size for 3D models is 10MB
* GLTF/GLB files are parsed and validated before display
* Nonce verification is used for all AJAX operations
* User capability checks prevent unauthorized access

== Credits ==
Developed and maintained by Omar Ash Zeinhom.

== Screenshots ==
1. Classic Gallery (Original Simple Gallery) - [https://minigallery.andgowebsolutions.com/gallery/grid-layout/](https://minigallery.andgowebsolutions.com/gallery/grid-layout/)
2. Full Page Slider - [https://minigallery.andgowebsolutions.com/gallery/full-page-slider/](https://minigallery.andgowebsolutions.com/gallery/full-page-slider/)
3. Grid Gallery - [https://minigallery.andgowebsolutions.com/gallery/grid-layout/](https://minigallery.andgowebsolutions.com/gallery/grid-layout/)
4. Mega Slider - [https://minigallery.andgowebsolutions.com/gallery/mega-slider/](https://minigallery.andgowebsolutions.com/gallery/mega-slider/)
5. Multi Gallery - [https://minigallery.andgowebsolutions.com/gallery/multi-carousel-2/](https://minigallery.andgowebsolutions.com/gallery/multi-carousel-2/)
6. Neon Carousel - [https://minigallery.andgowebsolutions.com/gallery/neon-carousel-2/](https://minigallery.andgowebsolutions.com/gallery/neon-carousel-2/)
7. Pro Carousel - [https://minigallery.andgowebsolutions.com/gallery/pro-carousel/](https://minigallery.andgowebsolutions.com/gallery/pro-carousel/)
8. Single Gallery - [https://minigallery.andgowebsolutions.com/gallery/single-gallery/](https://minigallery.andgowebsolutions.com/gallery/single-gallery/)
9. Spotlight Carousel - [https://minigallery.andgowebsolutions.com/gallery/spotlight-carousel/](https://minigallery.andgowebsolutions.com/gallery/spotlight-carousel/)
10. ThreeD Carousel - [https://minigallery.andgowebsolutions.com/gallery/3d-carousel/](https://minigallery.andgowebsolutions.com/gallery/3d-carousel/)
11. Testimonial Carousel - [https://minigallery.andgowebsolutions.com/gallery/testimonials-carousel/](https://minigallery.andgowebsolutions.com/gallery/testimonials-carousel/)
12. 3D Model Carousel - [https://minigallery.andgowebsolutions.com/gallery/3d-model-carousel/](https://minigallery.andgowebsolutions.com/gallery/3d-model-carousel/) (NEW)