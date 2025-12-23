<?php
// templates/upload-form.php - Modern Agency Design
if (!defined('ABSPATH')) {
    exit;
}

// Get existing gallery images
$existing_images_query = new WP_Query(array(
    'post_type' => 'glass_gallery_item',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
));

$existing_images = $existing_images_query->posts;

// Create category groups array for template
// Get all categories
$all_categories = get_terms(array(
    'taxonomy' => 'category',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));

$category_groups = array();

if (!empty($all_categories) && !is_wp_error($all_categories)) {
    // First, try to organize by parent-child hierarchy
    $parent_categories = array_filter($all_categories, function($term) {
        return $term->parent == 0;
    });

    $has_hierarchy = false;
    foreach ($parent_categories as $parent) {
        $children = array_filter($all_categories, function($term) use ($parent) {
            return $term->parent == $parent->term_id;
        });

        if (!empty($children)) {
            $has_hierarchy = true;
            $category_groups[$parent->term_id] = array(
                'label' => $parent->name,
                'terms' => $children
            );
        }
    }

    // If no hierarchy found, show all categories in one group
    if (!$has_hierarchy) {
        $category_groups['all'] = array(
            'label' => 'Categories',
            'terms' => $all_categories
        );
    }
}

// Debug: Log available categories (remove this after testing)
if (current_user_can('manage_options')) {
    error_log('Glass Gallery - Available Categories: ' . print_r($category_groups, true));
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php
// Check if password protection is enabled
$settings = wp_parse_args(get_option('glass_gallery_settings', array()), array(
    'upload_password_enabled' => false,
    'upload_password' => ''
));

$password_required = $settings['upload_password_enabled'] && !empty($settings['upload_password']);
$password_verified = false;

if ($password_required) {
    // Check if password was submitted
    if (isset($_POST['upload_password_submit'])) {
        if (wp_verify_nonce($_POST['password_nonce'], 'upload_password_check')) {
            if ($_POST['upload_password'] === $settings['upload_password']) {
                $password_verified = true;
                // Set session to remember password for this session
                if (!session_id()) {
                    session_start();
                }
                $_SESSION['glass_gallery_password_verified'] = true;
            } else {
                $password_error = 'Incorrect password. Please try again.';
            }
        }
    } else {
        // Check if already verified in session
        if (!session_id()) {
            session_start();
        }
        if (isset($_SESSION['glass_gallery_password_verified']) && $_SESSION['glass_gallery_password_verified']) {
            $password_verified = true;
        }
    }
}

// If password protection is enabled and not verified, show password form
if ($password_required && !$password_verified): ?>
<div class="upload-password-form">
    <div class="password-container">
        <div class="password-content">
            <div class="password-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Protected Upload Portal</h2>
            <p>This upload portal is password protected. Please enter the password to continue.</p>
            
            <?php if (isset($password_error)): ?>
                <div class="password-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo esc_html($password_error); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" class="password-form">
                <?php wp_nonce_field('upload_password_check', 'password_nonce'); ?>
                <div class="password-input-group">
                    <input type="password" name="upload_password" placeholder="Enter password" required autofocus>
                    <button type="submit" name="upload_password_submit">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.upload-password-form {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 25%, #f1f3f4 50%, #e8eaed 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.password-container {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 16px;
    padding: 3rem;
    max-width: 400px;
    width: 100%;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.password-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.password-container h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: #1a1a1a;
    letter-spacing: -0.025em;
}

.password-container p {
    color: #4a5568;
    margin: 0 0 2rem 0;
    font-size: 1rem;
}

.password-error {
    background: #fef2f2;
    color: #991b1b;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid #fecaca;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.password-input-group {
    display: flex;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.password-input-group input {
    flex: 1;
    padding: 1rem 1.25rem;
    border: none;
    outline: none;
    font-size: 1rem;
    background: transparent;
}

.password-input-group button {
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.password-input-group button:hover {
    background: linear-gradient(135deg, #2563eb, #7c3aed);
}

@media (max-width: 480px) {
    .password-container {
        padding: 2rem 1.5rem;
    }
    
    .password-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>

<?php else: ?>

<div class="agency-gallery-studio">
    <!-- Navigation Header -->
    <header class="studio-header">
        <div class="header-container">
            <div class="brand-section">
                <h1>Gallery Studio</h1>
                <span class="tagline">Professional Image Management</span>
            </div>
            
            <nav class="studio-nav">
                <button class="nav-item" data-view="upload">
                    <i class="fas fa-plus-circle"></i>
                    <span>Upload</span>
                </button>
                <button class="nav-item active" data-view="manage">
                    <i class="fas fa-layer-group"></i>
                    <span>Manage</span>
                    <span class="count-badge"><?php echo count($existing_images); ?></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Upload Studio -->
    <section class="studio-view" id="upload-studio">
        <div class="studio-content">
            <form id="studio-upload-form" enctype="multipart/form-data">
                <?php wp_nonce_field('glass_gallery_nonce', 'glass_upload_nonce'); ?>
                
                <!-- Upload Zone -->
                <div class="upload-studio">
                    <div class="upload-zone" id="upload-zone">
                        <div class="zone-content">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </div>
                            <h2>Drop your images here</h2>
                            <p>or click to browse and select multiple files</p>
                            <button type="button" class="browse-files-btn">
                                Choose Images
                            </button>
                            <div class="format-info">
                                <span>JPG, PNG, GIF</span>
                                <span>Max 10MB each</span>
                            </div>
                        </div>
                        <input type="file" id="file-input" name="gallery_images[]" accept="image/*" multiple style="display: none;">
                    </div>
                    
                    <!-- Progress Indicator -->
                    <div class="upload-progress" style="display: none;">
                        <div class="progress-header">
                            <span class="progress-title">Uploading Images</span>
                            <span class="progress-count">0 / 0</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                    </div>

                    <!-- Bulk Settings Panel -->
                    <div class="bulk-settings-panel" id="bulk-settings-panel" style="display: none;">
                        <div class="bulk-settings-header">
                            <div class="header-left">
                                <i class="fas fa-layer-group"></i>
                                <h3>Batch Settings</h3>
                                <span class="subtitle">Apply to all images at once</span>
                            </div>
                            <button type="button" class="toggle-bulk-settings active" title="Toggle batch settings">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>

                        <div class="bulk-settings-content">
                            <div class="bulk-settings-grid">
                                <div class="bulk-field">
                                    <label for="bulk-title">Batch Title</label>
                                    <input type="text" id="bulk-title" class="bulk-input" placeholder="e.g., Frameless Shower Door">
                                    <p class="field-hint">Leave empty to use individual file names</p>
                                </div>

                                <div class="bulk-field">
                                    <label>Batch Categories</label>
                                    <button type="button" class="bulk-categories-selector" id="bulk-categories-btn">
                                        <span class="selector-text">Select categories for all</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>

                                    <div class="bulk-categories-dropdown" id="bulk-categories-dropdown" style="display: none;">
                                        <?php foreach ($category_groups as $parent_id => $group): ?>
                                            <?php if (!empty($group['terms'])): ?>
                                            <div class="dropdown-group">
                                                <h6><?php echo esc_html($group['label']); ?></h6>
                                                <div class="group-options">
                                                    <?php foreach ($group['terms'] as $term): ?>
                                                    <label class="dropdown-option">
                                                        <input type="checkbox" class="bulk-category-checkbox" value="<?php echo $term->term_id; ?>" data-taxonomy="category" data-parent="<?php echo $parent_id; ?>">
                                                        <span class="option-mark"></span>
                                                        <span class="option-label"><?php echo esc_html($term->name); ?></span>
                                                    </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="field-hint">Selected categories will apply to all images</p>
                                </div>
                            </div>

                            <div class="bulk-actions">
                                <button type="button" class="action-btn primary" id="apply-bulk-settings">
                                    <i class="fas fa-check-double"></i>
                                    Apply to All Images
                                </button>
                                <button type="button" class="action-btn secondary" id="clear-bulk-settings">
                                    <i class="fas fa-eraser"></i>
                                    Clear Batch Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="message-display"></div>
                </div>
                
                <!-- Image Editor -->
                <div class="image-editor" id="image-editor" style="display: none;">
                    <div class="editor-header">
                        <h3>Configure Images</h3>
                        <div class="editor-actions">
                            <button type="button" class="action-btn secondary" data-action="select-all">
                                <i class="fas fa-check-square"></i>
                                Select All
                            </button>
                            <button type="button" class="action-btn danger" data-action="remove-selected">
                                <i class="fas fa-trash-alt"></i>
                                Remove Selected
                            </button>
                        </div>
                    </div>
                    
                    <div class="image-list" id="image-list">
                        <!-- Dynamic content -->
                    </div>
                    
                    <div class="editor-footer">
                        <button type="submit" class="upload-btn">
                            <i class="fas fa-rocket"></i>
                            <span>Upload Images</span>
                        </button>
                        <button type="button" class="reset-btn">
                            <i class="fas fa-rotate-left"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Management Studio -->
    <section class="studio-view active" id="manage-studio">
        <div class="studio-content">
            <div class="management-header">
                <div class="header-info">
                    <h2>Gallery Management</h2>
                    <p>Drag rows to reorder • Click to edit • Changes save automatically</p>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($existing_images); ?></span>
                        <span class="stat-label">Images</span>
                    </div>
                    <button class="refresh-btn">
                        <i class="fas fa-arrows-rotate"></i>
                        Refresh
                    </button>
                </div>
            </div>
            
            <?php if (!empty($existing_images)): ?>
            <div class="gallery-table" id="gallery-table">
                <div class="table-header">
                    <div class="col-order">#</div>
                    <div class="col-image">Image</div>
                    <div class="col-title">Title</div>
                    <div class="col-description">Description</div>
                    <div class="col-categories">Categories</div>
                    <div class="col-actions">Actions</div>
                </div>
                
                <div class="table-body" id="table-body">
                    <?php 
                    foreach ($existing_images as $index => $image): 
                        $post_id = $image->ID;
                        $image_url = get_the_post_thumbnail_url($post_id, 'medium');
                        $full_image_url = get_the_post_thumbnail_url($post_id, 'full');
                        $caption = get_post_meta($post_id, '_glass_image_caption', true);
                        $order = get_post_meta($post_id, '_glass_image_order', true);
                        
                        // Get current categories
                        $current_categories = array();
                        $taxonomies = get_object_taxonomies('glass_gallery_item');
                        foreach ($taxonomies as $taxonomy) {
                            $terms = get_the_terms($post_id, $taxonomy);
                            if ($terms && !is_wp_error($terms)) {
                                $current_categories[$taxonomy] = wp_list_pluck($terms, 'term_id');
                            }
                        }
                    ?>
                    
                    <div class="table-row" data-post-id="<?php echo $post_id; ?>">
                        <div class="col-order">
                            <div class="drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                            <span class="row-number"><?php echo $index + 1; ?></span>
                        </div>
                        
                        <div class="col-image">
                            <div class="image-preview">
                                <?php if ($image_url): ?>
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image->post_title); ?>">
                                    <div class="image-overlay">
                                        <a href="<?php echo esc_url($full_image_url); ?>" target="_blank" class="view-full">
                                            <i class="fas fa-expand"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-title">
                            <input type="text" class="inline-edit title-edit" value="<?php echo esc_attr($image->post_title); ?>" placeholder="Enter title">
                        </div>
                        
                        <div class="col-description">
                            <textarea class="inline-edit description-edit" rows="2" placeholder="Add description"><?php echo esc_textarea($caption); ?></textarea>
                        </div>
                        
                        <div class="col-categories">
                            <button class="categories-btn" data-post-id="<?php echo $post_id; ?>">
                                <i class="fas fa-tags"></i>
                                <span class="categories-preview">
                                    <?php 
                                    $total_categories = 0;
                                    foreach ($current_categories as $cat_terms) {
                                        $total_categories += count($cat_terms);
                                    }
                                    echo $total_categories . ' selected';
                                    ?>
                                </span>
                            </button>
                            
                            <div class="categories-popup" style="display: none;">
                                <div class="popup-header">
                                    <h4>Select Categories</h4>
                                    <button class="close-popup">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="popup-content">
                                    <?php foreach ($category_groups as $taxonomy => $group): ?>
                                        <?php if (!empty($group['terms'])): ?>
                                        <div class="category-group">
                                            <h5><?php echo $group['label']; ?></h5>
                                            <div class="category-options">
                                                <?php foreach ($group['terms'] as $term): 
                                                    $checked = isset($current_categories[$taxonomy]) && in_array($term->term_id, $current_categories[$taxonomy]);
                                                ?>
                                                <label class="category-option">
                                                    <input type="checkbox" name="categories[<?php echo $taxonomy; ?>][]" value="<?php echo $term->term_id; ?>" <?php checked($checked); ?>>
                                                    <span class="option-check"></span>
                                                    <span class="option-text"><?php echo esc_html($term->name); ?></span>
                                                </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-actions">
                            <div class="action-group">
                                <button class="action-icon save-icon" title="Auto-save active">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button class="action-icon delete-icon" title="Delete image" data-post-id="<?php echo $post_id; ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php else: ?>
            <div class="empty-gallery">
                <div class="empty-content">
                    <i class="fas fa-images"></i>
                    <h3>No images in your gallery yet</h3>
                    <p>Start by uploading your first images using the Upload tab</p>
                    <button class="switch-to-upload">
                        <i class="fas fa-plus-circle"></i>
                        Go to Upload
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Image Row Template -->
<template id="image-row-template">
    <div class="image-row">
        <div class="row-handle">
            <div class="drag-indicator">
                <i class="fas fa-grip-vertical"></i>
            </div>
            <label class="row-selector">
                <input type="checkbox" class="select-image">
                <span class="selector-check"></span>
            </label>
        </div>
        
        <div class="row-thumbnail">
            <img src="" alt="Preview">
            <button class="remove-image">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="row-details">
            <div class="detail-field">
                <label>Title</label>
                <input type="text" class="field-input title-input" placeholder="Enter image title" required>
            </div>
            
            <div class="detail-field">
                <label>Description</label>
                <textarea class="field-input description-input" rows="2" placeholder="Describe this image (optional)"></textarea>
            </div>
            
            <div class="detail-field categories-field">
                <label>Categories</label>
                <button type="button" class="categories-selector">
                    <span class="selector-text">Select categories</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                
                <div class="categories-dropdown" style="display: none;">
                    <?php foreach ($category_groups as $taxonomy => $group): ?>
                        <?php if (!empty($group['terms'])): ?>
                        <div class="dropdown-group">
                            <h6><?php echo $group['label']; ?></h6>
                            <div class="group-options">
                                <?php foreach ($group['terms'] as $term): ?>
                                <label class="dropdown-option">
                                    <input type="checkbox" name="categories[]" value="<?php echo $term->term_id; ?>" data-taxonomy="category">
                                    <span class="option-mark"></span>
                                    <span class="option-label"><?php echo esc_html($term->name); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</template>

<?php endif; ?>

<style>
/* Modern Agency Typography & Base */
* {
    box-sizing: border-box;
}

.agency-gallery-studio {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    min-height: 100vh;
    color: #0f172a;
    line-height: 1.6;
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Studio Header */
.studio-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 32px rgba(15, 23, 42, 0.04);
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.brand-section h1 {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.025em;
    margin: 0;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.tagline {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
    margin-left: 0.5rem;
}

.studio-nav {
    display: flex;
    gap: 0.5rem;
    background: #f8fafc;
    padding: 0.375rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #64748b;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.8);
    color: #334155;
}

.nav-item.active {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}

.count-badge {
    background: #3b82f6;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    min-width: 1.25rem;
    text-align: center;
}

/* Studio Views */
.studio-view {
    display: none;
    min-height: calc(100vh - 80px);
}

.studio-view.active {
    display: block;
}

.studio-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

/* Upload Studio */
.upload-studio {
    max-width: 800px;
    margin: 0 auto;
}

.upload-zone {
    background: #ffffff;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 4rem 2rem;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.upload-zone::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 0;
}

.upload-zone:hover,
.upload-zone.dragover {
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 16px 40px rgba(59, 130, 246, 0.12);
}

.upload-zone.dragover::before {
    opacity: 0.05;
}

.zone-content {
    position: relative;
    z-index: 1;
}

.upload-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.upload-zone h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: #0f172a;
    letter-spacing: -0.025em;
}

.upload-zone p {
    color: #64748b;
    margin: 0 0 2rem 0;
    font-size: 1rem;
}

.browse-files-btn {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.browse-files-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

.format-info {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 2rem;
    font-size: 0.875rem;
    color: #64748b;
}

/* Progress */
.upload-progress {
    background: #ffffff;
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 2rem;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.progress-title {
    font-weight: 600;
    color: #0f172a;
}

.progress-count {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.progress-bar {
    height: 8px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    width: 0%;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Bulk Settings Panel */
.bulk-settings-panel {
    background: #ffffff;
    border-radius: 16px;
    margin-top: 2rem;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    border: 2px solid #3b82f6;
}

.bulk-settings-header {
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}

.bulk-settings-header .header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.bulk-settings-header i {
    font-size: 1.5rem;
}

.bulk-settings-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
    line-height: 1;
}

.bulk-settings-header .subtitle {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-left: 0.5rem;
}

.toggle-bulk-settings {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.toggle-bulk-settings:hover {
    background: rgba(255, 255, 255, 0.3);
}

.toggle-bulk-settings.active i {
    transform: rotate(0deg);
}

.toggle-bulk-settings:not(.active) i {
    transform: rotate(180deg);
}

.bulk-settings-content {
    padding: 2rem;
}

.bulk-settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 1.5rem;
}

.bulk-field {
    display: flex;
    flex-direction: column;
}

.bulk-field label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.bulk-input {
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 1rem;
    background: #f8fafc;
    transition: all 0.2s ease;
    font-family: inherit;
}

.bulk-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.bulk-categories-selector {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
    width: 100%;
    font-family: inherit;
}

.bulk-categories-selector:hover {
    border-color: #cbd5e1;
    background: #ffffff;
}

.bulk-categories-dropdown {
    position: relative;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    margin-top: 0.5rem;
    padding: 0.75rem;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(15, 23, 42, 0.12);
}

.field-hint {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.375rem;
    margin-bottom: 0;
}

.bulk-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.action-btn.primary {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

/* Messages */
.message-display {
    margin-top: 1.5rem;
}

.message {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.message.success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.message.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Image Editor */
.image-editor {
    background: #ffffff;
    border-radius: 16px;
    margin-top: 2rem;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}

.editor-header {
    background: #f8fafc;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.editor-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
    color: #0f172a;
}

.editor-actions {
    display: flex;
    gap: 0.75rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.action-btn.danger {
    color: #dc2626;
    border-color: #fecaca;
}

.action-btn.danger:hover {
    background: #fef2f2;
}

/* Image List */
.image-list {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.image-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    display: grid;
    grid-template-columns: 60px 120px 1fr;
    gap: 1.25rem;
    align-items: start;
    transition: all 0.2s ease;
}

.image-row:hover {
    background: #ffffff;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

.row-handle {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.drag-indicator {
    color: #94a3b8;
    cursor: grab;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.drag-indicator:hover {
    background: #e2e8f0;
    color: #64748b;
}

.drag-indicator:active {
    cursor: grabbing;
}

.row-selector {
    cursor: pointer;
    display: flex;
    align-items: center;
}

.row-selector input[type="checkbox"] {
    display: none;
}

.selector-check {
    width: 18px;
    height: 18px;
    border: 2px solid #cbd5e1;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s ease;
}

.row-selector input[type="checkbox"]:checked + .selector-check {
    background: #3b82f6;
    border-color: #3b82f6;
}

.row-selector input[type="checkbox"]:checked + .selector-check::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 10px;
}

.row-thumbnail {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    aspect-ratio: 4/3;
}

.row-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-image {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    opacity: 0;
    transition: all 0.2s ease;
}

.row-thumbnail:hover .remove-image {
    opacity: 1;
}

.row-details {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
    align-items: start;
}

.detail-field {
    display: flex;
    flex-direction: column;
}

.detail-field label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.field-input {
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    background: #ffffff;
    transition: all 0.2s ease;
    font-family: inherit;
}

.field-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.categories-selector {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    width: 100%;
}

.categories-selector:hover {
    border-color: #cbd5e1;
}

.categories-field {
    position: relative;
}

.categories-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(15, 23, 42, 0.12);
    z-index: 200;
    max-height: 300px;
    overflow-y: auto;
    margin-top: 0.25rem;
}

.dropdown-group {
    padding: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

.dropdown-group:last-child {
    border-bottom: none;
}

.dropdown-group h6 {
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    margin: 0 0 0.5rem 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.group-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.25rem;
}

.dropdown-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.dropdown-option:hover {
    background: #f8fafc;
}

.dropdown-option input[type="checkbox"] {
    display: none;
}

.option-mark {
    width: 14px;
    height: 14px;
    border: 1.5px solid #cbd5e1;
    border-radius: 3px;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.dropdown-option input[type="checkbox"]:checked + .option-mark {
    background: #3b82f6;
    border-color: #3b82f6;
}

.dropdown-option input[type="checkbox"]:checked + .option-mark::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 8px;
}

/* Editor Footer */
.editor-footer {
    background: #f8fafc;
    padding: 1.5rem 2rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.upload-btn {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.upload-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

.reset-btn {
    background: #ffffff;
    color: #64748b;
    border: 1px solid #e2e8f0;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 500;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reset-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

/* Management Studio */
.management-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.header-info h2 {
    font-size: 1.875rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: #0f172a;
    letter-spacing: -0.025em;
}

.header-info p {
    color: #64748b;
    margin: 0;
    font-size: 1rem;
}

.header-stats {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.refresh-btn {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.refresh-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

/* Gallery Table */
.gallery-table {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

.table-header {
    background: #f8fafc;
    display: grid;
    grid-template-columns: 80px 100px 1fr 1fr 160px 100px;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table-body {
    display: flex;
    flex-direction: column;
}

.table-row {
    display: grid;
    grid-template-columns: 80px 100px 1fr 1fr 160px 100px;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    align-items: center;
    transition: all 0.2s ease;
    cursor: grab;
}

.table-row:hover {
    background: #f8fafc;
}

.table-row:active {
    cursor: grabbing;
}

.table-row:last-child {
    border-bottom: none;
}

.col-order {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.drag-handle {
    color: #94a3b8;
    cursor: grab;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.drag-handle:hover {
    background: #f1f5f9;
    color: #64748b;
}

.row-number {
    font-weight: 600;
    color: #64748b;
    font-size: 0.875rem;
}

.col-image .image-preview {
    position: relative;
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.2s ease;
}

.image-preview:hover .image-overlay {
    opacity: 1;
}

.view-full {
    color: white;
    text-decoration: none;
    padding: 0.375rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.view-full:hover {
    background: rgba(255, 255, 255, 0.2);
}

.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 1.5rem;
}

.inline-edit {
    border: none;
    background: transparent;
    padding: 0.5rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-family: inherit;
    color: #0f172a;
    transition: all 0.2s ease;
    width: 100%;
    resize: vertical;
}

.inline-edit:hover {
    background: #f8fafc;
}

.inline-edit:focus {
    outline: none;
    background: #ffffff;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.title-edit {
    font-weight: 500;
}

.categories-btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    width: 100%;
}

.categories-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.categories-preview {
    font-size: 0.8125rem;
    color: #64748b;
}

.col-categories {
    position: relative;
}

.categories-popup {
    position: absolute;
    top: 0;
    right: 100%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 12px 48px rgba(15, 23, 42, 0.15);
    z-index: 300;
    margin-right: 0.5rem;
    max-height: 400px;
    overflow: hidden;
    width: 450px;
    min-width: 400px;
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

.popup-header h4 {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0;
    color: #0f172a;
}

.close-popup {
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s ease;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-popup:hover {
    background: #e2e8f0;
    color: #475569;
}

.popup-content {
    max-height: 320px;
    overflow-y: auto;
    padding: 0.75rem;
}

.category-group {
    margin-bottom: 0.75rem;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 6px;
    padding: 0.5rem;
}

.category-group:last-child {
    margin-bottom: 0;
}

.category-group h5 {
    font-size: 0.6875rem;
    font-weight: 600;
    color: #475569;
    margin: 0 0 0.375rem 0;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    padding: 0 0.25rem;
}

.category-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.category-option {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
    min-width: fit-content;
}

.category-option:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.category-option input[type="checkbox"] {
    display: none;
}

.option-check {
    width: 16px;
    height: 16px;
    border: 1.5px solid #cbd5e1;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.category-option input[type="checkbox"]:checked + .option-check {
    background: #3b82f6;
    border-color: #3b82f6;
}

.category-option input[type="checkbox"]:checked + .option-check::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 9px;
}

.option-text {
    line-height: 1.3;
}

.action-group {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.action-icon {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.action-icon:hover {
    background: #f1f5f9;
}

.save-icon {
    color: #10b981;
}

.save-icon:hover {
    background: #f0fdf4;
}

.delete-icon:hover {
    background: #fef2f2;
    color: #ef4444;
}

/* Empty Gallery */
.empty-gallery {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
}

.empty-content {
    text-align: center;
    max-width: 400px;
}

.empty-content i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1.5rem;
}

.empty-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 0.75rem 0;
}

.empty-content p {
    color: #64748b;
    margin: 0 0 2rem 0;
    font-size: 1rem;
}

.switch-to-upload {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.switch-to-upload:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .table-header,
    .table-row {
        grid-template-columns: 60px 80px 1fr 120px 80px;
    }

    .col-description {
        display: none;
    }

    .row-details {
        grid-template-columns: 1fr 1fr;
    }

    .bulk-settings-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }
    
    .studio-content {
        padding: 1rem;
    }
    
    .upload-zone {
        padding: 2rem 1rem;
    }
    
    .upload-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .upload-zone h2 {
        font-size: 1.25rem;
    }
    
    .management-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .table-header {
        display: none;
    }
    
    .table-row {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8fafc;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: none;
    }
    
    .col-order {
        justify-content: space-between;
    }
    
    .col-image .image-preview {
        width: 100%;
        height: 200px;
    }
    
    .inline-edit {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 0.75rem;
    }
    
    .categories-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90vw;
        max-width: 400px;
        max-height: 80vh;
    }
    
    .row-details {
        grid-template-columns: 1fr;
    }
    
    .image-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .row-thumbnail {
        height: 200px;
    }
    
    .editor-footer {
        flex-direction: column;
    }

    .upload-btn,
    .reset-btn {
        width: 100%;
        justify-content: center;
    }

    .bulk-settings-content {
        padding: 1.5rem;
    }

    .bulk-actions {
        flex-direction: column;
    }

    .bulk-actions .action-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .zone-content {
        padding: 0 1rem;
    }
    
    .format-info {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .categories-popup {
        width: 95vw;
    }
    
    .category-options,
    .group-options {
        grid-template-columns: 1fr;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* Focus States */
.nav-item:focus,
.browse-files-btn:focus,
.upload-btn:focus,
.reset-btn:focus,
.refresh-btn:focus,
.switch-to-upload:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>