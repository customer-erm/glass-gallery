<?php
// templates/admin-dashboard.php
if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
$total_images = wp_count_posts('glass_gallery_item')->publish;
$total_categories = 0;
$taxonomies = get_object_taxonomies('glass_gallery_item');
foreach ($taxonomies as $taxonomy) {
    $total_categories += wp_count_terms($taxonomy);
}

$recent_images = get_posts(array(
    'post_type' => 'glass_gallery_item',
    'posts_per_page' => 5,
    'post_status' => 'publish'
));
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Glass Gallery Pro Dashboard</h1>
    
    <div class="glass-admin-dashboard">
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🖼️</div>
                <div class="stat-content">
                    <h3><?php echo number_format($total_images); ?></h3>
                    <p>Total Images</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏷️</div>
                <div class="stat-content">
                    <h3><?php echo number_format($total_categories); ?></h3>
                    <p>Total Categories</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo number_format(count_users()['total_users']); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3>Active</h3>
                    <p>Gallery Status</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="<?php echo admin_url('post-new.php?post_type=glass_gallery_item'); ?>" class="action-button primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    Add New Image
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=glass_gallery_item'); ?>" class="action-button">
                    <span class="dashicons dashicons-images-alt2"></span>
                    View All Images
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=glass-gallery-categories'); ?>" class="action-button">
                    <span class="dashicons dashicons-category"></span>
                    Manage Categories
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=glass-gallery-settings'); ?>" class="action-button">
                    <span class="dashicons dashicons-admin-settings"></span>
                    Settings
                </a>
            </div>
        </div>
        
        <!-- Recent Images -->
        <div class="recent-images">
            <h2>Recent Images</h2>
            <?php if (!empty($recent_images)): ?>
                <div class="images-list">
                    <?php foreach ($recent_images as $image): ?>
                        <div class="image-item">
                            <div class="image-thumbnail">
                                <?php if (has_post_thumbnail($image->ID)): ?>
                                    <?php echo get_the_post_thumbnail($image->ID, 'thumbnail'); ?>
                                <?php else: ?>
                                    <div class="no-image">📷</div>
                                <?php endif; ?>
                            </div>
                            <div class="image-info">
                                <h4><a href="<?php echo get_edit_post_link($image->ID); ?>"><?php echo esc_html($image->post_title); ?></a></h4>
                                <p class="image-date">Uploaded: <?php echo get_the_date('M j, Y', $image->ID); ?></p>
                                <p class="image-author">By: <?php echo get_the_author_meta('display_name', $image->post_author); ?></p>
                            </div>
                            <div class="image-actions">
                                <a href="<?php echo get_edit_post_link($image->ID); ?>" class="button-small">Edit</a>
                                <a href="<?php echo get_permalink($image->ID); ?>" class="button-small" target="_blank">View</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No images uploaded yet. <a href="<?php echo admin_url('post-new.php?post_type=glass_gallery_item'); ?>">Add your first image</a>!</p>
            <?php endif; ?>
        </div>
        
        <!-- Shortcode Information -->
        <div class="shortcode-info">
            <h2>Shortcode Usage</h2>
            <div class="shortcode-examples">
                <div class="shortcode-example">
                    <h4>Basic Gallery Display</h4>
                    <code>[glass_gallery]</code>
                    <p>Displays the full gallery with filters</p>
                </div>
                
                <div class="shortcode-example">
                    <h4>Gallery Without Filters</h4>
                    <code>[glass_gallery show_filters="false"]</code>
                    <p>Shows gallery grid only, no sidebar filters</p>
                </div>
                
                <div class="shortcode-example">
                    <h4>Featured Images Only</h4>
                    <code>[glass_gallery featured_only="true" limit="12"]</code>
                    <p>Shows only featured images, limited to 12</p>
                </div>
                
                <div class="shortcode-example">
                    <h4>Customer Upload Form</h4>
                    <code>[glass_upload_form]</code>
                    <p>Displays upload form for logged-in users</p>
                </div>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="system-info">
            <h2>System Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Plugin Version:</strong>
                    <?php echo GLASS_GALLERY_VERSION; ?>
                </div>
                <div class="info-item">
                    <strong>WordPress Version:</strong>
                    <?php echo get_bloginfo('version'); ?>
                </div>
                <div class="info-item">
                    <strong>PHP Version:</strong>
                    <?php echo PHP_VERSION; ?>
                </div>
                <div class="info-item">
                    <strong>Max Upload Size:</strong>
                    <?php echo size_format(wp_max_upload_size()); ?>
                </div>
            </div>
        </div>
        
    </div>
</div>

<style>
.glass-admin-dashboard {
    max-width: 1200px;
    margin: 20px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.stat-icon {
    font-size: 2em;
    margin-right: 15px;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 24px;
    color: #1d2327;
}

.stat-content p {
    margin: 0;
    color: #646970;
    font-size: 14px;
}

.quick-actions,
.recent-images,
.shortcode-info,
.system-info {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.quick-actions h2,
.recent-images h2,
.shortcode-info h2,
.system-info h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #1d2327;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
}

.action-button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    background: #f6f7f7;
    border: 1px solid #c3c4c7;
    border-radius: 6px;
    text-decoration: none;
    color: #2271b1;
    font-weight: 600;
    transition: all 0.2s;
}

.action-button:hover {
    background: #2271b1;
    color: #fff;
    text-decoration: none;
}

.action-button.primary {
    background: #2271b1;
    color: #fff;
}

.action-button.primary:hover {
    background: #135e96;
}

.action-button .dashicons {
    margin-right: 8px;
}

.images-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.image-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
    border: 1px solid #e2e4e7;
}

.image-thumbnail {
    width: 60px;
    height: 60px;
    margin-right: 15px;
    border-radius: 4px;
    overflow: hidden;
}

.image-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e2e4e7;
    color: #646970;
    font-size: 24px;
}

.image-info {
    flex: 1;
}

.image-info h4 {
    margin: 0 0 5px 0;
}

.image-info h4 a {
    text-decoration: none;
    color: #1d2327;
}

.image-info h4 a:hover {
    color: #2271b1;
}

.image-date,
.image-author {
    margin: 2px 0;
    color: #646970;
    font-size: 13px;
}

.image-actions {
    display: flex;
    gap: 8px;
}

.button-small {
    padding: 4px 8px;
    background: #2271b1;
    color: #fff;
    text-decoration: none;
    border-radius: 3px;
    font-size: 12px;
}

.button-small:hover {
    background: #135e96;
    color: #fff;
}

.shortcode-examples {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.shortcode-example {
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
    border: 1px solid #e2e4e7;
}

.shortcode-example h4 {
    margin: 0 0 8px 0;
    color: #1d2327;
}

.shortcode-example code {
    display: block;
    background: #2271b1;
    color: #fff;
    padding: 8px 12px;
    border-radius: 4px;
    margin: 8px 0;
    font-family: 'Courier New', monospace;
    font-size: 13px;
}

.shortcode-example p {
    margin: 8px 0 0 0;
    color: #646970;
    font-size: 14px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
    border: 1px solid #e2e4e7;
}

.info-item strong {
    display: block;
    margin-bottom: 5px;
    color: #1d2327;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .shortcode-examples {
        grid-template-columns: 1fr;
    }
    
    .image-item {
        flex-direction: column;
        text-align: center;
    }
    
    .image-thumbnail {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>