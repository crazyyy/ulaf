# Ultimate Extension for ACF

Enhanced ACF Flexible Content editing with image previews and performance optimizations - compatible with ACF v5.6+ and v6.5+

## Features

- **Image Previews**: Add preview images to ACF flexible content layouts
- **Multisite Support**: Automatic fallback to main site images in multisite setups
- **Performance Optimised**: Caching and optimised database queries
- **ACF Compatibility**: Works with both legacy (v5.6-v6.4.x) and current (v6.5+) ACF versions
- **Admin Interface**: Easy-to-use settings page for managing preview images
- **Accordion Behaviour**: Only one layout open at a time for better UX

## Translation Support

This plugin is fully translation-ready and supports internationalisation (i18n).

### Available Languages

The plugin includes a `.pot` file for translators. Currently available translations:

- **English** (default)
- *More languages coming soon...*

### For Translators

To translate this plugin:

1. **Download the .pot file**: `languages/ultimate-extension-for-acf.pot`
2. **Create translation files**:
   - `languages/ultimate-extension-for-acf-{language-code}.po`
   - `languages/ultimate-extension-for-acf-{language-code}.mo`
3. **Use translation tools** like Poedit, Loco Translate, or WP-CLI
4. **Submit translations** via [Ultimate Agency contact form](https://www.ultimate.agency/)

### Translation Files Location

```
ultimate-extension-for-acf/
├── languages/
│   ├── ultimate-extension-for-acf.pot          # Template file
│   ├── ultimate-extension-for-acf-en_US.po     # English (US)
│   ├── ultimate-extension-for-acf-en_US.mo     # Compiled English
│   ├── ultimate-extension-for-acf-de_DE.po     # German
│   ├── ultimate-extension-for-acf-de_DE.mo     # Compiled German
│   └── ...                                 # Other languages
```

### Text Domain

- **Text Domain**: `ultimate-extension-for-acf`
- **Language Path**: `/languages`

## Installation

1. Upload the plugin files to `/wp-content/plugins/ultimate-extension-for-acf/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure Advanced Custom Fields (ACF) is installed and active
4. Go to ACF Field Groups → Image Preview Settings to configure

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Advanced Custom Fields (ACF) v5.6+ or v6.5+

## Security

This plugin follows WordPress security best practices and implements industry-standard security measures to protect your installation.

### **Security Features**
- ✅ **User Authentication**: Proper user permission checks
- ✅ **Data Validation**: Input sanitization and output escaping
- ✅ **Secure Communications**: Protected AJAX requests
- ✅ **File Security**: Safe file handling and validation
- ✅ **Multisite Protection**: Secure cross-site data handling

### **Security Standards**
- Follows WordPress coding standards
- Implements OWASP security guidelines
- Regular security updates and maintenance
- Professional code review process

## Multisite Support

The plugin automatically handles multisite setups:

- **Current Site Priority**: Uses site-specific preview images when available
- **Main Site Fallback**: Falls back to main site images when the subsite doesn't have specific previews
- **URL Handling**: Correctly generates image URLs across different sites
- **Path Resolution**: Properly handles file paths in multisite environments

## License

This plugin is licensed under the GPL v2 or later. See [LICENSE.md](LICENSE.md) for details.

## Security Reporting

If you discover a security vulnerability, please report it responsibly through our secure contact channels.

**Contact**: [Ultimate Agency](https://www.ultimate.agency/contact/) - Security Team

**Guidelines**:
- Report through official channels only
- Allow reasonable time for fixes
- Follow responsible disclosure practices

## Support

For support, feature requests, or bug reports:

- **Website**: [Ultimate Agency](https://www.ultimate.agency/)
- **Contact**: [Contact Form](https://www.ultimate.agency/contact/)

## Changelog

### 1.0.0
- Initial release
- Image preview functionality for ACF flexible content
- Multisite support with automatic fallback
- Performance optimisations with caching
- Admin interface for managing preview images
- Support for both ACF v5.6+ and v6.5+
- Full translation support

---

**Author**: Miro Sedlacek - Ultimate Agency  
**Version**: 1.0.0  
**License**: GPL v2 or later
