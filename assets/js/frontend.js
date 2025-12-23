/**
 * Glass Gallery Pro - Frontend JavaScript
 * Version: 2.0.0 - Complete Rebuild
 * Simple, clean, reliable
 */
(function() {
    'use strict';

    // State
    let allItems = [];
    let filteredItems = [];
    let currentIndex = 0;

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        collectItems();
        setupFilters();
        setupModal();
        updateResults();
    }

    // ==================== COLLECT ITEMS ====================
    function collectItems() {
        const items = document.querySelectorAll('.ggp-item');
        allItems = Array.from(items).map(el => ({
            element: el,
            id: el.dataset.id,
            title: el.dataset.title,
            full: el.dataset.full,
            cats: JSON.parse(el.dataset.cats || '[]'),
            searchText: (el.dataset.title || '').toLowerCase()
        }));
        filteredItems = [...allItems];
    }

    // ==================== FILTERING ====================
    function setupFilters() {
        const searchInput = document.getElementById('ggpSearch');
        const clearBtn = document.getElementById('ggpClearAll');
        const checkboxes = document.querySelectorAll('.ggp-cat-item input[type="checkbox"]');

        if (searchInput) {
            searchInput.addEventListener('input', debounce(applyFilters, 300));
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', clearAllFilters);
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', applyFilters);
        });
    }

    function applyFilters() {
        const searchTerm = (document.getElementById('ggpSearch')?.value || '').toLowerCase().trim();
        const selectedCats = Array.from(document.querySelectorAll('.ggp-cat-item input:checked'))
            .map(cb => parseInt(cb.value));

        filteredItems = allItems.filter(item => {
            // Search filter
            const matchesSearch = !searchTerm || item.searchText.includes(searchTerm);

            // Category filter
            const matchesCategory = selectedCats.length === 0 ||
                selectedCats.some(catId => item.cats.includes(catId));

            return matchesSearch && matchesCategory;
        });

        updateDisplay();
        updateResults();
    }

    function updateDisplay() {
        const noResults = document.getElementById('ggpNoResults');

        allItems.forEach(item => {
            if (filteredItems.includes(item)) {
                item.element.classList.remove('ggp-hidden');
            } else {
                item.element.classList.add('ggp-hidden');
            }
        });

        if (noResults) {
            noResults.style.display = filteredItems.length === 0 ? 'block' : 'none';
        }
    }

    function updateResults() {
        const countEl = document.getElementById('ggpResultCount');
        if (countEl) {
            countEl.textContent = filteredItems.length;
        }
    }

    function clearAllFilters() {
        const searchInput = document.getElementById('ggpSearch');
        const checkboxes = document.querySelectorAll('.ggp-cat-item input[type="checkbox"]');

        if (searchInput) searchInput.value = '';
        checkboxes.forEach(cb => cb.checked = false);

        applyFilters();
    }

    // ==================== MODAL ====================
    function setupModal() {
        const modal = document.getElementById('ggpModal');
        if (!modal) return;

        const closeBtn = document.getElementById('ggpModalClose');
        const prevBtn = document.getElementById('ggpModalPrev');
        const nextBtn = document.getElementById('ggpModalNext');

        // Click on items
        document.querySelectorAll('.ggp-item').forEach(el => {
            el.addEventListener('click', function() {
                const item = allItems.find(i => i.element === this);
                if (item) {
                    const index = filteredItems.indexOf(item);
                    if (index !== -1) openModal(index);
                }
            });
        });

        // Modal controls
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (prevBtn) prevBtn.addEventListener('click', () => navigate(-1));
        if (nextBtn) nextBtn.addEventListener('click', () => navigate(1));

        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!modal || !modal.classList.contains('ggp-active')) return;

            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
        });
    }

    function openModal(index) {
        const modal = document.getElementById('ggpModal');
        if (!modal) return;

        currentIndex = index;
        modal.classList.add('ggp-active');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            updateModalContent();
        });
    }

    function closeModal() {
        const modal = document.getElementById('ggpModal');
        if (modal) {
            modal.classList.remove('ggp-active');
            document.body.style.overflow = '';
        }
    }

    function navigate(direction) {
        currentIndex += direction;
        if (currentIndex < 0) currentIndex = filteredItems.length - 1;
        if (currentIndex >= filteredItems.length) currentIndex = 0;
        updateModalContent();
    }

    function updateModalContent() {
        const item = filteredItems[currentIndex];
        if (!item) return;

        const img = document.getElementById('ggpModalImg');
        const title = document.getElementById('ggpModalTitle');
        const counter = document.getElementById('ggpModalCounter');
        const categoriesDiv = document.getElementById('ggpModalCategories');
        const prevBtn = document.getElementById('ggpModalPrev');
        const nextBtn = document.getElementById('ggpModalNext');

        // Update image
        if (img && item.full) {
            img.src = item.full;
            img.alt = item.title || '';
        }

        // Update categories (eyebrow above title)
        if (categoriesDiv) {
            categoriesDiv.innerHTML = '';
            if (item.cats && Array.isArray(item.cats) && item.cats.length > 0) {
                item.cats.forEach(catId => {
                    const catCheckbox = document.querySelector(`.ggp-cat-item input[value="${catId}"]`);
                    if (catCheckbox) {
                        const catName = catCheckbox.dataset.catName ||
                                      (catCheckbox.nextElementSibling ? catCheckbox.nextElementSibling.textContent : '');
                        if (catName) {
                            const badge = document.createElement('span');
                            badge.className = 'ggp-modal-cat-badge';
                            badge.textContent = catName;
                            categoriesDiv.appendChild(badge);
                        }
                    }
                });
            }
        }

        // Update title and counter
        if (title) title.textContent = item.title || '';
        if (counter) counter.textContent = `Image ${currentIndex + 1} of ${filteredItems.length}`;

        // Show/hide nav buttons
        if (prevBtn) prevBtn.style.display = filteredItems.length > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = filteredItems.length > 1 ? 'flex' : 'none';
    }

    // ==================== UTILITIES ====================
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

})();
