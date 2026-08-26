/**
 * LuxuryStay - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    const nav = document.querySelector('.luxury-nav');
    if (nav) {
        const updateNavState = function () {
            nav.classList.toggle('scrolled', window.scrollY > 10);
        };
        updateNavState();
        window.addEventListener('scroll', updateNavState, { passive: true });
    }

    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-target') || '0', 10);
                const suffix = el.getAttribute('data-suffix') || '';
                const duration = 1200;
                const start = performance.now();
                const step = function (now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const value = Math.floor(progress * target);
                    el.textContent = value.toLocaleString() + suffix;
                    if (progress < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
                counterObserver.unobserve(el);
            });
        }, { threshold: 0.6 });
        counters.forEach(function (counter) { counterObserver.observe(counter); });
    }

    // Auto-hide toasts
    document.querySelectorAll('.toast').forEach(function (toast) {
        setTimeout(function () {
            const bsToast = bootstrap.Toast.getOrCreateInstance(toast);
            bsToast.hide();
        }, 5000);
    });

    // Form loading state
    document.querySelectorAll('form[data-loading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            const loader = document.getElementById('pageLoader');
            if (loader) loader.classList.remove('d-none');
        });
    });

    // Add an accessible show/hide control to every password field.
    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        if (input.dataset.passwordToggleReady === 'true') return;

        const group = document.createElement('div');
        group.className = 'input-group password-input-group';
        input.parentNode.insertBefore(group, input);
        group.appendChild(input);

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'btn password-toggle';
        toggle.setAttribute('aria-label', 'Show password');
        toggle.setAttribute('aria-pressed', 'false');
        toggle.title = 'Show password';
        toggle.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
        group.appendChild(toggle);
        input.dataset.passwordToggleReady = 'true';

        toggle.addEventListener('click', function () {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            toggle.title = isHidden ? 'Hide password' : 'Show password';
            toggle.innerHTML = '<i class="bi bi-' + (isHidden ? 'eye-slash' : 'eye') + '" aria-hidden="true"></i>';
        });
    });

    const profileImageInput = document.querySelector('[data-profile-image-input]');
    if (profileImageInput) {
        const profilePreview = document.getElementById('profilePreview');
        const profileInitials = document.getElementById('profileInitials');
        profileImageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                if (!profilePreview) return;
                profilePreview.src = event.target.result;
                profilePreview.classList.remove('d-none');
                if (profileInitials) profileInitials.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    // Keep the homepage budget selection local until the visitor chooses Browse all.
    const priceRange = document.getElementById('priceRange');
    const priceDisplay = document.getElementById('priceDisplay');
    const appBase = typeof APP_BASE !== 'undefined' ? APP_BASE : window.APP_BASE || document.querySelector('meta[name="app-url"]')?.content || '';
    const appUrl = appBase.replace(/\/$/, '');
    if (priceRange && priceDisplay) {
        priceDisplay.textContent = 'Rs. ' + Number(priceRange.value).toLocaleString();
        priceRange.addEventListener('input', function () {
            priceDisplay.textContent = 'Rs. ' + Number(this.value).toLocaleString();
        });
    }

    const browseAllPriceButton = document.querySelector('[data-browse-all-price]');
    if (priceRange && browseAllPriceButton) {
        browseAllPriceButton.addEventListener('click', function (event) {
            event.preventDefault();
            const url = (appUrl || '') + '/properties.php?max_price=' + encodeURIComponent(priceRange.value);
            window.location.href = url;
        });
    }

    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    if (priceMin && priceMax) {
        [priceMin, priceMax].forEach(function (el) {
            el.addEventListener('input', updatePriceLabel);
        });
        function updatePriceLabel() {
            const label = document.getElementById('priceRangeLabel');
            if (label) {
                label.textContent = 'Rs. ' + Number(priceMin.value).toLocaleString() + ' - Rs. ' + Number(priceMax.value).toLocaleString();
            }
        }
    }

    // Filter tags - Navigate to properties with filter
    document.querySelectorAll('.filter-tag[data-filter]').forEach(function (tag) {
        tag.addEventListener('click', function (e) {
            e.preventDefault();
            const filterValue = this.dataset.filter;
            const propertyTypes = ['Hotel', 'Villa', 'Resort', 'Guest House'];
            let url = (appUrl || '') + '/properties.php?';
            if (propertyTypes.includes(filterValue)) {
                url += 'type=' + encodeURIComponent(filterValue);
            } else {
                const amenityMap = {
                    'Breakfast': 'Breakfast',
                    'Pool': 'Pool'
                };
                if (amenityMap[filterValue]) {
                    url += 'amenities=' + encodeURIComponent(amenityMap[filterValue]);
                }
            }
            const loader = document.getElementById('pageLoader');
            if (loader) loader.classList.remove('d-none');
            window.location.href = url;
        });
    });

    document.querySelectorAll('.amenity-chip input[type="checkbox"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const label = this.closest('.amenity-chip');
            if (label) {
                label.classList.toggle('active', this.checked);
            }
        });
    });

    // Search suggestions
    const locationInput = document.getElementById('searchLocation');
    const suggestionsBox = document.getElementById('searchSuggestions');
    if (locationInput && suggestionsBox) {
        let debounceTimer;
        locationInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                suggestionsBox.classList.add('d-none');
                return;
            }
            debounceTimer = setTimeout(function () {
                fetch((appUrl || '') + '/api/search-suggestions.php?q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.length) {
                            suggestionsBox.classList.add('d-none');
                            return;
                        }
                        suggestionsBox.innerHTML = data.map(function (item) {
                            return '<div class="suggestion-item" data-value="' + item.district + '">' +
                                '<i class="bi bi-geo-alt text-primary me-2"></i>' + item.name + ', ' + item.district + '</div>';
                        }).join('');
                        suggestionsBox.classList.remove('d-none');
                        suggestionsBox.querySelectorAll('.suggestion-item').forEach(function (el) {
                            el.addEventListener('click', function () {
                                locationInput.value = this.dataset.value;
                                suggestionsBox.classList.add('d-none');
                            });
                        });
                    });
            }, 300);
        });
        document.addEventListener('click', function (e) {
            if (!locationInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('d-none');
            }
        });
    }
});

// APP_BASE from inline script on pages that need API
if (typeof APP_BASE === 'undefined') {
    var APP_BASE = document.querySelector('meta[name="app-url"]')?.content || '';
}
