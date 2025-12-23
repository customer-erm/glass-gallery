// assets/js/admin.js
(function($) {
    'use strict';

    $(document).ready(function() {
        initializeAdminFeatures();
        initializeCategoryManagement();
        initializeSettingsPage();
        initializeDashboard();
    });

    function initializeAdminFeatures() {
        // General admin initialization
        console.log('Glass Gallery Pro Admin initialized');
        
        // Add loading states to buttons
        $('.glass-admin-button').on('click', function() {
            const $btn = $(this);
            if (!$btn.hasClass('loading')) {
                $btn.addClass('loading').append('<span class="spinner"></span>');
            }
        });
    }

    function initializeCategoryManagement() {
        // Tab switching functionality
        $('.category-tab').on('click', function() {
            const taxonomy = $(this).data('taxonomy');
            
            // Update active tab
            $('.category-tab').removeClass('active');
            $(this).addClass('active');
            
            // Update active content
            $('.category-content').removeClass('active');
            $('#content-' + taxonomy).addClass('active');
        });

        // Auto-generate slug from term name
        $(document).on('input', '#edit-term-name', function() {
            const name = $(this).val();
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            $('#edit-term-slug').val(slug);
        });

        // Add term form validation
        $('.add-term-form').on('submit', function(e) {
            const termName = $(this).find('.new-term-input').val().trim();
            if (!termName) {
                e.preventDefault();
                alert('Please enter a term name');
                return false;
            }
        });

        // Bulk actions handler
        $('#bulk-apply-btn').on('click', function() {
            const action = $('#bulk-action-select').val();
            if (!action) {
                alert('Please select an action');
                return;
            }

            const checkedTerms = $('.term-item input[type="checkbox"]:checked');
            if (checkedTerms.length === 0) {
                alert('Please select at least one term');
                return;
            }

            if (action === 'delete') {
                if (confirm('Are you sure you want to delete the selected terms? This action cannot be undone.')) {
                    bulkDeleteTerms(checkedTerms);
                }
            } else if (action === 'export') {
                bulkExportTerms(checkedTerms);
            }
        });
    }

    function initializeSettingsPage() {
        // Shortcode generator
        $('#generate-shortcode').on('click', function() {
            let shortcode = '[glass_gallery';
            
            const showFilters = $('#sc_show_filters').val();
            const columns = $('#sc_columns').val();
            const limit = $('#sc_limit').val();
            const featured = $('#sc_featured').val();
            
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
            $('#generated_shortcode').val(shortcode);
        });

        // Copy shortcode to clipboard
        $('#copy-shortcode').on('click', function() {
            const shortcodeInput = $('#generated_shortcode')[0];
            shortcodeInput.select();
            shortcodeInput.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                const $btn = $(this);
                const originalText = $btn.text();
                $btn.text('Copied!').addClass('copied');
                
                setTimeout(function() {
                    $btn.text(originalText).removeClass('copied');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy shortcode:', err);
                alert('Failed to copy shortcode. Please copy manually.');
            }
        });

        // Settings form validation
        $('.glass-settings-form').on('submit', function() {
            const imagesPerPage = parseInt($('input[name="images_per_page"]').val());
            const maxFileSize = parseInt($('input[name="max_file_size"]').val());
            
            if (imagesPerPage < 1 || imagesPerPage > 100) {
                alert('Images per page must be between 1 and 100');
                return false;
            }
            
            if (maxFileSize < 1 || maxFileSize > 100) {
                alert('Max file size must be between 1 and 100 MB');
                return false;
            }
            
            return true;
        });
    }

    function initializeDashboard() {
        // Dashboard statistics animation
        $('.stat-card').each(function(index) {
            $(this).delay(index * 100).animate({
                opacity: 1,
                transform: 'translateY(0)'
            }, 300);
        });

        // Quick action button effects
        $('.action-button').hover(
            function() {
                $(this).find('.dashicons').addClass('animate-bounce');
            },
            function() {
                $(this).find('.dashicons').removeClass('animate-bounce');
            }
        );

        // Recent images lazy loading effect
        $('.image-item').each(function(index) {
            $(this).delay(index * 50).fadeIn(200);
        });
    }

    function bulkDeleteTerms(terms) {
        let completed = 0;
        const total = terms.length;
        
        terms.each(function() {
            const $term = $(this).closest('.term-item');
            const termId = $term.data('term-id');
            const taxonomy = $term.find('.delete-term').data('taxonomy');
            
            const formData = new FormData();
            formData.append('action', 'delete_gallery_term');
            formData.append('nonce', getAdminNonce());
            formData.append('term_id', termId);
            formData.append('taxonomy', taxonomy);
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    completed++;
                    if (response.success) {
                        $term.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                    
                    if (completed === total) {
                        showAdminMessage('Bulk delete completed!', 'success');
                    }
                },
                error: function() {
                    completed++;
                    if (completed === total) {
                        showAdminMessage('Bulk delete completed with some errors', 'warning');
                    }
                }
            });
        });
    }

    function bulkExportTerms(terms) {
        const exportData = {
            version: '1.0',
            export_date: new Date().toISOString(),
            selected_terms: []
        };
        
        terms.each(function() {
            const $term = $(this).closest('.term-item');
            const termData = {
                id: $term.data('term-id'),
                name: $term.find('h4').text(),
                taxonomy: $term.find('.edit-term').data('taxonomy')
            };
            exportData.selected_terms.push(termData);
        });
        
        const dataStr = JSON.stringify(exportData, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = 'glass-gallery-selected-terms-' + new Date().toISOString().split('T')[0] + '.json';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showAdminMessage('Selected terms exported successfully!', 'success');
    }

    function showAdminMessage(message, type) {
        const messageClass = type === 'success' ? 'notice-success' : 
                           type === 'error' ? 'notice-error' : 
                           type === 'warning' ? 'notice-warning' : 'notice-info';
        
        const messageHtml = `
            <div class="notice ${messageClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `;
        
        $('.wrap > h1').after(messageHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.notice').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    function getAdminNonce() {
        // Try to get nonce from various possible sources
        return $('input[name="glass_gallery_nonce"]').val() ||
               $('input[name="_wpnonce"]').val() ||
               $('#_wpnonce').val() ||
               '';
    }

    // Image upload preview for admin
    $(document).on('change', 'input[type="file"][accept*="image"]', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = $('<img>')
                    .attr('src', e.target.result)
                    .css({
                        'max-width': '200px',
                        'max-height': '200px',
                        'border-radius': '5px',
                        'margin-top': '10px'
                    });
                
                $(file.target).siblings('.image-preview').remove();
                $(file.target).after($('<div class="image-preview">').append(preview));
            };
            reader.readAsDataURL(file);
        }
    });

    // Sortable functionality for term ordering (if needed)
    if ($.fn.sortable) {
        $('.terms-list').sortable({
            items: '.term-item',
            placeholder: 'term-placeholder',
            update: function(event, ui) {
                const termOrder = [];
                $(this).children('.term-item').each(function(index) {
                    termOrder.push({
                        id: $(this).data('term-id'),
                        order: index
                    });
                });
                
                // Save new order via AJAX
                savetermOrder(termOrder);
            }
        });
    }

    function savetermOrder(termOrder) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'save_term_order',
                nonce: getAdminNonce(),
                term_order: JSON.stringify(termOrder)
            },
            success: function(response) {
                if (response.success) {
                    showAdminMessage('Term order updated!', 'success');
                }
            }
        });
    }

    // Keyboard shortcuts for admin
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S to save (prevent default and trigger save)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const $saveBtn = $('.button-primary[type="submit"]');
            if ($saveBtn.length) {
                $saveBtn.click();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            $('.modal-overlay, .glass-modal').fadeOut();
        }
    });

    // Auto-save draft functionality for long forms
    let autoSaveTimer;
    $('form.auto-save').on('input change', function() {
        clearTimeout(autoSaveTimer);
        const $form = $(this);
        
        autoSaveTimer = setTimeout(function() {
            const formData = $form.serialize() + '&action=auto_save_form';
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('.auto-save-indicator').text('Draft saved').fadeIn().delay(2000).fadeOut();
                    }
                }
            });
        }, 3000); // Save after 3 seconds of inactivity
    });

    // Enhanced tooltips for admin elements
    $('[data-tooltip]').each(function() {
        const $element = $(this);
        const tooltipText = $element.data('tooltip');
        
        $element.hover(
            function() {
                const $tooltip = $('<div class="glass-tooltip">')
                    .text(tooltipText)
                    .css({
                        position: 'absolute',
                        background: '#333',
                        color: '#fff',
                        padding: '5px 10px',
                        borderRadius: '4px',
                        fontSize: '12px',
                        zIndex: 9999,
                        whiteSpace: 'nowrap'
                    });
                
                $('body').append($tooltip);
                
                const offset = $element.offset();
                $tooltip.css({
                    top: offset.top - $tooltip.outerHeight() - 5,
                    left: offset.left + ($element.outerWidth() - $tooltip.outerWidth()) / 2
                });
            },
            function() {
                $('.glass-tooltip').remove();
            }
        );
    });

    // Color scheme detection and adaptive styling
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        $('body').addClass('glass-dark-mode');
    }

})(jQuery);

// Utility functions
window.GlassGalleryAdmin = {
    showMessage: function(message, type) {
        // Public method to show admin messages
        const messageClass = type === 'success' ? 'notice-success' : 
                           type === 'error' ? 'notice-error' : 
                           type === 'warning' ? 'notice-warning' : 'notice-info';
        
        const messageHtml = `
            <div class="notice ${messageClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `;
        
        jQuery('.wrap > h1').after(messageHtml);
    },
    
    confirmDialog: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    loading: function(show, element) {
        const $el = element ? jQuery(element) : jQuery('body');
        
        if (show) {
            $el.addClass('glass-loading');
        } else {
            $el.removeClass('glass-loading');
        }
    }
};