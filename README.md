# PSM Resource Manager WordPress Plugin

A comprehensive resource management plugin for WordPress with custom post types, taxonomies, and dynamic forms.

## Features

### Core Functionality
- **Custom Post Type**: `psm_resource` for managing all types of resources
- **Custom Taxonomy**: `psm_resource_type` for categorizing resources
- **Custom Database Tables**: Extended data storage for resource-specific information
- **Security**: Nonces implemented throughout for secure form submissions

### Resource Types Supported
1. **PDF Documents** - Upload and manage PDF files with image thumbnails
2. **Videos** - Embed or link videos from various hosting platforms
3. **Podcasts** - Link to podcast episodes with hosting platform options
4. **Articles** - Standard blog-style content resources
5. **External Links** - Links to external websites and resources

### Admin Features
- **Dynamic Forms**: Form fields change based on selected resource type
- **Media Upload**: WordPress media library integration for image uploads
- **Resource Management**: Full CRUD operations for resources
- **Bulk Actions**: Delete multiple resources at once
- **Resource Types Management**: Add, edit, and delete resource categories

### Frontend Features
- **Responsive Templates**: Mobile-friendly resource display
- **Archive Pages**: List all resources with filtering options
- **Single Resource Pages**: Detailed view of individual resources
- **Search & Filter**: AJAX-powered filtering by type and search terms
- **Media Embeds**: Automatic embedding of YouTube, Vimeo, Spotify content

### Technical Features
- **AJAX Integration**: Smooth user experience without page reloads
- **Template System**: Custom templates for frontend display
- **Responsive Design**: Works on all device sizes
- **Accessibility**: Screen reader friendly and keyboard navigable

## Installation

1. Upload the plugin files to the `/wp-content/plugins/psm-resource-manager/` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'Resources' in the WordPress admin menu to start managing resources

## File Structure

```
psm-resource-manager/
├── psm-resource-manager.php          # Main plugin file
├── assets/
│   ├── css/
│   │   ├── admin.css                 # Admin interface styles
│   │   └── frontend.css              # Frontend display styles
│   └── js/
│       ├── admin.js                  # Admin JavaScript functionality
│       └── frontend.js               # Frontend JavaScript functionality
├── includes/
│   └── resource-handler.php          # Core functionality and AJAX handlers
└── templates/
    ├── admin/
    │   ├── add-resource.php          # Add new resource form
    │   ├── resources-list.php        # Resources management list
    │   └── resource-types.php        # Resource types management
    └── frontend/
        ├── archive-psm_resource.php  # Resources archive template
        ├── single-psm_resource.php   # Single resource template
        └── resource-card.php         # Resource card component
```

## Usage

### Adding Resources

1. Go to 'Resources' > 'Add New Resource' in the WordPress admin
2. Fill in the resource title and description
3. Select the resource type from the dropdown
4. Complete the type-specific fields that appear:
   - **PDF**: Upload an image thumbnail
   - **Video/Podcast**: Enter URL and select hosting platform
   - **Link**: Enter the external URL
5. Click 'Save Resource'

### Managing Resource Types

1. Go to 'Resources' > 'Resource Types'
2. Add new resource types using the form on the left
3. Manage existing types in the table on the right

### Frontend Display

Resources are automatically displayed using custom templates:
- Archive: `/resources/` - Shows all resources with filtering
- Single: `/resource/resource-name/` - Shows individual resource details
- Taxonomy: `/resource-type/type-name/` - Shows resources of specific type

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## License

This plugin is licensed under the GPL v2 or later.

---

**Version**: 1.0.0  
**Author**: PSM Consult  
**Tested up to**: WordPress 6.4
