# Glass Gallery Pro - Plugin Documentation

**Version:** 2.0.0
**Author:** Elite Results Marketing
**License:** GPL v2 or later

---

## Table of Contents

1. [Overview](#overview)
2. [Installation & Setup](#installation--setup)
3. [User Guide](#user-guide)
4. [Developer Reference](#developer-reference)
5. [LLM Context for Development](#llm-context-for-development)

---

## Overview

### What This Plugin Does

Glass Gallery Pro is a professional filterable image gallery with a customer upload portal, specifically designed for glass companies. It enables businesses to showcase their products and projects with a modern, interactive gallery system while allowing customers to submit their own images.

### Key Features

- **Professional Image Gallery** - Displays images with advanced filtering and search
- **Customer Upload Portal** - Allows customers/contractors to upload images via drag-and-drop
- **Category Filtering** - Filter by multiple glass industry categories (product type, glass type, hardware, etc.)
- **Modal Lightbox** - Click images to view fullscreen with navigation
- **Featured Gallery** - Display random/carousel images on landing pages
- **Analytics Tracking** - Track image views and interactions
- **Batch Upload** - Upload and categorize multiple images at once

### Glass Industry Categories (Pre-configured)

- Product Types (Shower Enclosures, Wine Cellars, Room Partitions)
- Shower Shapes (Single Door, Inline, Corner, Neo-Angle)
- Glass Types (Clear, Low Iron, Satin, Rain, Color, Mirror)
- Hardware Colors, Handle Styles, Framing Types
- Features (Support Bar, Wet Room, Steam Shower)

---

## Installation & Setup

### Requirements

- WordPress 5.0+
- PHP 7.0+
- No external plugins required

### Installation Steps

1. **Upload Plugin**
   - Upload `glass-gallery` folder to `/wp-content/plugins/`
   - OR upload as ZIP via Plugins → Add New → Upload

2. **Activate**
   - Go to Plugins in WordPress admin
   - Click "Activate" on Glass Gallery Pro

3. **Initial Configuration**
   - Navigate to Glass Gallery → Settings
   - Click "Populate Categories Now" to add pre-configured industry categories
   - Configure display and upload settings

### Plugin Creates Automatically

- Custom Post Type: `glass_gallery_item`
- Database table: `wp_glass_gallery_views` (for analytics)
- WordPress Categories taxonomy for the post type

---

## User Guide

### Displaying the Gallery

**Main Gallery Shortcode:**
```
[glass_gallery]
```

**With Options:**
```
[glass_gallery category="shower-enclosures" limit="20" columns="4" show_filters="true"]
```

| Attribute | Default | Description |
|-----------|---------|-------------|
| `category` | (all) | Filter to specific category slug |
| `limit` | -1 | Max images (-1 for unlimited) |
| `columns` | 4 | Grid columns (2-5) |
| `show_filters` | true | Show/hide filter sidebar |
| `featured_only` | false | Show only featured images |

### Upload Portal

**Upload Form Shortcode:**
```
[glass_upload_form]
```

Features:
- Drag-and-drop upload zone
- Multiple file selection
- Title, description, category per image
- Batch settings (apply to all)
- Optional password protection

### Featured Gallery

**Featured Gallery Shortcode:**
```
[glass_featured_gallery limit="12" carousel="false" button_text="View Gallery" button_url="/gallery"]
```

| Attribute | Default | Description |
|-----------|---------|-------------|
| `limit` | 12 | Number of images |
| `carousel` | false | Enable carousel mode |
| `button_text` | "View Full Gallery" | CTA button text |
| `button_url` | "/gallery" | Link to full gallery |

### Admin Dashboard

**Glass Gallery → Dashboard**
- Statistics: Total images, categories, users
- Quick Actions: Add image, view all, manage categories
- Recent images list
- Shortcode reference

**Glass Gallery → All Images**
- WordPress post list for gallery items
- Featured image column
- Edit individual images

**Glass Gallery → Settings**
- Display: Images per page, columns, lightbox
- Upload: Auto-approve, file types, max size
- Advanced: Comments, watermark, analytics
- Default Categories: Pre-populate lists

### Adding Images (Admin)

1. Go to Glass Gallery → Add New
2. Enter title
3. Set featured image (the gallery photo)
4. Add caption/description in the metabox
5. Set display order (lower = first)
6. Mark as featured if desired
7. Assign categories
8. Publish

---

## Developer Reference

### File Structure

```
glass-gallery/
├── glass-gallery-pro.php          # Main plugin file
├── glass-gallery-categories.json  # Pre-configured category data
├── assets/
│   ├── css/
│   │   ├── admin.css              # Admin styles
│   │   ├── frontend.css           # Gallery styles
│   │   └── featured-gallery.css   # Featured gallery styles
│   └── js/
│       ├── admin.js               # Admin functionality
│       ├── frontend.js            # Gallery filtering/modal
│       ├── upload-manager.js      # Upload form handling
│       └── featured-gallery.js    # Carousel/featured display
└── templates/
    ├── admin-dashboard.php        # Dashboard page
    ├── admin-settings.php         # Settings form
    ├── admin-categories.php       # Category info
    ├── gallery-display.php        # Frontend gallery
    ├── upload-form.php            # Upload form
    └── featured-gallery.php       # Featured gallery
```

### Custom Post Type

**Post Type:** `glass_gallery_item`
- Supports: title, editor, custom fields, thumbnail
- Public: true
- REST API: enabled

### Post Meta Fields

| Meta Key | Type | Description |
|----------|------|-------------|
| `_glass_image_caption` | string | Image description/caption |
| `_glass_image_order` | int | Custom display order |
| `_glass_featured_image` | bool | Featured image flag |

### Database Table

**Table:** `wp_glass_gallery_views`

| Column | Type | Description |
|--------|------|-------------|
| `id` | mediumint | Primary key |
| `post_id` | bigint | Gallery item post ID |
| `ip_address` | varchar(45) | Visitor IP |
| `user_agent` | text | Browser info |
| `view_date` | datetime | View timestamp |

### WordPress Options

| Option Key | Type | Description |
|------------|------|-------------|
| `glass_gallery_settings` | array | Display, upload, advanced settings |
| `glass_gallery_category_relationships` | array | Parent-child category mapping |
| `glass_gallery_custom_taxonomies` | array | Custom taxonomy slugs |
| `glass_gallery_taxonomy_visibility` | array | Visibility state of taxonomies |

### Main Class: GlassGalleryPro

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `create_post_types()` | Registers custom post type |
| `create_database_tables()` | Creates views tracking table |
| `gallery_shortcode($atts)` | Renders main gallery |
| `upload_form_shortcode($atts)` | Renders upload form |
| `featured_gallery_shortcode($atts)` | Renders featured gallery |
| `handle_image_upload()` | Single image AJAX upload |
| `handle_batch_image_upload()` | Batch upload handler |
| `filter_gallery_images()` | AJAX filtering |
| `handle_track_image_view()` | Analytics tracking |

### AJAX Endpoints

| Action | Purpose | Auth |
|--------|---------|------|
| `upload_gallery_image` | Single upload | Public |
| `batch_upload_gallery_images` | Batch upload | Public |
| `filter_gallery_images` | Filter gallery | Public |
| `track_image_view` | Track views | Public |
| `update_gallery_image` | Update details | Public |
| `delete_gallery_image` | Delete image | Public |
| `add_gallery_term` | Add category | Admin |
| `populate_default_categories` | Add defaults | Admin |
| `export_gallery_categories` | Export JSON | Admin |

### CSS Classes (Frontend)

| Class | Element |
|-------|---------|
| `.ggp-gallery` | Main container |
| `.ggp-item` | Individual gallery item |
| `.ggp-modal` | Lightbox modal |
| `.ggp-sidebar` | Filter sidebar |
| `.ggp-grid` | Image grid |

---

## LLM Context for Development

### Quick Reference for AI Assistants

**Architecture:**
- Single main class `GlassGalleryPro` in `glass-gallery-pro.php`
- Templates in `/templates/` for all UI
- Assets in `/assets/` (css and js)
- No jQuery dependency for main gallery (vanilla JS)
- Upload form uses jQuery

**Key Files to Edit:**

| Task | File(s) |
|------|---------|
| Modify gallery display | `templates/gallery-display.php`, `assets/js/frontend.js` |
| Change upload form | `templates/upload-form.php`, `assets/js/upload-manager.js` |
| Add admin settings | `glass-gallery-pro.php` (settings_page method), `templates/admin-settings.php` |
| Modify filtering | `assets/js/frontend.js`, `filter_gallery_images()` in main file |
| Change lightbox | `assets/js/frontend.js`, `assets/css/frontend.css` |

**Data Flow:**
1. Images stored as custom post type `glass_gallery_item`
2. Featured image = the gallery photo
3. Categories use WordPress standard taxonomy
4. Frontend loads all images, JS handles filtering
5. AJAX used for uploads and dynamic filtering

**Common Modifications:**

*Adding a new gallery attribute:*
1. Add to `shortcode_atts()` in `gallery_shortcode()` method
2. Pass to template via variables
3. Use in `gallery-display.php` template

*Adding a new upload field:*
1. Add HTML in `upload-form.php`
2. Handle in `assets/js/upload-manager.js`
3. Save in `handle_image_upload()` or `handle_batch_image_upload()`

*Adding a new post meta field:*
1. Add to metabox in `gallery_details_metabox()`
2. Save in `save_meta_boxes()`
3. Display where needed

**Security Patterns:**
- Nonce: `glass_gallery_nonce`
- File upload via `wp_handle_upload()`
- Capability checks: `manage_options`, `upload_files`, `edit_post`
- Sanitization throughout

**Testing:**
1. Add images via admin or upload form
2. View gallery on frontend page with shortcode
3. Test filters and search
4. Check lightbox functionality
5. Test on mobile for responsive behavior

### Prompt Template for Development Tasks

```
I need to modify the Glass Gallery Pro WordPress plugin.

TASK: [describe what you want to change]

CONTEXT:
- Main plugin file: glass-gallery-pro.php (contains GlassGalleryPro class)
- Templates: templates/ folder
- Frontend JS: assets/js/frontend.js (vanilla JS, no jQuery)
- Upload JS: assets/js/upload-manager.js (uses jQuery)
- Admin JS: assets/js/admin.js

Custom post type: glass_gallery_item
Categories: Uses WordPress standard categories
Meta fields: _glass_image_caption, _glass_image_order, _glass_featured_image

Shortcodes:
- [glass_gallery] - main gallery
- [glass_upload_form] - upload portal
- [glass_featured_gallery] - featured display

Please provide the specific code changes needed.
```

---

## Support

**Author:** Elite Results Marketing
**Website:** https://www.eliteresultsmarketing.com
