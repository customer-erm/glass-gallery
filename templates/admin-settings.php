<?php
// templates/admin-settings.php
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['glass_settings_nonce'], 'glass_settings')) {
    // Save settings
    $settings = array(
        'images_per_page' => intval($_POST['images_per_page']),
        'default_columns' => intval($_POST['default_columns']),
        'enable_lightbox' => isset($_POST['enable_lightbox']),
        'auto_approve_uploads' => isset($_POST['auto_approve_uploads']),
        'allowed_file_types' => sanitize_text_field($_POST['allowed_file_types']),
        'max_file_size' => intval($_POST['max_file_size']),
        'require_categories' => isset($_POST['require_categories']),
        'enable_comments' => isset($_POST['enable_comments']),
        'watermark_enabled' => isset($_POST['watermark_enabled']),
        'watermark_text' => sanitize_text_field($_POST['watermark_text']),
        'analytics_enabled' => isset($_POST['analytics_enabled']),
        'upload_password_enabled' => isset($_POST['upload_password_enabled']),
        'upload_password' => sanitize_text_field($_POST['upload_password'])
    );
    
    update_option('glass_gallery_settings', $settings);
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}

// Get current settings
$default_settings = array(
    'images_per_page' => 20,
    'default_columns' => 4,
    'enable_lightbox' => true,
    'auto_approve_uploads' => false,
    'allowed_file_types' => 'jpg,jpeg,png,gif',
    'max_file_size' => 10,
    'require_categories' => false,
    'enable_comments' => false,
    'watermark_enabled' => false,
    'watermark_text' => '',
    'analytics_enabled' => true,
    'upload_password_enabled' => false,
    'upload_password' => ''
);

$settings = wp_parse_args(get_option('glass_gallery_settings', array()), $default_settings);
?>

<div class="wrap">
    <h1>Glass Gallery Settings</h1>
    
    <form method="post" action="" class="glass-settings-form">
        <?php wp_nonce_field('glass_settings', 'glass_settings_nonce'); ?>
        
        <!-- Display Settings -->
        <div class="settings-section">
            <h2>Display Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Images Per Page</th>
                    <td>
                        <input type="number" name="images_per_page" value="<?php echo esc_attr($settings['images_per_page']); ?>" min="1" max="100" />
                        <p class="description">Number of images to load initially and when clicking "Load More"</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Default Columns</th>
                    <td>
                        <select name="default_columns">
                            <option value="2" <?php selected($settings['default_columns'], 2); ?>>2 Columns</option>
                            <option value="3" <?php selected($settings['default_columns'], 3); ?>>3 Columns</option>
                            <option value="4" <?php selected($settings['default_columns'], 4); ?>>4 Columns</option>
                            <option value="5" <?php selected($settings['default_columns'], 5); ?>>5 Columns</option>
                        </select>
                        <p class="description">Default number of columns in the gallery grid</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enable Lightbox</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_lightbox" <?php checked($settings['enable_lightbox']); ?> />
                            Enable Fancybox lightbox for image viewing
                        </label>
                        <p class="description">When enabled, clicking images opens them in a modal overlay</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Upload Settings -->
        <div class="settings-section">
            <h2>Upload Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Auto-Approve Uploads</th>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_approve_uploads" <?php checked($settings['auto_approve_uploads']); ?> />
                            Automatically publish user-uploaded images
                        </label>
                        <p class="description">When disabled, uploaded images require manual approval</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Allowed File Types</th>
                    <td>
                        <input type="text" name="allowed_file_types" value="<?php echo esc_attr($settings['allowed_file_types']); ?>" class="regular-text" />
                        <p class="description">Comma-separated list of allowed file extensions (e.g., jpg,jpeg,png,gif)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Max File Size (MB)</th>
                    <td>
                        <input type="number" name="max_file_size" value="<?php echo esc_attr($settings['max_file_size']); ?>" min="1" max="100" />
                        <p class="description">Maximum file size allowed for uploads (in megabytes)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Require Categories</th>
                    <td>
                        <label>
                            <input type="checkbox" name="require_categories" <?php checked($settings['require_categories']); ?> />
                            Require users to select at least one category when uploading
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Advanced Settings -->
        <div class="settings-section">
            <h2>Advanced Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Enable Comments</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_comments" <?php checked($settings['enable_comments']); ?> />
                            Allow comments on gallery images
                        </label>
                        <p class="description">Enables WordPress commenting system for gallery items</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Watermark Images</th>
                    <td>
                        <label>
                            <input type="checkbox" name="watermark_enabled" <?php checked($settings['watermark_enabled']); ?> />
                            Add watermark to uploaded images
                        </label>
                        <p class="description">Automatically adds watermark to prevent unauthorized use</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Watermark Text</th>
                    <td>
                        <input type="text" name="watermark_text" value="<?php echo esc_attr($settings['watermark_text']); ?>" class="regular-text" />
                        <p class="description">Text to use as watermark (leave empty to use site name)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Analytics</th>
                    <td>
                        <label>
                            <input type="checkbox" name="analytics_enabled" <?php checked($settings['analytics_enabled']); ?> />
                            Track image views and user interactions
                        </label>
                        <p class="description">Enables basic analytics tracking for gallery performance</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Password Protect Upload Portal</th>
                    <td>
                        <label>
                            <input type="checkbox" name="upload_password_enabled" <?php checked($settings['upload_password_enabled']); ?> />
                            Require password to access upload form
                        </label>
                        <p class="description">When enabled, users must enter a password to access the upload form</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Upload Portal Password</th>
                    <td>
                        <input type="text" name="upload_password" value="<?php echo esc_attr($settings['upload_password']); ?>" class="regular-text" />
                        <p class="description">Password required to access the upload form (only used if password protection is enabled)</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Default Categories -->
        <div class="settings-section">
            <h2>Default Categories</h2>
            <p class="section-description">Add default categories that will be pre-populated when the plugin is activated. Enter one per line.</p>
            
            <div class="categories-grid">
                <div class="category-group">
                    <h4>Product Types</h4>
                    <textarea name="default_product_types" rows="8" class="large-text">Shower Enclosures
Wine Cellars
Room Partitions
Stair Rails & Wind Screens
Mirrors
Sauna Glass
Furnishings
Sneeze Guards</textarea>
                </div>
                
                <div class="category-group">
                    <h4>Shower Shapes</h4>
                    <textarea name="default_shower_shapes" rows="8" class="large-text">Single Door
Inline
Corner
Neo-Angle
Shower Screen
Glass on Tub</textarea>
                </div>
                
                <div class="category-group">
                    <h4>Functions</h4>
                    <textarea name="default_functions" rows="6" class="large-text">Swinging
Single Sliding
Double Sliding
Bi-Fold</textarea>
                </div>
                
                <div class="category-group">
                    <h4>Glass Types</h4>
                    <textarea name="default_glass_types" rows="8" class="large-text">Regular Clear (Standard)
Low Iron (Ultra Clear)
Satin/Etched
Rain or Niagara
Color
Mirror
Reeded Glass</textarea>
                </div>
            </div>
            
            <p class="description">
                <strong>Note:</strong> These categories will only be added if they don't already exist. 
                <button type="button" class="button" id="populate-categories">Populate Categories Now</button>
            </p>
        </div>
        
        <!-- Shortcode Generator -->
        <div class="settings-section">
            <h2>Shortcode Generator</h2>
            <div class="shortcode-generator">
                <div class="generator-controls">
                    <h4>Gallery Display Options</h4>
                    <table class="form-table">
                        <tr>
                            <th>Show Filters</th>
                            <td>
                                <select id="sc_show_filters">
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Number of Columns</th>
                            <td>
                                <select id="sc_columns">
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4" selected>4</option>
                                    <option value="5">5</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Image Limit</th>
                            <td>
                                <input type="number" id="sc_limit" placeholder="-1 (unlimited)" min="-1" />
                            </td>
                        </tr>
                        <tr>
                            <th>Featured Only</th>
                            <td>
                                <select id="sc_featured">
                                    <option value="false">No</option>
                                    <option value="true">Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <button type="button" class="button" id="generate-shortcode">Generate Shortcode</button>
                </div>
                
                <div class="generated-shortcode">
                    <h4>Generated Shortcode</h4>
                    <input type="text" id="generated_shortcode" value="[glass_gallery]" class="large-text" readonly />
                    <button type="button" class="button" id="copy-shortcode">Copy to Clipboard</button>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" class="button-primary" value="Save Settings" />
        </p>
    </form>
</div>

<style>
.glass-settings-form {
    max-width: 1000px;
}

.settings-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.settings-section h2 {
    margin-top: 0;
    color: #1d2327;
    border-bottom: 1px solid #c3c4c7;
    padding-bottom: 10px;
}

.section-description {
    color: #646970;
    font-style: italic;
    margin-bottom: 15px;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}

.category-group h4 {
    margin-top: 0;
    margin-bottom: 8px;
    color: #1d2327;
}

.shortcode-generator {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 15px;
}

.generator-controls h4,
.generated-shortcode h4 {
    margin-top: 0;
    color: #1d2327;
}

#generated_shortcode {
    margin-bottom: 10px;
}

#copy-shortcode {
    background: #00a32a;
    color: #fff;
    border-color: #00a32a;
}

#copy-shortcode:hover {
    background: #008a20;
    border-color: #008a20;
}

@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .shortcode-generator {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shortcode generator
    const generateBtn = document.getElementById('generate-shortcode');
    const shortcodeInput = document.getElementById('generated_shortcode');
    const copyBtn = document.getElementById('copy-shortcode');
    
    generateBtn.addEventListener('click', function() {
        let shortcode = '[glass_gallery';
        
        const showFilters = document.getElementById('sc_show_filters').value;
        const columns = document.getElementById('sc_columns').value;
        const limit = document.getElementById('sc_limit').value;
        const featured = document.getElementById('sc_featured').value;
        
        if (showFilters !== 'true') {
            shortcode += ' show_filters="' + showFilters + '"';
        }
        
        if (columns !== '4') {
            shortcode += ' columns="' + columns + '"';
        }
        
        if (limit && limit !== '-1') {
            shortcode += ' limit="' + limit + '"';
        }
        
        if (featured === 'true') {
            shortcode += ' featured_only="true"';
        }
        
        shortcode += ']';
        shortcodeInput.value = shortcode;
    });
    
    copyBtn.addEventListener('click', function() {
        shortcodeInput.select();
        document.execCommand('copy');
        
        const originalText = copyBtn.textContent;
        copyBtn.textContent = 'Copied!';
        setTimeout(function() {
            copyBtn.textContent = originalText;
        }, 2000);
    });
    
    // Populate categories button
    const populateBtn = document.getElementById('populate-categories');
    if (populateBtn) {
        populateBtn.addEventListener('click', function() {
            if (confirm('This will add default categories to your gallery. Continue?')) {
                // AJAX call to populate categories
                const formData = new FormData();
                formData.append('action', 'populate_default_categories');
                formData.append('nonce', '<?php echo wp_create_nonce("populate_categories"); ?>');
                
                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Default categories have been added successfully!');
                    } else {
                        alert('Error adding categories: ' + data.data);
                    }
                })
                .catch(error => {
                    alert('Network error occurred');
                });
            }
        });
    }
});
</script>