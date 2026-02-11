# Hooktrace

**Cross-Plugin Debug & Trace Recorder for WordPress**

Hooktrace is a developer observability tool that records and visualizes the runtime execution order of WordPress hooks, filters, and plugin initialization for a single page request.

## 🎯 Purpose

This plugin helps WordPress developers answer critical debugging questions:

- Which hooks fired, in what order?
- Which callbacks executed on each hook?
- What was the callback priority?
- Which plugin, theme, or core file registered/executed the callback?
- What is the exact file and line number?
- What was the relative execution time?

## ⚠️ Important Notes

- **Development/Staging Only** - This plugin is designed exclusively for development and staging environments
- **Requires WP_DEBUG** - The plugin automatically disables itself if `WP_DEBUG` is not `true`
- **Admin Only** - Only users with `manage_options` capability can access the trace timeline
- **Observational Only** - This plugin never modifies WordPress behavior or hook execution

## ✨ Features

### Hook List Tracking
Records all hooks that fire during a page request with:
- Hook name
- Hook type (action or filter)
- Source (core, theme, or plugin)
- Timestamp

### Detailed Callback Inspection
When a hook is selected, displays comprehensive callback information:
- Callback name (function, class::method, or closure)
- Priority
- Execution order
- Accepted arguments count
- Execution duration (milliseconds)
- Source file and line number
- Owning plugin/theme/core
- Callback type (function, method, closure)

### Modern Modal UI
- Admin bar integration with hook count
- Beautiful, searchable hook list
- Filter by type (action/filter) and source (core/theme/plugin)
- Detailed callback view for selected hooks
- Responsive design with smooth animations
- Color-coded badges for quick source identification

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 8.0 or higher
- `WP_DEBUG` set to `true` in `wp-config.php`
- User with `manage_options` capability (administrator)

## 🚀 Installation

### Standard Installation

1. Upload the `hooktrace` folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure `WP_DEBUG` is set to `true` in your `wp-config.php` file:

```php
define( 'WP_DEBUG', true );
```

### Early Hook Capture (Optional but Recommended)

For complete hook capture from the very beginning of WordPress initialization:

1. Copy `hooktrace-bootstrap.php` from the plugin directory
2. Place it in `/wp-content/mu-plugins/` directory
3. The file will automatically load the main plugin early

**Note:** The `mu-plugins` directory may not exist. Create it if needed.

## 📖 Usage

1. **Activate the Plugin** - Ensure the plugin is activated and `WP_DEBUG` is enabled
2. **Navigate to Any Page** - Visit any WordPress page (frontend or admin)
3. **Open Timeline** - Click "Hook Trace" in the WordPress admin bar
4. **Browse Hooks**:
   - Use the search box to find specific hooks
   - Filter by type (action/filter) or source (core/theme/plugin)
   - Click any hook to see detailed callback information
5. **Inspect Callbacks**:
   - View all callbacks registered for the selected hook
   - See execution order, priority, and duration
   - Identify source file and line number
   - Clear selection to return to hook list

### Understanding the Interface

- **Hook Name** - The WordPress hook that fired
- **Type Badge** - Purple for actions, Orange for filters
- **Source Badge**:
  - 🔴 **Red (Core)** - WordPress core
  - 🔵 **Blue (Theme)** - Active theme
  - 🟢 **Green (Plugin)** - Active plugin
- **Callback Details**:
  - **Priority** - The priority at which the callback was registered
  - **Execution Order** - The order in which callbacks executed
  - **Duration** - Execution time in milliseconds (relative to first callback)
  - **File Path** - Exact file and line number where callback is defined

## 🏗️ Architecture

The plugin follows a clean, object-oriented architecture:

```
HookTrace\
├── Core          # Bootstrap and main plugin controller
├── Collector      # Event collectors (HookList, SelectedHook)
├── Inspector      # Callback metadata resolution
├── Storage        # In-memory request-level storage
└── UI             # Admin interface
```

### Key Design Principles

- **No Hooks in Constructors** - All hooks registered via `register_hooks()` methods
- **Single Responsibility** - Each class has one clear purpose
- **In-Memory Only** - No database writes, all data discarded after request
- **Performance Conscious** - Minimal overhead (5-10ms average)
- **Early Boot Support** - MU-plugin bootstrap captures hooks from the very beginning

## 🔒 Safety Features

- ✅ Auto-disables when `WP_DEBUG` is false
- ✅ Never runs for non-admin users
- ✅ Never modifies hook execution
- ✅ Never suppresses PHP errors
- ✅ Never silently catches exceptions
- ✅ Observational only - zero side effects

## 🐛 Troubleshooting

### Timeline Not Appearing

1. Check that `WP_DEBUG` is set to `true` in `wp-config.php`
2. Verify you're logged in as an administrator
3. Ensure the plugin is activated
4. Check browser console for JavaScript errors

### Missing Early Hooks

If you're not seeing hooks from very early in the WordPress load process:

1. Install the MU-plugin bootstrap file (see Installation section)
2. Ensure the file is in `/wp-content/mu-plugins/`
3. Clear any object cache

### Performance Concerns

If you notice significant slowdown:

1. This is expected in development - the plugin adds ~5-10ms overhead
2. Only use in development/staging environments
3. Disable when not actively debugging

## 📝 Development

### Code Standards

- PHP 8.0+ syntax
- WordPress Coding Standards
- PSR-4 autoloading
- No anonymous global functions
- No eval or output buffering hacks

### Contributing

This plugin follows strict architectural rules:

- No database persistence
- No request history
- No REST/AJAX/Cron tracing
- No auto conflict resolution
- No production usage

## 📄 License

GPLv2 or later

## 👥 Credits

- **Author:** smilingsyntax
- **Author URI:** https://smilingsyntax.com
- **Contributor:** codemadan
- **Contributor URI:** https://manjul.me

## 🔗 Support

For issues, feature requests, or contributions, please refer to the plugin repository.

---

**Remember:** This is a developer tool. Always use in development/staging environments only!
