// Agency Gallery Manager JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Agency Gallery Manager initialized');
    
    // Initialize all components
    initializeNavigation();
    initializeUploadStudio();
    initializeManagementStudio();
    
    let imageFiles = [];
    let dragCounter = 0;
    
    function initializeNavigation() {
        const navItems = document.querySelectorAll('.nav-item');
        const studioViews = document.querySelectorAll('.studio-view');
        
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                const targetView = this.getAttribute('data-view');
                
                // Update active nav
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                // Update active view
                studioViews.forEach(view => view.classList.remove('active'));
                const targetElement = document.getElementById(targetView + '-studio');
                if (targetElement) {
                    targetElement.classList.add('active');
                }
            });
        });
        
        // Handle switch to upload button
        const switchBtn = document.querySelector('.switch-to-upload');
        if (switchBtn) {
            switchBtn.addEventListener('click', function() {
                document.querySelector('[data-view="upload"]').click();
            });
        }
    }

    
    function initializeUploadStudio() {
        const uploadZone = document.getElementById('upload-zone');
        const fileInput = document.getElementById('file-input');
        const browseBtn = document.querySelector('.browse-files-btn');
        const form = document.getElementById('studio-upload-form');

        if (!uploadZone || !fileInput || !browseBtn) {
            console.warn('Upload elements not found');
            return;
        }

        // Initialize bulk settings
        initializeBulkSettings();
        
        // Browse button click
        browseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });
        
        // Upload zone click
        uploadZone.addEventListener('click', function(e) {
            if (e.target === uploadZone || e.target.closest('.zone-content')) {
                fileInput.click();
            }
        });
        
        // File input change
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleFiles(Array.from(e.target.files));
            }
        });
        
        // Drag and drop events
        uploadZone.addEventListener('dragenter', function(e) {
            e.preventDefault();
            dragCounter++;
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dragCounter--;
            if (dragCounter === 0) {
                uploadZone.classList.remove('dragover');
            }
        });
        
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dragCounter = 0;
            uploadZone.classList.remove('dragover');
            
            const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
            
            if (files.length > 0) {
                handleFiles(files);
            }
        });
        
        // Form submission
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (imageFiles.length === 0) {
                    showMessage('Please select at least one image to upload.', 'error');
                    return;
                }
                
                // Validate all images have titles
                const rows = document.querySelectorAll('#image-list .image-row');
                let hasErrors = false;
                
                rows.forEach(row => {
                    const title = row.querySelector('.title-input').value.trim();
                    if (!title) {
                        row.querySelector('.title-input').style.borderColor = '#ef4444';
                        hasErrors = true;
                    } else {
                        row.querySelector('.title-input').style.borderColor = '#e2e8f0';
                    }
                });
                
                if (hasErrors) {
                    showMessage('Please provide titles for all images.', 'error');
                    return;
                }
                
                uploadImages();
            });
        }
        
        // Reset button
        const resetBtn = document.querySelector('.reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to reset? All images and data will be lost.')) {
                    resetUploadForm();
                }
            });
        }
        
        // Bulk actions
        document.addEventListener('click', function(e) {
            if (e.target.closest('.action-btn')) {
                const btn = e.target.closest('.action-btn');
                const action = btn.getAttribute('data-action');
                const checkboxes = document.querySelectorAll('#image-list .select-image');
                
                switch (action) {
                    case 'select-all':
                        checkboxes.forEach(cb => cb.checked = true);
                        break;
                        
                    case 'remove-selected':
                        if (confirm('Remove selected images?')) {
                            const selected = Array.from(checkboxes).filter(cb => cb.checked);
                            selected.forEach(cb => {
                                const row = cb.closest('.image-row');
                                const index = parseInt(row.dataset.index);
                                removeImageRow(row, index);
                            });
                        }
                        break;
                }
            }
        });
    }
    
    function handleFiles(files) {
        files.forEach(file => {
            if (file.type.startsWith('image/') && file.size <= 20 * 1024 * 1024) {
                imageFiles.push(file);
                createImageRow(file, imageFiles.length - 1);
            } else if (file.size > 20 * 1024 * 1024) {
                showMessage(`File "${file.name}" is too large. Maximum size is 10MB.`, 'error');
            }
        });
        
        if (imageFiles.length > 0) {
            showImageEditor();
        }
    }
    
    function createImageRow(file, index) {
        const template = document.getElementById('image-row-template');
        if (!template) {
            console.error('Image row template not found');
            return;
        }
        
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.image-row');
        
        row.dataset.index = index;
        
        // Set up image preview
        const img = clone.querySelector('.row-thumbnail img');
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        // Auto-generate title from filename
        const titleInput = clone.querySelector('.title-input');
        titleInput.value = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ");
        
        // Remove button
        const removeBtn = clone.querySelector('.remove-image');
        removeBtn.addEventListener('click', function() {
            removeImageRow(row, index);
        });
        
        // Categories selector
        const categoriesSelector = clone.querySelector('.categories-selector');
        const categoriesDropdown = clone.querySelector('.categories-dropdown');
        
        if (categoriesSelector && categoriesDropdown) {
            categoriesSelector.addEventListener('click', function() {
                const isVisible = categoriesDropdown.style.display === 'block';
                // Hide all other dropdowns
                document.querySelectorAll('.categories-dropdown').forEach(dd => {
                    dd.style.display = 'none';
                });
                // Toggle this dropdown
                categoriesDropdown.style.display = isVisible ? 'none' : 'block';
            });
        }
        
        // Add to list
        const imageList = document.getElementById('image-list');
        if (imageList) {
            imageList.appendChild(clone);
            
            // Animate in
            setTimeout(() => {
                row.classList.add('fade-in');
            }, 50);
        }
    }
    
    function removeImageRow(row, index) {
        imageFiles.splice(index, 1);
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            row.remove();
            
            // Update indexes
            const rows = document.querySelectorAll('#image-list .image-row');
            rows.forEach((row, newIndex) => {
                row.dataset.index = newIndex;
            });
            
            if (imageFiles.length === 0) {
                hideImageEditor();
            }
        }, 300);
    }
    
    function showImageEditor() {
        const editor = document.getElementById('image-editor');
        const bulkPanel = document.getElementById('bulk-settings-panel');

        if (bulkPanel) {
            bulkPanel.style.display = 'block';
            setTimeout(() => {
                bulkPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        }

        if (editor) {
            editor.style.display = 'block';
        }
    }
    
    function hideImageEditor() {
        const editor = document.getElementById('image-editor');
        const bulkPanel = document.getElementById('bulk-settings-panel');

        if (bulkPanel) {
            bulkPanel.style.display = 'none';
        }

        if (editor) {
            editor.style.display = 'none';
        }
    }

    function initializeBulkSettings() {
        // Toggle bulk settings panel
        const toggleBtn = document.querySelector('.toggle-bulk-settings');
        const bulkContent = document.querySelector('.bulk-settings-content');

        if (toggleBtn && bulkContent) {
            toggleBtn.addEventListener('click', function() {
                const isActive = this.classList.contains('active');

                if (isActive) {
                    bulkContent.style.display = 'none';
                    this.classList.remove('active');
                } else {
                    bulkContent.style.display = 'block';
                    this.classList.add('active');
                }
            });
        }

        // Bulk category selector dropdown
        const bulkCategoriesBtn = document.getElementById('bulk-categories-btn');
        const bulkCategoriesDropdown = document.getElementById('bulk-categories-dropdown');

        if (bulkCategoriesBtn && bulkCategoriesDropdown) {
            bulkCategoriesBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = bulkCategoriesDropdown.style.display === 'block';
                bulkCategoriesDropdown.style.display = isVisible ? 'none' : 'block';
            });

            // Update selector text when categories are selected
            bulkCategoriesDropdown.addEventListener('change', function(e) {
                if (e.target.classList.contains('bulk-category-checkbox')) {
                    updateBulkCategoriesText();
                }
            });
        }

        // Apply bulk settings to all images
        const applyBtn = document.getElementById('apply-bulk-settings');
        if (applyBtn) {
            applyBtn.addEventListener('click', applyBulkSettings);
        }

        // Clear bulk settings
        const clearBtn = document.getElementById('clear-bulk-settings');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearBulkSettings);
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#bulk-categories-btn') && !e.target.closest('#bulk-categories-dropdown')) {
                if (bulkCategoriesDropdown) {
                    bulkCategoriesDropdown.style.display = 'none';
                }
            }
        });
    }

    function updateBulkCategoriesText() {
        const selectedCount = document.querySelectorAll('.bulk-category-checkbox:checked').length;
        const selectorText = document.querySelector('#bulk-categories-btn .selector-text');

        if (selectorText) {
            if (selectedCount === 0) {
                selectorText.textContent = 'Select categories for all';
            } else {
                selectorText.textContent = `${selectedCount} ${selectedCount === 1 ? 'category' : 'categories'} selected`;
            }
        }
    }

    function applyBulkSettings() {
        const bulkTitle = document.getElementById('bulk-title').value.trim();
        const selectedCategories = Array.from(document.querySelectorAll('.bulk-category-checkbox:checked'));

        let appliedCount = 0;

        // Apply to all image rows
        const imageRows = document.querySelectorAll('#image-list .image-row');

        imageRows.forEach(row => {
            // Apply title if provided
            if (bulkTitle) {
                const titleInput = row.querySelector('.title-input');
                if (titleInput) {
                    titleInput.value = bulkTitle;
                    appliedCount++;
                }
            }

            // Apply categories
            if (selectedCategories.length > 0) {
                // First, uncheck all categories in this row
                row.querySelectorAll('input[type="checkbox"][name^="categories"]').forEach(cb => {
                    cb.checked = false;
                });

                // Then check the selected categories
                selectedCategories.forEach(bulkCb => {
                    const value = bulkCb.value;
                    const checkbox = row.querySelector(`input[type="checkbox"][value="${value}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        });

        if (appliedCount > 0 || selectedCategories.length > 0) {
            showMessage(`Batch settings applied to ${imageRows.length} image(s)!`, 'success');
        } else {
            showMessage('Please enter a title or select categories to apply.', 'error');
        }
    }

    function clearBulkSettings() {
        // Clear bulk title
        const bulkTitle = document.getElementById('bulk-title');
        if (bulkTitle) {
            bulkTitle.value = '';
        }

        // Uncheck all bulk categories
        document.querySelectorAll('.bulk-category-checkbox:checked').forEach(cb => {
            cb.checked = false;
        });

        updateBulkCategoriesText();
        showMessage('Batch settings cleared.', 'success');
    }
    
    function uploadImages() {
        const progressSection = document.querySelector('.upload-progress');
        const progressFill = document.querySelector('.progress-fill');
        const progressCount = document.querySelector('.progress-count');
        const uploadBtn = document.querySelector('.upload-btn');
        const uploadBtnSpan = uploadBtn.querySelector('span');
        
        progressSection.style.display = 'block';
        uploadBtn.disabled = true;
        uploadBtnSpan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        
        const rows = Array.from(document.querySelectorAll('#image-list .image-row'));
        let completed = 0;
        let successful = 0;
        let errors = [];
        
        function uploadNext(index) {
            if (index >= rows.length) {
                // All uploads complete
                progressSection.style.display = 'none';
                uploadBtn.disabled = false;
                uploadBtnSpan.innerHTML = '<i class="fas fa-rocket"></i> Upload Images';
                
                if (successful === rows.length) {
                    showMessage(`Successfully uploaded ${successful} images!`, 'success');
                    resetUploadForm();
                    // Switch to manage view and refresh
                    setTimeout(() => {
                        document.querySelector('[data-view="manage"]').click();
                        location.reload();
                    }, 2000);
                } else {
                    showMessage(`Uploaded ${successful} out of ${rows.length} images. ${errors.length} failed.`, 'error');
                }
                return;
            }
            
            const row = rows[index];
            const fileIndex = parseInt(row.dataset.index);
            const file = imageFiles[fileIndex];
            
            if (!file) {
                uploadNext(index + 1);
                return;
            }
            
            const title = row.querySelector('.title-input').value.trim();
            const description = row.querySelector('.description-input').value.trim();
            
            // Collect categories
            const categories = {};
            const categoryInputs = row.querySelectorAll('input[name^="categories["]:checked');
            
            categoryInputs.forEach(input => {
                const matches = input.name.match(/categories\[([^\]]+)\]\[\]/);
                if (matches) {
                    const taxonomy = matches[1];
                    if (!categories[taxonomy]) {
                        categories[taxonomy] = [];
                    }
                    categories[taxonomy].push(input.value);
                }
            });
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'upload_gallery_image');
            formData.append('nonce', glass_gallery_ajax.nonce);
            formData.append('gallery_image', file);
            formData.append('image_title', title);
            formData.append('image_caption', description);
            formData.append('categories', JSON.stringify(categories));
            formData.append('image_order', index);
            
            // Upload
            fetch(glass_gallery_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                completed++;
                
                if (data.success) {
                    successful++;
                    row.style.background = '#f0fdf4';
                    row.style.borderColor = '#bbf7d0';
                } else {
                    errors.push(`${title}: ${data.data || 'Unknown error'}`);
                    row.style.background = '#fef2f2';
                    row.style.borderColor = '#fecaca';
                }
                
                const progress = (completed / rows.length) * 100;
                progressFill.style.width = progress + '%';
                progressCount.textContent = `${completed} / ${rows.length}`;
                
                uploadNext(index + 1);
            })
            .catch(error => {
                completed++;
                errors.push(`${title}: Network error`);
                row.style.background = '#fef2f2';
                row.style.borderColor = '#fecaca';
                
                const progress = (completed / rows.length) * 100;
                progressFill.style.width = progress + '%';
                progressCount.textContent = `${completed} / ${rows.length}`;
                
                uploadNext(index + 1);
            });
        }
        
        uploadNext(0);
    }
    
    function initializeManagementStudio() {
        const tableBody = document.getElementById('table-body');
        
        if (!tableBody) {
            console.warn('Table body not found');
            return;
        }
        
        // Auto-save functionality with debounce
        let saveTimeout;
        
        function autoSave(element, postId, field, value) {
            clearTimeout(saveTimeout);
            const row = element.closest('.table-row');
            const saveIcon = row.querySelector('.save-icon');
            
            saveIcon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            saveIcon.style.color = '#f59e0b';
            
            saveTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'update_gallery_image');
                formData.append('nonce', glass_gallery_ajax.nonce);
                formData.append('post_id', postId);
                formData.append('field', field);
                formData.append('value', value);
                
                fetch(glass_gallery_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        saveIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                        saveIcon.style.color = '#10b981';
                    } else {
                        saveIcon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                        saveIcon.style.color = '#ef4444';
                    }
                })
                .catch(error => {
                    saveIcon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                    saveIcon.style.color = '#ef4444';
                });
            }, 1000);
        }
        
        // Handle input changes
        tableBody.addEventListener('input', function(e) {
            const target = e.target;
            const row = target.closest('.table-row');
            if (!row) return;
            
            const postId = row.dataset.postId;
            
            if (target.classList.contains('title-edit')) {
                autoSave(target, postId, 'title', target.value);
            } else if (target.classList.contains('description-edit')) {
                autoSave(target, postId, 'description', target.value);
            }
        });
        
        // Categories button handling
        tableBody.addEventListener('click', function(e) {
            if (e.target.closest('.categories-btn')) {
                const btn = e.target.closest('.categories-btn');
                const popup = btn.parentElement.querySelector('.categories-popup');
                
                // Hide all other popups
                document.querySelectorAll('.categories-popup').forEach(p => {
                    if (p !== popup) p.style.display = 'none';
                });
                
                // Toggle this popup
                const isVisible = popup.style.display === 'block';
                popup.style.display = isVisible ? 'none' : 'block';
            }
            
            if (e.target.closest('.close-popup')) {
                e.target.closest('.categories-popup').style.display = 'none';
            }
        });
        
        // Handle category checkbox changes
        tableBody.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox' && e.target.name && e.target.name.startsWith('categories[')) {
                const row = e.target.closest('.table-row');
                const postId = row.dataset.postId;
                const taxonomy = e.target.name.match(/categories\[([^\]]+)\]/)[1];
                
                const checkedCategories = Array.from(
                    row.querySelectorAll(`input[name="categories[${taxonomy}][]"]:checked`)
                ).map(cb => cb.value);
                
                autoSave(e.target, postId, 'categories', JSON.stringify({[taxonomy]: checkedCategories}));
                
                // Update preview text
                const allChecked = row.querySelectorAll('input[name^="categories["]:checked').length;
                const preview = row.querySelector('.categories-preview');
                if (preview) {
                    preview.textContent = `${allChecked} selected`;
                }
            }
        });
        
        // Delete image handling
        tableBody.addEventListener('click', function(e) {
            if (e.target.closest('.delete-icon')) {
                const btn = e.target.closest('.delete-icon');
                const postId = btn.dataset.postId;
                const row = btn.closest('.table-row');
                const title = row.querySelector('.title-edit').value;
                
                if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
                    deleteImage(postId, row);
                }
            }
        });
        
        // Refresh button
        const refreshBtn = document.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                location.reload();
            });
        }
        
        // Initialize sortable if available
        if (typeof Sortable !== 'undefined') {
            new Sortable(tableBody, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function(evt) {
                    updateImageOrder();
                }
            });
        }
    }
    
    function deleteImage(postId, row) {
        const formData = new FormData();
        formData.append('action', 'delete_gallery_image');
        formData.append('nonce', glass_gallery_ajax.nonce);
        formData.append('post_id', postId);
        
        fetch(glass_gallery_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.remove();
                    updateImageCount();
                }, 300);
                showMessage('Image deleted successfully.', 'success');
            } else {
                showMessage('Failed to delete image: ' + (data.data || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showMessage('Network error while deleting image.', 'error');
        });
    }
    
    function updateImageOrder() {
        const rows = document.querySelectorAll('#table-body .table-row');
        const orderData = [];
        
        rows.forEach((row, index) => {
            const postId = row.dataset.postId;
            orderData.push({ postId: postId, order: index });
            
            // Update row number
            const rowNumber = row.querySelector('.row-number');
            if (rowNumber) {
                rowNumber.textContent = index + 1;
            }
        });
        
        // Save new order
        const formData = new FormData();
        formData.append('action', 'update_gallery_order');
        formData.append('nonce', glass_gallery_ajax.nonce);
        formData.append('order_data', JSON.stringify(orderData));
        
        fetch(glass_gallery_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Image order updated.', 'success');
            }
        })
        .catch(error => {
            console.error('Order update error:', error);
        });
    }
    
    function updateImageCount() {
        const count = document.querySelectorAll('#table-body .table-row').length;
        const badge = document.querySelector('.count-badge');
        const statNumber = document.querySelector('.stat-number');
        
        if (badge) badge.textContent = count;
        if (statNumber) statNumber.textContent = count;
    }
    
    function resetUploadForm() {
        imageFiles = [];
        const imageList = document.getElementById('image-list');
        if (imageList) imageList.innerHTML = '';
        const fileInput = document.getElementById('file-input');
        if (fileInput) fileInput.value = '';
        hideImageEditor();
        clearMessages();
        clearBulkSettings();
    }
    
    function showMessage(message, type) {
        clearMessages();
        const container = document.querySelector('.message-display');
        if (container) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const icon = type === 'success' ? 'fas fa-check-circle' : 
                        type === 'error' ? 'fas fa-exclamation-circle' : 
                        'fas fa-info-circle';
            
            messageDiv.innerHTML = `<i class="${icon}"></i> ${message}`;
            container.appendChild(messageDiv);
            
            // Auto-remove success messages
            if (type === 'success') {
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.style.opacity = '0';
                        setTimeout(() => {
                            if (messageDiv.parentNode) {
                                messageDiv.parentNode.removeChild(messageDiv);
                            }
                        }, 300);
                    }
                }, 5000);
            }
        }
    }
    
    function clearMessages() {
        const container = document.querySelector('.message-display');
        if (container) {
            container.innerHTML = '';
        }
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.categories-btn') && !e.target.closest('.categories-popup')) {
            document.querySelectorAll('.categories-popup').forEach(popup => {
                popup.style.display = 'none';
            });
        }
        
        if (!e.target.closest('.categories-selector') && !e.target.closest('.categories-dropdown')) {
            document.querySelectorAll('.categories-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    });
    
    // Load Sortable.js if not already loaded
    if (typeof Sortable === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
        script.onload = function() {
            // Re-initialize sortable after loading
            const tableBody = document.getElementById('table-body');
            if (tableBody) {
                new Sortable(tableBody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: function(evt) {
                        updateImageOrder();
                    }
                });
            }
        };
        document.head.appendChild(script);
    }
});