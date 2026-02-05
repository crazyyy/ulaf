# Mini Gallery Plugin - Admin Directory Documentation

## 📁 **Admin Directory Overview**

```plaintext
📁 includes/admin/
├── 📄 class-*.php          # Core functionality
├── 📁 views/               # Admin UI components
│   ├── 📁 edit-gallery/    # Gallery editor
│   ├── 📁 galleries/       # Gallery management
│   ├── 📁 albums/          # Album management
│   └── 📁 dashboard/       # Admin dashboard
├── 📁 tables/              # WP list tables
├── 📁 js/                  # JavaScript files
└── 📁 css/                 # Stylesheets
```

```plaintext
includes/admin/
├── Core Admin Classes/
├── Views/ (UI Components)
├── Tables/ (WP List Tables)
├── CSS/ (Admin Styles)
├── JS/ (Admin Scripts)
└── Images/ (Admin Icons/Assets)
```

## 🔧 **Core Admin Classes**

### **Main Admin Controller**

- `class-mgwpp-admin.php` - Main admin controller, initializes admin interface
- `class-mgwpp-admin-menu.php` - Registers admin menu pages and submenus
- `class-mgwpp-admin-core.php` - Core admin functionality and hooks

### **Data Management**

- `class-mgwpp-data-handler.php` - CRUD operations for galleries/albums
- `class-mgwpp-data-manager.php` - Database operations and data validation
- `class-mgwpp_ajax_handler.php` - Handles AJAX requests for admin operations

### **Security & Assets**

- `class-security-scanner.php` - Security scanning for suspicious files
- `class-mgwpp-admin-assets.php` - Enqueues admin CSS/JS files

## 🎨 **Views Directory Structure**

### **Dashboard Views** (`views/dashboard/`)

- Main admin dashboard with statistics
- Quick access to recent galleries
- System status overview

### **Gallery Management** (`views/galleries/`)

- Gallery listing and management
- Add/edit gallery interfaces
- Gallery type selection

### **Album Management** (`views/albums/`)

- Album creation and organization
- Bulk image management
- Album settings panel

### **Edit Gallery** (`views/edit-gallery/`)

- Gallery editor interface
- Image upload/arrangement
- Gallery settings and customization

### **Testimonials** (`views/testimonials/`)

- Testimonial management
- Client review displays
- Testimonial carousel settings

### **Public Views** (`views/public/`)

- Frontend display templates
- Shortcode rendering views

### **Inner Header** (`views/inner-header/`)

- Admin page headers
- Breadcrumb navigation
- Page title and actions

## 📊 **Tables Directory**

### **WP List Tables**

- `class-mgwpp-albums-table.php` - Displays albums in WP admin table format
- *Note: `class-storage-table.php` and `class-suspicious-files-table.php` mentioned in structure but not found in actual files*

## 🎯 **JavaScript Files**

### **Admin Scripts** (`js/`)

- `mgwpp-admin-scripts.js` - Main admin JavaScript functionality
- `mgwpp-editor.js` - Gallery editor JavaScript (27.7KB - largest JS file)
- `mg-scripts.js` - Legacy/helper scripts
- `admin-edit.js` - Empty file (placeholder)

## 🎨 **CSS Files**

### **Admin Styles** (`css/`)

- Admin-specific styling
- Dashboard and table styles
- Form and button styling

## 🔍 **Quick Debugging Guide**

### **Common Issues & Solutions**

1. **Gallery Not Displaying**
   - Check `class-mgwpp-data-handler.php` - Data retrieval issues
   - Verify `class-mgwpp_ajax_handler.php` - AJAX responses

2. **Admin Pages Not Loading**
   - Check `class-mgwpp-admin-menu.php` - Menu registration
   - Verify `class-mgwpp-admin.php` - Admin initialization

3. **Images Not Uploading**
   - Check `views/edit-gallery/` - Upload interface
   - Verify `class-mgwpp-data-handler.php` - Upload processing

4. **AJAX Not Working**
   - Check `class-mgwpp_ajax_handler.php`
   - Verify admin JavaScript console for errors

5. **Security Scanner Issues**
   - Check `class-security-scanner.php`
   - Verify file permissions in uploads directory

### **File Size Notes**

- `mgwpp-editor.js` (27.7KB) - Largest JS file, contains editor functionality
- `class-mgwpp_ajax_handler.php` (19.7KB) - Main AJAX handler
- `class-mgwpp-admin-assets.php` (19KB) - Asset management

### **Directory Quick Reference**

```plaintext
📁 includes/admin/
├── 📄 class-*.php          # Core functionality
├── 📁 views/               # Admin UI components
│   ├── 📁 edit-gallery/    # Gallery editor
│   ├── 📁 galleries/       # Gallery management
│   ├── 📁 albums/          # Album management
│   └── 📁 dashboard/       # Admin dashboard
├── 📁 tables/              # WP list tables
├── 📁 js/                  # JavaScript files
└── 📁 css/                 # Stylesheets
```

## ⚡ **1-Minute Debugging Checklist**

1. **Check Console Errors** - Look at browser console for JS errors
2. **Verify AJAX Endpoints** - Check `admin-ajax.php` responses
3. **Check File Permissions** - Ensure uploads directory is writable
4. **Verify Shortcode** - Test with `[mgwpp_gallery id="X"]`
5. **Check Gallery Type** - Verify gallery type is set in post meta

---

**Last Updated:** January 3, 2026  
**Plugin Version:** Based on directory timestamps  
**Debug Status:** Ready for troubleshooting
