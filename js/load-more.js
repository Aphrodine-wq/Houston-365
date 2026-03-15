/**
 * Front-end "Load More" for Recent News
 * Reveals batches of hidden cards without additional AJAX requests.
 */
(function() {
    'use strict';

    const BATCH_SIZE = 6;
    const grid = document.querySelector('.recent-posts-grid');
    const loadMoreBtn = document.querySelector('.load-more-btn');

    if (!grid || !loadMoreBtn) return;

    const items = Array.from(grid.querySelectorAll('.recent-news-item'));

    function revealNextBatch() {
        const hiddenItems = items.filter(item => item.classList.contains('recent-news-item--hidden'));
        if (!hiddenItems.length) {
            disableButton();
            return;
        }

        hiddenItems.slice(0, BATCH_SIZE).forEach(item => {
            item.classList.remove('recent-news-item--hidden');
        });

        if (items.every(item => !item.classList.contains('recent-news-item--hidden'))) {
            disableButton();
        }
    }

    function disableButton() {
        loadMoreBtn.classList.add('is-disabled');
        loadMoreBtn.disabled = true;
        loadMoreBtn.style.display = 'none';
    }

    loadMoreBtn.addEventListener('click', revealNextBatch);

    // Hide button if everything is already visible (edge case)
    if (items.every(item => !item.classList.contains('recent-news-item--hidden'))) {
        disableButton();
    }
})();
