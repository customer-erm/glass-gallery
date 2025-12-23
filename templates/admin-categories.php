<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>Gallery Categories</h1>
    
    <div class="category-notice">
        <p><strong>Note:</strong> This gallery uses standard WordPress categories. Manage categories using the native WordPress system:</p>
        <ul>
            <li>Go to <strong>Posts → Categories</strong> in the WordPress admin menu</li>
            <li>Create parent categories (e.g., "Shower Enclosures", "Commercial Glass")</li>
            <li>Create child categories under parents (e.g., "Frameless" under "Shower Enclosures")</li>
            <li>All categories will automatically work with your gallery filter system</li>
        </ul>
        <a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" class="button button-primary">
            Manage Categories in WordPress
        </a>
    </div>
    
    <div class="category-preview">
        <h2>Current Gallery Categories</h2>
        <?php
        $categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false,
            'parent' => 0
        ));
        
        if (!empty($categories) && !is_wp_error($categories)):
            foreach ($categories as $parent_cat):
                $child_cats = get_terms(array(
                    'taxonomy' => 'category',
                    'hide_empty' => false,
                    'parent' => $parent_cat->term_id
                ));
        ?>
            <div class="category-group">
                <h3><?php echo esc_html($parent_cat->name); ?> (<?php echo $parent_cat->count; ?>)</h3>
                <?php if (!empty($child_cats)): ?>
                <ul>
                    <?php foreach ($child_cats as $child): ?>
                    <li><?php echo esc_html($child->name); ?> (<?php echo $child->count; ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <p>No categories found. <a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>">Create your first category</a>.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.category-notice {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-left: 4px solid #2271b1;
    padding: 20px;
    margin: 20px 0;
}
.category-notice ul {
    margin-left: 20px;
}
.category-preview {
    background: #fff;
    border: 1px solid #c3c4c7;
    padding: 20px;
    margin: 20px 0;
}
.category-group {
    margin-bottom: 20px;
    padding: 15px;
    background: #f6f7f7;
    border-radius: 4px;
}
.category-group h3 {
    margin: 0 0 10px 0;
    color: #1d2327;
}
.category-group ul {
    margin: 10px 0 0 20px;
}
</style>