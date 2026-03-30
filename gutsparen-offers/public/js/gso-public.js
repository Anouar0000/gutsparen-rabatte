document.addEventListener('DOMContentLoaded', function() {
    const initCopyButtons = function(root) {
        const copyButtons = root.querySelectorAll('[data-gso-copy]');

        copyButtons.forEach(function(button) {
            if (button.dataset.gsoCopyBound === '1') {
                return;
            }

            button.dataset.gsoCopyBound = '1';

            button.addEventListener('click', function() {
                const code = button.getAttribute('data-gso-copy');
                const originalText = button.textContent;

                if (!code) {
                    return;
                }

                navigator.clipboard.writeText(code).then(function() {
                    button.textContent = 'Kopiert';
                    window.setTimeout(function() {
                        button.textContent = originalText;
                    }, 1400);
                }).catch(function() {
                    button.textContent = 'Fehler';
                    window.setTimeout(function() {
                        button.textContent = originalText;
                    }, 1400);
                });
            });
        });
    };

    const initSlider = function(wrap) {
        if (!wrap || wrap.dataset.gsoSliderBound === '1') {
            return;
        }

        const slider = wrap.querySelector('[data-gso-slider]');
        const prev = wrap.querySelector('[data-gso-slider-prev]');
        const next = wrap.querySelector('[data-gso-slider-next]');

        if (!slider || !prev || !next) {
            return;
        }

        wrap.dataset.gsoSliderBound = '1';

        const getStep = function() {
            const firstItem = slider.firstElementChild;
            if (!firstItem) {
                return 280;
            }

            const styles = window.getComputedStyle(slider);
            const gap = parseFloat(styles.columnGap || styles.gap || '0');
            return firstItem.getBoundingClientRect().width + gap;
        };

        const updateButtons = function() {
            const maxScroll = slider.scrollWidth - slider.clientWidth;
            prev.disabled = slider.scrollLeft <= 4;
            next.disabled = slider.scrollLeft >= maxScroll - 4;
        };

        const scrollSlider = function(direction) {
            const step = getStep();
            const target = Math.max(0, slider.scrollLeft + (direction * step));

            slider.scrollTo({ left: target, behavior: 'smooth' });
            window.setTimeout(updateButtons, 220);
        };

        prev.addEventListener('click', function() {
            scrollSlider(-1);
        });

        next.addEventListener('click', function() {
            scrollSlider(1);
        });

        slider.addEventListener('scroll', updateButtons, { passive: true });
        window.addEventListener('resize', updateButtons);
        window.requestAnimationFrame(updateButtons);
        window.setTimeout(updateButtons, 180);
    };

    const initOverview = function(root) {
        root.querySelectorAll('[data-gso-slider-wrap]').forEach(initSlider);
        initCopyButtons(root);
    };

    const updateOverviewUrl = function(formData, formAction) {
        const url = new URL(formAction, window.location.origin);

        formData.forEach(function(value, key) {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });

        window.history.replaceState({}, '', url.toString());
    };

    const forms = document.querySelectorAll('[data-gso-overview-form]');

    forms.forEach(function(form) {
        const section = form.closest('.gso-overview-section');
        const results = section ? section.querySelector('[data-gso-overview-results]') : null;
        const searchInput = form.querySelector('.gso-filter-search');
        const categorySelect = form.querySelector('.gso-filter-select');
        let requestId = 0;
        let debounceTimer = null;

        if (!section || !results) {
            return;
        }

        const runFilter = function() {
            const data = new FormData(form);
            data.append('action', 'gso_filter_offers');
            data.append('nonce', (window.gsoPublic && window.gsoPublic.nonce) || '');

            requestId += 1;
            const currentRequestId = requestId;

            results.classList.add('is-loading');

            fetch((window.gsoPublic && window.gsoPublic.ajaxUrl) || form.action, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
            })
                .then(function(response) {
                    return response.json();
                })
                .then(function(payload) {
                    if (currentRequestId !== requestId || !payload || !payload.success || !payload.data) {
                        return;
                    }

                    results.innerHTML = payload.data.html;
                    results.classList.remove('is-loading');
                    initOverview(results);
                    updateOverviewUrl(new FormData(form), form.action);
                })
                .catch(function() {
                    results.classList.remove('is-loading');
                });
        };

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            runFilter();
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(runFilter, 250);
            });
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', runFilter);
        }
    });

    initOverview(document);
});






