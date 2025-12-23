<?php
/**
 * Modern Gallery Display Template
 * Version: 2.0.0 - Complete Rebuild
 */
if (!defined('ABSPATH')) exit;

// Get all categories
$all_categories = get_terms(array(
    'taxonomy' => 'category',
    'hide_empty' => false,
    'orderby' => 'name'
));

// Count images per category
if (!empty($all_categories) && !is_wp_error($all_categories)) {
    foreach ($all_categories as $cat) {
        $count_query = new WP_Query(array(
            'post_type' => 'glass_gallery_item',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(array(
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $cat->term_id
            )),
            'fields' => 'ids'
        ));
        $cat->count = $count_query->found_posts;
        wp_reset_postdata();
    }
}

// Query all images (we'll filter client-side for speed)
$gallery_query = new WP_Query(array(
    'post_type' => 'glass_gallery_item',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'rand'
));
?>

<div class="ggp-gallery">

    <?php if ($atts['show_filters'] === 'true' && !empty($all_categories)): ?>
    <!-- Filters Sidebar -->
    <aside class="ggp-sidebar">
        <div class="ggp-filters">
            <div class="ggp-filter-header">
                <h3>Filter Projects</h3>
                <button type="button" class="ggp-clear-btn" id="ggpClearAll">Clear All</button>
            </div>

            <div class="ggp-search">
                <input type="text" id="ggpSearch" placeholder="Search..." autocomplete="off">
            </div>

            <div class="ggp-categories">
                <?php foreach ($all_categories as $cat): ?>
                    <?php
                    // Skip "Products" parent category and only show categories with images
                    if ($cat->count > 0 && strtolower($cat->name) !== 'products'):
                    ?>
                    <label class="ggp-cat-item">
                        <input type="checkbox" value="<?php echo $cat->term_id; ?>" data-cat-name="<?php echo esc_attr($cat->name); ?>">
                        <span class="ggp-cat-name" title="<?php echo esc_attr($cat->name); ?>"><?php echo esc_html($cat->name); ?></span>
                        <span class="ggp-count"><?php echo $cat->count; ?></span>
                    </label>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="ggp-main">

        <div class="ggp-results">
            <p><strong id="ggpResultCount">0</strong> images</p>
        </div>

        <!-- Grid -->
        <div class="ggp-grid" id="ggpGrid">
            <?php if ($gallery_query->have_posts()):
                while ($gallery_query->have_posts()): $gallery_query->the_post();
                    $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                    $full_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    $title = get_the_title();
                    $caption = get_post_meta(get_the_ID(), '_glass_image_caption', true);

                    // Get category IDs
                    $cats = get_the_terms(get_the_ID(), 'category');
                    $cat_ids = array();
                    $cat_names = array();
                    if ($cats && !is_wp_error($cats)) {
                        foreach ($cats as $c) {
                            $cat_ids[] = $c->term_id;
                            $cat_names[] = $c->name;
                        }
                    }

                    if ($image_url): ?>
            <div class="ggp-item"
                 data-id="<?php echo get_the_ID(); ?>"
                 data-cats="<?php echo esc_attr(json_encode($cat_ids)); ?>"
                 data-title="<?php echo esc_attr($title); ?>"
                 data-full="<?php echo esc_url($full_url); ?>">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                <div class="ggp-overlay">
                    <h4><?php echo esc_html($title); ?></h4>
                    <?php if (!empty($cat_names)): ?>
                        <p><?php echo esc_html(implode(', ', $cat_names)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
                    <?php endif;
                endwhile;
                wp_reset_postdata();
            endif; ?>
        </div>

        <div class="ggp-no-results" id="ggpNoResults" style="display:none;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <h3>No images found</h3>
            <p>Try different filters or search terms</p>
        </div>

    </main>

</div>

<!-- Modal -->
<div class="ggp-modal" id="ggpModal">
    <button class="ggp-modal-close" id="ggpModalClose">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <button class="ggp-modal-prev" id="ggpModalPrev">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15,18 9,12 15,6"></polyline>
        </svg>
    </button>

    <button class="ggp-modal-next" id="ggpModalNext">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
    </button>

    <div class="ggp-modal-container">
        <div class="ggp-modal-image-area">
            <img id="ggpModalImg" src="" alt="">
        </div>

        <div class="ggp-modal-sidebar">
            <div class="ggp-modal-header">
                <div class="ggp-modal-categories" id="ggpModalCategories"></div>
                <h2 id="ggpModalTitle"></h2>
                <p class="ggp-modal-counter" id="ggpModalCounter"></p>
            </div>
        </div>
    </div>
</div>
