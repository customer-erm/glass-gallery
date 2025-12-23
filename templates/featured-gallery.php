<?php
/**
 * Featured Gallery Template
 * Displays random images with optional category filter and link to full gallery
 * Supports both grid and carousel modes with modal functionality
 */
if (!defined('ABSPATH')) exit;

// Build query args
$query_args = array(
    'post_type' => 'glass_gallery_item',
    'post_status' => 'publish',
    'posts_per_page' => intval($atts['limit']),
    'orderby' => 'rand'
);

// Add category filter if specified
if (!empty($atts['category'])) {
    $query_args['tax_query'] = array(
        array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['category'])
        )
    );
}

$featured_query = new WP_Query($query_args);
$is_carousel = ($atts['carousel'] === 'true' || $atts['carousel'] === '1');
$unique_id = 'ggp-featured-' . uniqid();

// Gather all images data for modal
$images_data = array();
if ($featured_query->have_posts()) {
    while ($featured_query->have_posts()) {
        $featured_query->the_post();
        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
        $full_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        $caption = get_post_meta(get_the_ID(), '_glass_image_caption', true);

        // Get categories for this image
        $cats = get_the_terms(get_the_ID(), 'category');
        $cat_names = array();
        if ($cats && !is_wp_error($cats)) {
            foreach ($cats as $c) {
                $cat_names[] = $c->name;
            }
        }

        if ($image_url) {
            $images_data[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'image_url' => $image_url,
                'full_image_url' => $full_image_url,
                'caption' => $caption,
                'categories' => $cat_names
            );
        }
    }
    wp_reset_postdata();
}
?>

<div class="ggp-featured-gallery <?php echo $is_carousel ? 'ggp-carousel-mode' : 'ggp-grid-mode'; ?>" id="<?php echo esc_attr($unique_id); ?>" data-images='<?php echo esc_attr(json_encode($images_data)); ?>'>
    <?php if (!empty($images_data)): ?>

        <?php if ($is_carousel): ?>
            <!-- 5-Column Vertical Carousel Mode -->
            <div class="ggp-carousel-wrapper">
                <button class="ggp-carousel-nav ggp-carousel-prev" aria-label="Previous image">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>

                <div class="ggp-carousel-container">
                    <div class="ggp-carousel-track">
                        <?php foreach ($images_data as $index => $image): ?>
                            <div class="ggp-carousel-slide" data-index="<?php echo $index; ?>">
                                <div class="ggp-carousel-image-wrap">
                                    <img src="<?php echo esc_url($image['image_url']); ?>"
                                         alt="<?php echo esc_attr($image['title']); ?>"
                                         loading="lazy"
                                         class="ggp-carousel-image">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button class="ggp-carousel-nav ggp-carousel-next" aria-label="Next image">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

        <?php else: ?>
            <!-- Grid Mode -->
            <div class="ggp-featured-grid">
                <?php foreach ($images_data as $index => $image): ?>
                    <div class="ggp-featured-item" data-index="<?php echo $index; ?>">
                        <img src="<?php echo esc_url($image['image_url']); ?>"
                             alt="<?php echo esc_attr($image['title']); ?>"
                             loading="lazy">
                        <div class="ggp-featured-overlay">
                            <h4><?php echo esc_html($image['title']); ?></h4>
                            <svg class="ggp-zoom-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Modal (matching main gallery design with sidebar) -->
        <div class="ggp-modal" role="dialog" aria-modal="true" aria-hidden="true">
            <button class="ggp-modal-close" aria-label="Close modal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <button class="ggp-modal-prev" aria-label="Previous image">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>

            <button class="ggp-modal-next" aria-label="Next image">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            <div class="ggp-modal-container">
                <div class="ggp-modal-image-area">
                    <img src="" alt="" class="ggp-modal-image" id="ggp-modal-image">
                </div>

                <div class="ggp-modal-sidebar">
                    <div class="ggp-modal-header">
                        <div class="ggp-modal-categories" id="ggp-modal-categories"></div>
                        <h2 class="ggp-modal-title"></h2>
                        <p class="ggp-modal-counter">
                            <span class="ggp-modal-current">1</span> / <span class="ggp-modal-total"><?php echo count($images_data); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <p class="ggp-featured-empty">No images found.</p>
    <?php endif; ?>
</div>
