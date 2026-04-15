document.addEventListener('DOMContentLoaded', function() {
    const viewedOffers = new Set();

    const getMatomoTracker = function() {
        return Array.isArray(window._paq) ? window._paq : null;
    };

    const sendOfferEvent = function(payload) {
        if (!payload || !payload.offerId || !payload.event) {
            return;
        }

        const tracker = getMatomoTracker();

        if (!tracker) {
            return;
        }

        const contentName = payload.name || ('Offer #' + payload.offerId);
        const contentPiece = payload.surface || 'offer';
        const contentTarget = payload.target || '';
        const eventCategory = getOfferEventCategory(contentPiece);

        if (payload.event === 'impression') {
            tracker.push(['trackContentImpression', contentName, contentPiece, contentTarget]);
            tracker.push(['trackEvent', eventCategory, 'impression', contentName]);
            return;
        }

        if (payload.event === 'click') {
            tracker.push(['trackContentInteraction', 'click', contentName, contentPiece, contentTarget]);
            tracker.push(['trackEvent', eventCategory, 'cta_click', contentName]);
            return;
        }

        if (payload.event === 'copy') {
            tracker.push(['trackEvent', eventCategory, 'code_copy', contentName]);
        }
    };

    const getOfferEventCategory = function(surface) {
        if (surface === 'gutsparen_banner') {
            return 'GutSparen Offer | banner';
        }

        if (surface === 'gutsparen_overview') {
            return 'GutSparen Offer | overview offers';
        }

        return 'GutSparen Offer';
    };

    const initTracking = function(root) {
        const trackables = root.querySelectorAll('[data-gso-track-impression]');

        if (!trackables.length) {
            return;
        }

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting || entry.intersectionRatio < 0.5) {
                    return;
                }

                const element = entry.target;
                const offerId = element.getAttribute('data-gso-offer-id');
                const surface = element.getAttribute('data-gso-track-surface') || '';
                const key = offerId + ':' + surface;

                if (!offerId || viewedOffers.has(key)) {
                    observer.unobserve(element);
                    return;
                }

                viewedOffers.add(key);
                observer.unobserve(element);

                sendOfferEvent({
                    offerId: offerId,
                    event: 'impression',
                    surface: surface,
                    name: element.getAttribute('data-gso-track-name') || '',
                    target: element.getAttribute('data-gso-track-target') || '',
                });
            });
        }, {
            threshold: 0.5,
        });

        trackables.forEach(function(element) {
            const offerId = element.getAttribute('data-gso-offer-id');
            const surface = element.getAttribute('data-gso-track-surface') || '';
            const key = offerId + ':' + surface;

            if (!offerId || viewedOffers.has(key)) {
                return;
            }

            observer.observe(element);
        });
    };

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
                const offer = button.closest('[data-gso-offer-id]');

                if (!code) {
                    return;
                }

                navigator.clipboard.writeText(code).then(function() {
                    if (offer) {
                        sendOfferEvent({
                            offerId: offer.getAttribute('data-gso-offer-id'),
                            event: 'copy',
                            surface: offer.getAttribute('data-gso-track-surface') || '',
                            name: offer.getAttribute('data-gso-track-name') || '',
                            target: offer.getAttribute('data-gso-track-target') || '',
                        });
                    }

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
        initTracking(root);
    };

    document.addEventListener('click', function(event) {
        const button = event.target.closest('[data-gso-track-click]');

        if (!button) {
            return;
        }

        const offer = button.closest('[data-gso-offer-id]');

        if (!offer) {
            return;
        }

        sendOfferEvent({
            offerId: offer.getAttribute('data-gso-offer-id'),
            event: button.getAttribute('data-gso-track-click') === 'copy' ? 'copy' : 'click',
            surface: offer.getAttribute('data-gso-track-surface') || '',
            name: offer.getAttribute('data-gso-track-name') || '',
            target: button.getAttribute('href') || '',
        });
    });

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






