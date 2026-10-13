// Main JavaScript for Orion Advent Travel Portal
document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. Mobile Navigation Drawer Toggle
    // ==========================================
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const hamburgerIcon = document.getElementById('hamburgerIcon');
    const closeIcon = document.getElementById('closeIcon');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isExpanded = !mobileMenu.classList.contains('hidden');
            if (isExpanded) {
                mobileMenu.classList.add('hidden');
                if (hamburgerIcon) hamburgerIcon.classList.remove('hidden');
                if (closeIcon) closeIcon.classList.add('hidden');
            } else {
                mobileMenu.classList.remove('hidden');
                if (hamburgerIcon) hamburgerIcon.classList.add('hidden');
                if (closeIcon) closeIcon.classList.remove('hidden');
            }
        });
    }

    // ==========================================
    // 2. Ambient Hero Background Image Slider
    // (banner-05.webp & banner-07.webp - No Nav, No Dots, Smooth Auto-Crossfade)
    // ==========================================
    const bgSlides = document.querySelectorAll('.hero-bg-slide');
    if (bgSlides.length > 1) {
        let currentSlide = 0;
        setInterval(() => {
            bgSlides[currentSlide].classList.remove('opacity-100');
            bgSlides[currentSlide].classList.add('opacity-0');

            currentSlide = (currentSlide + 1) % bgSlides.length;

            bgSlides[currentSlide].classList.remove('opacity-0');
            bgSlides[currentSlide].classList.add('opacity-100');
        }, 5500);
    }

    // ==========================================
    // 3. Dynamic Animated Heading Word Switcher
    // Cycles through vibrant holiday experiences
    // ==========================================
    const dynamicHeroWord = document.getElementById('dynamicHeroWord');
    if (dynamicHeroWord) {
        const rotatingWords = [
            'Holiday Packages',
            'Honeymoon Specials',
            'Family Vacations',
            'Adventure Tours',
            'Luxury Getaways',
            'Weekend Escapes',
            'International Deals'
        ];
        let wordIndex = 0;
        setInterval(() => {
            wordIndex = (wordIndex + 1) % rotatingWords.length;
            // Smooth exit animation
            dynamicHeroWord.style.opacity = '0';
            dynamicHeroWord.style.transform = 'translateY(6px)';
            setTimeout(() => {
                dynamicHeroWord.textContent = rotatingWords[wordIndex];
                // Smooth enter animation
                dynamicHeroWord.style.opacity = '1';
                dynamicHeroWord.style.transform = 'translateY(0)';
            }, 300);
        }, 2800);
    }

    // ==========================================
    // 4. Hero Search Tab Switcher
    // ==========================================
    const heroTabs = document.querySelectorAll('#heroTabs .tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');
    const topSearchBtnText = document.getElementById('topSearchBtnText');
    const mobileHeroSearchBtnText = document.getElementById('mobileHeroSearchBtnText');

    const searchBtnLabels = {
        'panel-domestic': 'Search Domestic Packages',
        'panel-international': 'Search International Tours',
        'panel-flights': 'Search Cheap Flights',
        'panel-hotels': 'Search Best Hotels'
    };

    if (heroTabs.length > 0) {
        heroTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetId = tab.getAttribute('data-target');

                // Reset all tabs to inactive state:
                // Inactive tab text is dark/slate, but its ICON is the main brand teal color (text-brand-600)
                heroTabs.forEach(t => {
                    t.classList.remove('bg-brand-600', 'text-white', 'font-bold');
                    t.classList.add('text-slate-700', 'font-semibold', 'hover:text-brand-700', 'hover:bg-slate-100');
                    const icon = t.querySelector('i, svg');
                    if (icon) {
                        icon.classList.remove('text-white');
                        icon.classList.add('text-brand-600');
                    }
                });

                // Set clicked tab to active state:
                // Active tab has bg-brand-600 with white text and white icon
                tab.classList.remove('text-slate-700', 'font-semibold', 'hover:text-brand-700', 'hover:bg-slate-100');
                tab.classList.add('bg-brand-600', 'text-white', 'font-bold');
                const icon = tab.querySelector('i, svg');
                if (icon) {
                    icon.classList.remove('text-brand-600');
                    icon.classList.add('text-white');
                }

                // Switch corresponding form panel
                tabPanels.forEach(panel => {
                    if (panel.id === targetId) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });

                // Update both Desktop & Mobile Search Button labels
                if (topSearchBtnText && searchBtnLabels[targetId]) {
                    topSearchBtnText.textContent = searchBtnLabels[targetId];
                }
                if (mobileHeroSearchBtnText && searchBtnLabels[targetId]) {
                    mobileHeroSearchBtnText.textContent = searchBtnLabels[targetId];
                }

                // Close any open dropdowns when switching tabs
                closeAllDropdowns();
            });
        });
    }

    // ==========================================
    // 5. Interactive Custom Dropdowns Engine
    // ==========================================
    const dropdownWrappers = document.querySelectorAll('.dropdown-wrapper');

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }

    // Toggle dropdown on trigger click
    dropdownWrappers.forEach(wrapper => {
        const trigger = wrapper.querySelector('.dropdown-trigger');
        const menu = wrapper.querySelector('.dropdown-menu');

        if (trigger && menu) {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isCurrentlyOpen = !menu.classList.contains('hidden');
                closeAllDropdowns();
                if (!isCurrentlyOpen) {
                    menu.classList.remove('hidden');
                    const searchInput = menu.querySelector('.dropdown-search-input');
                    if (searchInput) {
                        setTimeout(() => searchInput.focus(), 50);
                    }
                }
            });

            // Prevent clicks inside the dropdown menu from closing it prematurely
            menu.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // Selection items inside dropdown
        const selectItems = wrapper.querySelectorAll('.dropdown-select-item');
        selectItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const val = item.getAttribute('data-val');
                const sub = item.getAttribute('data-sub');

                const valEl = wrapper.querySelector('.field-val');
                const subEl = wrapper.querySelector('.field-sub');
                const inputEl = wrapper.querySelector('input.field-input');
                const typeaheadEl = wrapper.querySelector('input.typeahead-input');
                const destSubtext = wrapper.querySelector('.dest-subtext');

                if (valEl && val) {
                    if ('value' in valEl) {
                        valEl.value = val;
                    } else {
                        valEl.textContent = val;
                    }
                }
                if (subEl && sub) subEl.innerHTML = sub;
                if (inputEl && val) inputEl.value = val;
                if (typeaheadEl && val) typeaheadEl.value = val;
                if (destSubtext && sub) destSubtext.innerHTML = sub;

                // Close dropdown
                closeAllDropdowns();
            });
        });
    });

    // Helper for safe HTML rendering in autocomplete
    function escapeHtmlStr(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ==========================================
    // Live API Autocomplete Engine (Hero & Global)
    // Powered by /api/places.php (OSM / Google Places / DB Availability)
    // ==========================================
    const typeaheadInputs = document.querySelectorAll('.typeahead-input');
    typeaheadInputs.forEach(input => {
        const wrapper = input.closest('.dropdown-wrapper');
        if (!wrapper) return;
        const menu = wrapper.querySelector('.dropdown-menu');
        const listContainer = wrapper.querySelector('.suggestion-list');
        const statusIndicator = wrapper.querySelector('.dropdown-status-indicator');

        const category = input.getAttribute('data-category') || '';
        const searchType = input.getAttribute('data-type') || 'package';
        let debounceTimer = null;
        let activeIndex = -1;

        // Function to bind click to rendered suggestion items
        function attachItemClickHandlers() {
            if (!listContainer) return;
            const items = listContainer.querySelectorAll('.dropdown-select-item');
            items.forEach((item, idx) => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const val = item.getAttribute('data-val');
                    const sub = item.getAttribute('data-sub');

                    if (val) input.value = val;
                    const destSubtext = wrapper.querySelector('.dest-subtext');
                    if (destSubtext && sub) destSubtext.innerHTML = escapeHtmlStr(sub);

                    closeAllDropdowns();
                });
            });
        }

        // Attach to initial pre-rendered items
        attachItemClickHandlers();

        // Fetch suggestions from live API endpoint
        function fetchLiveSuggestions(query) {
            if (!listContainer) return;

            const q = query.trim();
            const url = `api/places.php?q=${encodeURIComponent(q)}&category=${encodeURIComponent(category)}&type=${encodeURIComponent(searchType)}`;

            if (statusIndicator) {
                statusIndicator.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i> Fetching';
            }

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (statusIndicator) {
                        statusIndicator.innerHTML = '<i class="fa-solid fa-bolt text-[10px]"></i> Live API';
                    }

                    if (!res.success || !res.data || res.data.length === 0) {
                        listContainer.innerHTML = `
                            <div class="p-3 text-center text-xs text-slate-400">
                                <i class="fa-solid fa-location-dot text-slate-300 mr-1.5"></i>
                                <span>No destinations found for "${escapeHtmlStr(q)}"</span>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    res.data.forEach(item => {
                        let iconBg = 'bg-slate-100';
                        let iconColor = 'text-slate-500';
                        let iconClass = 'fa-solid fa-location-dot';
                        let badgeClass = 'bg-slate-100 text-slate-600';

                        if (item.has_packages) {
                            iconBg = 'bg-teal-50';
                            iconColor = 'text-teal-600';
                            iconClass = 'fa-solid fa-map-location-dot';
                            badgeClass = 'bg-teal-50 text-teal-700 font-bold';
                        } else if (item.has_hotels) {
                            iconBg = 'bg-amber-50';
                            iconColor = 'text-amber-600';
                            iconClass = 'fa-solid fa-hotel';
                            badgeClass = 'bg-amber-50 text-amber-700 font-bold';
                        } else if (category === 'international') {
                            iconBg = 'bg-sky-50';
                            iconColor = 'text-sky-600';
                            iconClass = 'fa-solid fa-globe';
                            badgeClass = 'bg-sky-50 text-sky-700 font-bold';
                        }

                        html += `
                            <div class="dropdown-select-item flex items-center justify-between p-2 hover:bg-slate-50 cursor-pointer transition rounded-xl"
                                 data-val="${escapeHtmlStr(item.name)}" 
                                 data-sub="${escapeHtmlStr(item.full_address)}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg ${iconBg} ${iconColor} flex items-center justify-center shrink-0">
                                        <i class="${iconClass} text-xs"></i>
                                    </div>
                                    <div class="truncate">
                                        <span class="block text-xs font-bold text-slate-800">${escapeHtmlStr(item.name)}</span>
                                        <span class="block text-[10px] text-slate-400 truncate">${escapeHtmlStr(item.full_address)}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] ${badgeClass} px-2 py-0.5 shrink-0 rounded">
                                    ${escapeHtmlStr(item.badge || 'Destination')}
                                </span>
                            </div>
                        `;
                    });

                    listContainer.innerHTML = html;
                    attachItemClickHandlers();
                })
                .catch(err => {
                    if (statusIndicator) {
                        statusIndicator.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-[10px] text-rose-500"></i> Error';
                    }
                });
        }

        // Event listener for user input typing
        input.addEventListener('input', () => {
            const query = input.value.trim();
            closeAllDropdowns();
            if (menu) menu.classList.remove('hidden');

            clearTimeout(debounceTimer);
            if (query.length === 0) {
                // If emptied, fetch popular defaults
                fetchLiveSuggestions('');
                return;
            }

            if (listContainer) {
                listContainer.innerHTML = `
                    <div class="p-3 text-center text-xs text-slate-400">
                        <i class="fa-solid fa-circle-notch fa-spin text-teal-600 mr-2"></i>
                        <span>Searching destinations via API...</span>
                    </div>
                `;
            }

            debounceTimer = setTimeout(() => {
                fetchLiveSuggestions(query);
            }, 220);
        });

        // Event listener for focus / click
        const handleFocus = (e) => {
            e.stopPropagation();
            closeAllDropdowns();
            if (menu) menu.classList.remove('hidden');

            // If empty or only 1 character and no items rendered, fetch defaults
            if (input.value.trim().length === 0 && (!listContainer || listContainer.children.length === 0)) {
                fetchLiveSuggestions('');
            }
        };

        input.addEventListener('focus', handleFocus);
        input.addEventListener('click', handleFocus);

        // Keyboard navigation (Arrow keys + Enter)
        input.addEventListener('keydown', (e) => {
            if (!menu || menu.classList.contains('hidden') || !listContainer) return;
            const items = listContainer.querySelectorAll('.dropdown-select-item');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && activeIndex < items.length) {
                    e.preventDefault();
                    items[activeIndex].click();
                }
            } else if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });

        function updateActiveItem(items) {
            items.forEach((item, idx) => {
                if (idx === activeIndex) {
                    item.classList.add('bg-slate-100', 'ring-1', 'ring-teal-500');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('bg-slate-100', 'ring-1', 'ring-teal-500');
                }
            });
        }
    });

    // Close all dropdowns when clicking outside
    document.addEventListener('click', () => {
        closeAllDropdowns();
    });

    // Close all dropdowns when pressing ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });

    // ==========================================
    // 6. Calendar Date Picker & Quick Date Chips
    // ==========================================
    document.querySelectorAll('.date-picker-input').forEach(input => {
        input.addEventListener('change', () => {
            const rawDate = input.value;
            if (rawDate) {
                const parts = rawDate.split('-');
                if (parts.length === 3) {
                    const d = new Date(parts[0], parts[1] - 1, parts[2]);
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                    const formattedDate = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                    const dayName = days[d.getDay()];

                    const wrapper = input.closest('.dropdown-wrapper');
                    if (wrapper) {
                        const valEl = wrapper.querySelector('.field-val');
                        const subEl = wrapper.querySelector('.field-sub');
                        const hiddenInput = wrapper.querySelector('input.field-input');

                        if (valEl) {
                            if ('value' in valEl) valEl.value = formattedDate;
                            else valEl.textContent = formattedDate;
                        }
                        if (subEl) subEl.textContent = `${dayName} • Departure`;
                        if (hiddenInput) hiddenInput.value = rawDate;
                    }
                    closeAllDropdowns();
                }
            }
        });
    });

    document.querySelectorAll('.quick-date-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const dateVal = btn.getAttribute('data-date');
            const subVal = btn.getAttribute('data-sub') || 'Departure Date';
            const wrapper = btn.closest('.dropdown-wrapper');
            if (wrapper) {
                const valEl = wrapper.querySelector('.field-val');
                const subEl = wrapper.querySelector('.field-sub');
                const hiddenInput = wrapper.querySelector('input.field-input');

                if (valEl) {
                    if ('value' in valEl) valEl.value = dateVal;
                    else valEl.textContent = dateVal;
                }
                if (subEl) subEl.textContent = subVal;
                if (hiddenInput) hiddenInput.value = dateVal;
            }
            closeAllDropdowns();
        });
    });

    // ==========================================
    // 7. Travelers & Duration Counters Logic
    // ==========================================
    document.querySelectorAll('.counter-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const action = btn.getAttribute('data-action');
            const targetId = btn.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            const min = parseInt(btn.getAttribute('data-min') || '0', 10);
            const max = parseInt(btn.getAttribute('data-max') || '10', 10);

            if (targetEl) {
                let currentVal = parseInt(targetEl.textContent || '0', 10);
                if (action === 'plus' && currentVal < max) {
                    currentVal++;
                } else if (action === 'minus' && currentVal > min) {
                    currentVal--;
                }
                targetEl.textContent = currentVal;

                // Update summary text in parent wrapper
                const wrapper = btn.closest('.dropdown-wrapper');
                if (wrapper) {
                    updateTravelersSummary(wrapper);
                }
            }
        });
    });

    // Duration chip selection
    document.querySelectorAll('.duration-chip').forEach(chip => {
        chip.addEventListener('click', (e) => {
            e.stopPropagation();
            const parent = chip.parentElement;
            parent.querySelectorAll('.duration-chip').forEach(c => {
                c.classList.remove('bg-brand-600', 'text-white', 'active');
                c.classList.add('bg-slate-100', 'text-slate-700');
            });
            chip.classList.remove('bg-slate-100', 'text-slate-700');
            chip.classList.add('bg-brand-600', 'text-white', 'active');

            const wrapper = chip.closest('.dropdown-wrapper');
            if (wrapper) {
                updateTravelersSummary(wrapper);
            }
        });
    });

    // Apply button inside dropdowns
    document.querySelectorAll('.dropdown-apply-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const wrapper = btn.closest('.dropdown-wrapper');
            if (wrapper) {
                updateTravelersSummary(wrapper);
            }
            closeAllDropdowns();
        });
    });

    function updateTravelersSummary(wrapper) {
        const adultsEl = wrapper.querySelector('[id$="AdultsCount"]');
        const kidsEl = wrapper.querySelector('[id$="KidsCount"]');
        const roomsEl = wrapper.querySelector('[id$="RoomsCount"]');
        const activeDuration = wrapper.querySelector('.duration-chip.active');

        const adults = adultsEl ? parseInt(adultsEl.textContent, 10) : 2;
        const kids = kidsEl ? parseInt(kidsEl.textContent, 10) : 0;
        const rooms = roomsEl ? parseInt(roomsEl.textContent, 10) : 1;
        const duration = activeDuration ? activeDuration.getAttribute('data-duration') : '5N / 6D';

        const valEl = wrapper.querySelector('.field-val');
        const subEl = wrapper.querySelector('.field-sub');
        const inputEl = wrapper.querySelector('input.field-input');

        let guestsText = `${adults} Adult${adults > 1 ? 's' : ''}`;
        if (kids > 0) {
            guestsText += `, ${kids} Child${kids > 1 ? 'ren' : ''}`;
        }

        if (valEl) {
            if ('value' in valEl) {
                valEl.value = `${guestsText} • ${duration}`;
            } else {
                valEl.textContent = `${guestsText} • ${duration}`;
            }
        }
        if (subEl) {
            subEl.textContent = `${rooms} Room${rooms > 1 ? 's' : ''} • Standard`;
        }
        if (inputEl) {
            inputEl.value = `${guestsText}, ${rooms} Room, ${duration}`;
        }
    }

    // ==========================================
    // 7.1 Flights & Hotels Specific Interactivity
    // ==========================================
    
    // ==========================================
    // Dedicated Flight Airport Instant Search Engine
    // Filters by Official Airport Name, City, or IATA Code
    // ==========================================
    const flightAirportInputs = document.querySelectorAll('.flight-airport-typeahead');
    flightAirportInputs.forEach(input => {
        const wrapper = input.closest('.dropdown-wrapper');
        if (!wrapper) return;
        const menu = wrapper.querySelector('.dropdown-menu');
        const items = wrapper.querySelectorAll('.flight-select-item');

        const filterAirports = () => {
            const query = input.value.trim().toLowerCase();
            closeAllDropdowns();
            if (menu) menu.classList.remove('hidden');

            items.forEach(item => {
                const searchData = (item.getAttribute('data-search') || '').toLowerCase();
                const code = (item.getAttribute('data-code') || '').toLowerCase();
                const name = (item.getAttribute('data-name') || '').toLowerCase();

                if (query === '' || searchData.includes(query) || name.includes(query) || code.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        };

        input.addEventListener('focus', filterAirports);
        input.addEventListener('input', filterAirports);
        input.addEventListener('click', (e) => {
            e.stopPropagation();
            filterAirports();
        });

        // Click on airport item
        items.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const val = item.getAttribute('data-val') || (item.getAttribute('data-name') + ' (' + item.getAttribute('data-code') + ')');
                const code = item.getAttribute('data-code');
                const name = item.getAttribute('data-name');
                input.value = val;

                const dispCode = wrapper.querySelector('#dispFromCode, #dispToCode');
                if (dispCode && code) dispCode.textContent = code;
                const dispName = wrapper.querySelector('#dispFromName, #dispToName');
                if (dispName && name) dispName.textContent = name;

                closeAllDropdowns();
            });
        });
    });

    // Flight Swap Button (Origin <-> Destination)
    const flightSwapBtn = document.getElementById('flightSwapBtn');
    const flightFromInput = document.getElementById('flightFromInput');
    const flightToInput = document.getElementById('flightToInput');

    if (flightSwapBtn && flightFromInput && flightToInput) {
        flightSwapBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const tempVal = flightFromInput.value;
            flightFromInput.value = flightToInput.value;
            flightToInput.value = tempVal;
        });
    }

    // Flight Trip Type Toggle (One Way / Round Trip / Multi-City)
    const flightTypeRadios = document.querySelectorAll('.flight-type-radio');
    const flightReturnDateBlock = document.getElementById('flightReturnDateBlock');
    const flightMultiCityContainer = document.getElementById('flightMultiCityContainer');
    const flightReturnDateHidden = document.getElementById('flightReturnDateHidden');
    const flightDateLabel = document.getElementById('flightDateLabel');
    const flightDateInput = document.getElementById('flightDateInput');
    const flightDepDate = document.getElementById('flightDepDate');
    const flightRetDate = document.getElementById('flightRetDate');
    const flightFrom2Input = document.getElementById('flightFrom2Input');

    function updateFlightDateDisplay() {
        const selectedType = document.querySelector('.flight-type-radio:checked')?.value || 'oneway';
        const depVal = flightDepDate?.value;
        const retVal = flightRetDate?.value;

        if (selectedType === 'roundtrip') {
            if (flightDateLabel) flightDateLabel.textContent = 'Departure & Return';
            if (flightDateInput && depVal) {
                flightDateInput.value = depVal;
            }
            if (flightReturnDateHidden && retVal) {
                flightReturnDateHidden.value = retVal;
            }
        } else if (selectedType === 'multicity') {
            if (flightDateLabel) flightDateLabel.textContent = 'Leg 1 Departure';
            if (flightDateInput && depVal) {
                flightDateInput.value = depVal;
            }
            if (flightReturnDateHidden) {
                flightReturnDateHidden.value = '';
            }
        } else {
            if (flightDateLabel) flightDateLabel.textContent = 'Departure Date';
            if (flightDateInput && depVal) {
                flightDateInput.value = depVal;
            }
            if (flightReturnDateHidden) {
                flightReturnDateHidden.value = '';
            }
        }
    }

    flightTypeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'roundtrip') {
                if (flightReturnDateBlock) flightReturnDateBlock.classList.remove('hidden');
                if (flightMultiCityContainer) flightMultiCityContainer.classList.add('hidden');
                if (flightReturnDateHidden && flightRetDate) {
                    flightReturnDateHidden.value = flightRetDate.value;
                }
            } else if (radio.value === 'multicity') {
                if (flightReturnDateBlock) flightReturnDateBlock.classList.add('hidden');
                if (flightMultiCityContainer) flightMultiCityContainer.classList.remove('hidden');
                if (flightReturnDateHidden) {
                    flightReturnDateHidden.value = '';
                }
                // Auto-sync Leg 2 From with Leg 1 Destination if empty
                if (flightFrom2Input && !flightFrom2Input.value && flightToInput && flightToInput.value) {
                    flightFrom2Input.value = flightToInput.value;
                }
            } else {
                if (flightReturnDateBlock) flightReturnDateBlock.classList.add('hidden');
                if (flightMultiCityContainer) flightMultiCityContainer.classList.add('hidden');
                if (flightReturnDateHidden) {
                    flightReturnDateHidden.value = '';
                }
            }
            updateFlightDateDisplay();
        });
    });

    if (flightDepDate) {
        flightDepDate.addEventListener('change', () => {
            if (flightRetDate && flightDepDate.value) {
                flightRetDate.min = flightDepDate.value;
                // If return date is earlier than departure date, advance it
                if (!flightRetDate.value || flightRetDate.value < flightDepDate.value) {
                    const d = new Date(flightDepDate.value);
                    d.setDate(d.getDate() + 3);
                    flightRetDate.value = d.toISOString().split('T')[0];
                }
            }
            updateFlightDateDisplay();
            const selectedType = document.querySelector('.flight-type-radio:checked')?.value || 'oneway';
            if (selectedType !== 'roundtrip') {
                closeAllDropdowns();
            }
        });
    }

    if (flightRetDate) {
        flightRetDate.addEventListener('change', () => {
            updateFlightDateDisplay();
            closeAllDropdowns();
        });
    }


    // Flight Cabin Class Chips & Apply Button
    const flightCabinChips = document.querySelectorAll('.flight-cabin-chip');
    let selectedCabin = 'Economy';

    flightCabinChips.forEach(chip => {
        chip.addEventListener('click', (e) => {
            e.stopPropagation();
            flightCabinChips.forEach(c => {
                c.classList.remove('bg-brand-600', 'text-white', 'active');
                c.classList.add('bg-slate-100', 'text-slate-700');
            });
            chip.classList.remove('bg-slate-100', 'text-slate-700');
            chip.classList.add('bg-brand-600', 'text-white', 'active');
            selectedCabin = chip.getAttribute('data-cabin') || 'Economy';
        });
    });

    const flightApplyBtn = document.getElementById('flightApplyBtn');
    const flightClassInput = document.getElementById('flightClassInput');
    const flightClassDisplayInput = document.getElementById('flightClassDisplayInput');
    const flightAdultsHidden = document.getElementById('flightAdultsHidden');
    const flightAdultsCount = document.getElementById('flightAdultsCount');

    if (flightApplyBtn) {
        flightApplyBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const adults = flightAdultsCount ? parseInt(flightAdultsCount.textContent, 10) : 1;
            const cabin = typeof selectedCabin !== 'undefined' ? selectedCabin : 'Economy';
            if (flightClassDisplayInput) {
                flightClassDisplayInput.value = `${cabin}, ${adults} Traveler${adults > 1 ? 's' : ''}`;
            }
            if (flightClassInput) {
                flightClassInput.value = cabin;
            }
            if (flightAdultsHidden) {
                flightAdultsHidden.value = adults;
            }
            closeAllDropdowns();
        });
    }

    // Hotel Apply Button (Rooms & Guests)
    const hotelApplyBtn = document.getElementById('hotelApplyBtn');
    const hotelGuestsInput = document.getElementById('hotelGuestsInput');
    const hotelRoomsCount = document.getElementById('hotelRoomsCount');
    const hotelAdultsCount = document.getElementById('hotelAdultsCount');

    if (hotelApplyBtn && hotelGuestsInput) {
        hotelApplyBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const rooms = hotelRoomsCount ? parseInt(hotelRoomsCount.textContent, 10) : 1;
            const adults = hotelAdultsCount ? parseInt(hotelAdultsCount.textContent, 10) : 2;
            hotelGuestsInput.value = `${rooms} Room${rooms > 1 ? 's' : ''}, ${adults} Guest${adults > 1 ? 's' : ''}`;
            closeAllDropdowns();
        });
    }

    // ==========================================
    // 8. Search Button Click Handler (Submits Active Panel Form)
    // ==========================================
    const topSearchBtn = document.getElementById('topSearchBtn');
    if (topSearchBtn) {
        topSearchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const activePanel = document.querySelector('.tab-panel:not(.hidden)');
            if (activePanel) {
                const form = activePanel.querySelector('form');
                if (form) {
                    topSearchBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Searching...</span>`;
                    setTimeout(() => {
                        form.submit();
                    }, 200);
                }
            }
        });
    }

    const mobileHeroSearchBtn = document.getElementById('mobileHeroSearchBtn');
    if (mobileHeroSearchBtn && topSearchBtn) {
        mobileHeroSearchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            mobileHeroSearchBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Searching...</span>`;
            topSearchBtn.click();
        });
    }

    // ==========================================
    // 9. Domestic Packages Slider Navigation, Filtering & Auto-Slide
    // ==========================================
    const domesticSlider = document.getElementById('domesticSlider');
    const domesticSlidePrev = document.getElementById('domesticSlidePrev');
    const domesticSlideNext = document.getElementById('domesticSlideNext');
    const sliderContainer = document.querySelector('.domestic-slider-container');
    let resetAutoSlide = null;

    if (domesticSlider && domesticSlidePrev && domesticSlideNext) {
        const scrollStep = 355; // Card width + gap
        let autoSlideTimer = null;
        const autoSlideInterval = 3800; // Auto-slide every 3.8 seconds

        function updateSliderButtons() {
            const atStart = domesticSlider.scrollLeft <= 5;
            const atEnd = domesticSlider.scrollLeft + domesticSlider.clientWidth >= domesticSlider.scrollWidth - 10;
            domesticSlidePrev.disabled = atStart;
            domesticSlideNext.disabled = atEnd;
        }

        function slideNext() {
            const atEnd = domesticSlider.scrollLeft + domesticSlider.clientWidth >= domesticSlider.scrollWidth - 15;
            if (atEnd) {
                domesticSlider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                domesticSlider.scrollBy({ left: scrollStep, behavior: 'smooth' });
            }
        }

        function slidePrev() {
            const atStart = domesticSlider.scrollLeft <= 15;
            if (atStart) {
                domesticSlider.scrollTo({ left: domesticSlider.scrollWidth, behavior: 'smooth' });
            } else {
                domesticSlider.scrollBy({ left: -scrollStep, behavior: 'smooth' });
            }
        }

        domesticSlidePrev.addEventListener('click', () => {
            slidePrev();
            if (resetAutoSlide) resetAutoSlide();
        });

        domesticSlideNext.addEventListener('click', () => {
            slideNext();
            if (resetAutoSlide) resetAutoSlide();
        });

        domesticSlider.addEventListener('scroll', () => {
            updateSliderButtons();
        });

        // Auto-Slide Functions
        function startAutoSlide() {
            stopAutoSlide();
            autoSlideTimer = setInterval(() => {
                slideNext();
            }, autoSlideInterval);
        }

        function stopAutoSlide() {
            if (autoSlideTimer) {
                clearInterval(autoSlideTimer);
                autoSlideTimer = null;
            }
        }

        resetAutoSlide = function() {
            stopAutoSlide();
            startAutoSlide();
        };

        // Pause auto-slide on hover or touch interaction
        const hoverTarget = sliderContainer || domesticSlider;
        hoverTarget.addEventListener('mouseenter', stopAutoSlide);
        hoverTarget.addEventListener('mouseleave', startAutoSlide);
        hoverTarget.addEventListener('touchstart', stopAutoSlide, { passive: true });
        hoverTarget.addEventListener('touchend', startAutoSlide, { passive: true });

        // Start auto-slide on load
        startAutoSlide();
        setTimeout(updateSliderButtons, 150);
    }

    const domesticFilterBtns = document.querySelectorAll('.domestic-filter-btn');
    const packageCards = document.querySelectorAll('.package-card');

    if (domesticFilterBtns.length > 0) {
        domesticFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-filter');

                // Reset buttons styling (for white section background)
                domesticFilterBtns.forEach(b => {
                    b.classList.remove('bg-brand-600', 'text-white');
                    b.classList.add('bg-slate-100', 'text-slate-700', 'border', 'border-slate-200');
                });

                // Set active button
                btn.classList.remove('bg-slate-100', 'text-slate-700', 'border', 'border-slate-200');
                btn.classList.add('bg-brand-600', 'text-white');

                // Filter cards
                packageCards.forEach(card => {
                    const category = card.getAttribute('data-category') || '';
                    if (filter === 'all' || category.includes(filter)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Reset slider to beginning after filtering and resume auto-slide
                if (domesticSlider) {
                    domesticSlider.scrollTo({ left: 0, behavior: 'smooth' });
                    if (resetAutoSlide) resetAutoSlide();
                }
            });
        });
    }

    // Wishlist heart toggle
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const icon = btn.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-regular')) {
                    icon.classList.remove('fa-regular', 'text-slate-600');
                    icon.classList.add('fa-solid', 'text-rose-500');
                } else {
                    icon.classList.remove('fa-solid', 'text-rose-500');
                    icon.classList.add('fa-regular', 'text-slate-600');
                }
            }
        });
    });

    // ==========================================
    // 10. Flight Deals Slider Navigation, Filtering & Auto-Slide
    // ==========================================
    const flightSlider = document.getElementById('flightSlider');
    const flightSlidePrev = document.getElementById('flightSlidePrev');
    const flightSlideNext = document.getElementById('flightSlideNext');
    const flightSliderContainer = document.querySelector('.flight-slider-container');
    let resetFlightAutoSlide = null;

    if (flightSlider && flightSlidePrev && flightSlideNext) {
        function getFlightScrollStep() {
            const firstCard = flightSlider.querySelector('.flight-card');
            return firstCard ? firstCard.offsetWidth + 20 : 380;
        }

        let flightAutoSlideTimer = null;
        const flightAutoSlideInterval = 4200; // Auto-slide every 4.2 seconds

        function updateFlightSliderButtons() {
            const atStart = flightSlider.scrollLeft <= 5;
            const atEnd = flightSlider.scrollLeft + flightSlider.clientWidth >= flightSlider.scrollWidth - 10;
            flightSlidePrev.disabled = atStart;
            flightSlideNext.disabled = atEnd;
        }

        function slideFlightNext() {
            const atEnd = flightSlider.scrollLeft + flightSlider.clientWidth >= flightSlider.scrollWidth - 15;
            if (atEnd) {
                flightSlider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                flightSlider.scrollBy({ left: getFlightScrollStep(), behavior: 'smooth' });
            }
        }

        function slideFlightPrev() {
            const atStart = flightSlider.scrollLeft <= 15;
            if (atStart) {
                flightSlider.scrollTo({ left: flightSlider.scrollWidth, behavior: 'smooth' });
            } else {
                flightSlider.scrollBy({ left: -getFlightScrollStep(), behavior: 'smooth' });
            }
        }

        flightSlidePrev.addEventListener('click', () => {
            slideFlightPrev();
            if (resetFlightAutoSlide) resetFlightAutoSlide();
        });

        flightSlideNext.addEventListener('click', () => {
            slideFlightNext();
            if (resetFlightAutoSlide) resetFlightAutoSlide();
        });

        flightSlider.addEventListener('scroll', () => {
            updateFlightSliderButtons();
        });

        // Flight Auto-Slide Functions
        function startFlightAutoSlide() {
            stopFlightAutoSlide();
            flightAutoSlideTimer = setInterval(() => {
                slideFlightNext();
            }, flightAutoSlideInterval);
        }

        function stopFlightAutoSlide() {
            if (flightAutoSlideTimer) {
                clearInterval(flightAutoSlideTimer);
                flightAutoSlideTimer = null;
            }
        }

        resetFlightAutoSlide = function() {
            stopFlightAutoSlide();
            startFlightAutoSlide();
        };

        // Pause auto-slide on hover or touch
        const flightHoverTarget = flightSliderContainer || flightSlider;
        flightHoverTarget.addEventListener('mouseenter', stopFlightAutoSlide);
        flightHoverTarget.addEventListener('mouseleave', startFlightAutoSlide);
        flightHoverTarget.addEventListener('touchstart', stopFlightAutoSlide, { passive: true });
        flightHoverTarget.addEventListener('touchend', startFlightAutoSlide, { passive: true });

        // Start auto-slide on load
        startFlightAutoSlide();
        setTimeout(updateFlightSliderButtons, 150);
    }



    // Book flight CTA - Switch to Flights tab in Hero search
    document.querySelectorAll('.book-flight-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const flightTab = document.querySelector('[data-target="panel-flights"]');
            if (flightTab) {
                flightTab.click();
            }
        });
    });

    // ==========================================
    // 11. International Packages Filtering
    // ==========================================
    const intlFilterBtns = document.querySelectorAll('.intl-filter-btn');
    const intlCards = document.querySelectorAll('.intl-card');

    if (intlFilterBtns.length > 0) {
        intlFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-intl-filter');

                // Reset filter buttons to sleek track state
                intlFilterBtns.forEach(b => {
                    b.classList.remove('bg-brand-600', 'text-white', 'font-bold');
                    b.classList.add('text-slate-600', 'font-semibold', 'hover:text-brand-600', 'hover:bg-white');
                    const icon = b.querySelector('i');
                    if (icon) {
                        icon.classList.remove('text-white');
                        icon.classList.add('text-brand-600');
                    }
                });

                // Set active button
                btn.classList.remove('text-slate-600', 'font-semibold', 'hover:text-brand-600', 'hover:bg-white');
                btn.classList.add('bg-brand-600', 'text-white', 'font-bold');
                const activeIcon = btn.querySelector('i');
                if (activeIcon) {
                    activeIcon.classList.remove('text-brand-600');
                    activeIcon.classList.add('text-white');
                }

                // Filter cards
                intlCards.forEach(card => {
                    const category = card.getAttribute('data-category') || '';
                    if (filter === 'all' || category.includes(filter)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // ==========================================
    // 12. Hotels & Resorts 2-Row Slider Navigation, Destination Filter & Booking CTA
    // ==========================================
    const hotelsSlider = document.getElementById('hotelsSlider');
    const hotelSlidePrev = document.getElementById('hotelSlidePrev');
    const hotelSlideNext = document.getElementById('hotelSlideNext');
    const hotelSliderContainer = document.querySelector('.hotel-slider-container');
    const hotelDestBtns = document.querySelectorAll('.hotel-dest-btn');
    const hotelCards = document.querySelectorAll('.hotel-card');
    const activeLocationCount = document.getElementById('activeLocationCount');
    let resetHotelAutoSlide = null;

    if (hotelsSlider && hotelSlidePrev && hotelSlideNext) {
        let hotelAutoSlideTimer = null;
        const hotelAutoSlideInterval = 4200; // Auto-slide every 4.2 seconds

        function getHotelScrollStep() {
            const firstCard = hotelsSlider.querySelector('.hotel-card:not([style*="display: none"])');
            if (firstCard) {
                return firstCard.offsetWidth + 16;
            }
            return 400;
        }

        function updateHotelSliderButtons() {
            const atStart = hotelsSlider.scrollLeft <= 10;
            const atEnd = hotelsSlider.scrollLeft + hotelsSlider.clientWidth >= hotelsSlider.scrollWidth - 15;
            hotelSlidePrev.disabled = atStart;
            hotelSlideNext.disabled = atEnd;
        }

        function slideHotelNext() {
            const atEnd = hotelsSlider.scrollLeft + hotelsSlider.clientWidth >= hotelsSlider.scrollWidth - 15;
            if (atEnd) {
                hotelsSlider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                hotelsSlider.scrollBy({ left: getHotelScrollStep(), behavior: 'smooth' });
            }
        }

        function slideHotelPrev() {
            const atStart = hotelsSlider.scrollLeft <= 15;
            if (atStart) {
                hotelsSlider.scrollTo({ left: hotelsSlider.scrollWidth, behavior: 'smooth' });
            } else {
                hotelsSlider.scrollBy({ left: -getHotelScrollStep(), behavior: 'smooth' });
            }
        }

        hotelSlidePrev.addEventListener('click', () => {
            slideHotelPrev();
            if (resetHotelAutoSlide) resetHotelAutoSlide();
        });

        hotelSlideNext.addEventListener('click', () => {
            slideHotelNext();
            if (resetHotelAutoSlide) resetHotelAutoSlide();
        });

        hotelsSlider.addEventListener('scroll', () => {
            updateHotelSliderButtons();
        });

        function startHotelAutoSlide() {
            stopHotelAutoSlide();
            hotelAutoSlideTimer = setInterval(() => {
                slideHotelNext();
            }, hotelAutoSlideInterval);
        }

        function stopHotelAutoSlide() {
            if (hotelAutoSlideTimer) {
                clearInterval(hotelAutoSlideTimer);
                hotelAutoSlideTimer = null;
            }
        }

        resetHotelAutoSlide = function() {
            stopHotelAutoSlide();
            startHotelAutoSlide();
        };

        // Pause auto-slide on hover or touch
        const hotelHoverTarget = hotelSliderContainer || hotelsSlider;
        hotelHoverTarget.addEventListener('mouseenter', stopHotelAutoSlide);
        hotelHoverTarget.addEventListener('mouseleave', startHotelAutoSlide);
        hotelHoverTarget.addEventListener('touchstart', stopHotelAutoSlide, { passive: true });
        hotelHoverTarget.addEventListener('touchend', startHotelAutoSlide, { passive: true });

        startHotelAutoSlide();
        setTimeout(updateHotelSliderButtons, 150);
    }

    if (hotelDestBtns.length > 0) {
        hotelDestBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const locId = btn.getAttribute('data-location-id');
                const destName = btn.querySelector('h4')?.textContent.trim() || 'Selected Location';

                // Reset all destination buttons styling
                hotelDestBtns.forEach(b => {
                    b.classList.remove('bg-brand-600', 'text-white', 'border-brand-600', 'font-bold', 'active-dest');
                    b.classList.add('bg-white', 'text-slate-700', 'border-slate-200', 'hover:border-brand-500', 'hover:bg-slate-50', 'font-semibold');
                });

                // Set clicked button active
                btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200', 'hover:border-brand-500', 'hover:bg-slate-50', 'font-semibold');
                btn.classList.add('bg-brand-600', 'text-white', 'border-brand-600', 'font-bold', 'active-dest');

                // Filter hotel cards
                let visibleCount = 0;
                hotelCards.forEach(card => {
                    const cardLoc = card.getAttribute('data-location') || '';
                    if (locId === 'all' || cardLoc === locId) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Reset slider to beginning after filtering
                if (hotelsSlider) {
                    hotelsSlider.scrollTo({ left: 0, behavior: 'smooth' });
                    if (resetHotelAutoSlide) resetHotelAutoSlide();
                }

                // Update active count text
                if (activeLocationCount) {
                    if (locId === 'all') {
                        activeLocationCount.textContent = `Showing all ${visibleCount} top-rated stays`;
                    } else {
                        activeLocationCount.textContent = `Showing ${visibleCount} handpicked stays in ${destName}`;
                    }
                }
            });
        });
    }

    // View Rooms CTA - Switch to Hotels tab in Hero search
    document.querySelectorAll('.book-hotel-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const hotelTab = document.querySelector('[data-target="panel-hotels"]');
            if (hotelTab) {
                hotelTab.click();
            }
            const hotelName = btn.getAttribute('data-hotel-name');
            const hotelLocation = btn.getAttribute('data-location');
            if (hotelName) {
                const hotelsPanel = document.getElementById('panel-hotels');
                if (hotelsPanel) {
                    const fieldVal = hotelsPanel.querySelector('.field-val');
                    const fieldSub = hotelsPanel.querySelector('.field-sub');
                    if (fieldVal) fieldVal.textContent = hotelName;
                    if (fieldSub && hotelLocation) fieldSub.textContent = `Selected property in ${hotelLocation.toUpperCase()}`;
                }
            }
        });
    });

    // ==========================================
    // 13. Curated Experiences & Themes Interactive Handlers
    // ==========================================
    document.querySelectorAll('.theme-spot-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const href = btn.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('#')) {
                // Allow direct navigation to search page
                return;
            }
            e.preventDefault();
            const spotName = btn.getAttribute('data-spot');
            const spotType = btn.getAttribute('data-type'); // 'dom' or 'intl'
            const themeName = btn.getAttribute('data-theme');

            // Switch Hero search tab based on destination type
            const targetTab = spotType === 'intl' 
                ? document.querySelector('[data-target="panel-international"]')
                : document.querySelector('[data-target="panel-domestic"]');
            
            if (targetTab) {
                targetTab.click();
            }

            // Populate destination in target panel
            const panelId = spotType === 'intl' ? 'panel-international' : 'panel-domestic';
            const panel = document.getElementById(panelId);
            if (panel) {
                const valEl = panel.querySelector('.field-val');
                const subEl = panel.querySelector('.field-sub');
                if (valEl && spotName) valEl.textContent = spotName;
                if (subEl) subEl.textContent = `Trending in ${themeName || 'Packages'}`;
            }

            // Scroll to Hero Search
            const heroSearch = document.getElementById('hero');
            if (heroSearch) {
                heroSearch.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    document.querySelectorAll('.theme-cta-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const href = btn.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('#')) {
                // Allow direct navigation to search page
                return;
            }
            const themeName = btn.getAttribute('data-theme');
            const targetTab = document.querySelector('[data-target="panel-domestic"]');
            if (targetTab) {
                targetTab.click();
            }
            const panel = document.getElementById('panel-domestic');
            if (panel && themeName) {
                const themeVal = panel.querySelectorAll('.field-val')[1];
                if (themeVal) themeVal.textContent = themeName;
            }
        });
    });

    // ==========================================
    // 14. Testimonials Slider, Auto-Slide & Category Filter
    // ==========================================
    const testimonialsSlider = document.getElementById('testimonialsSlider');
    const testimonialSlidePrev = document.getElementById('testimonialSlidePrev');
    const testimonialSlideNext = document.getElementById('testimonialSlideNext');
    const testimonialsContainer = document.querySelector('.testimonials-slider-container');
    const testimonialFilterBtns = document.querySelectorAll('.testimonial-filter-btn');
    const testimonialCards = document.querySelectorAll('.testimonial-card-item');
    let resetTestimonialAutoSlide = null;

    if (testimonialsSlider && testimonialSlidePrev && testimonialSlideNext) {
        let testimonialAutoSlideTimer = null;
        const testimonialAutoSlideInterval = 4500; // 4.5 seconds

        function getTestimonialScrollStep() {
            const firstCard = testimonialsSlider.querySelector('.testimonial-card-item:not([style*="display: none"])');
            if (firstCard) {
                return firstCard.offsetWidth + 24;
            }
            return 360;
        }

        function updateTestimonialButtons() {
            const atStart = testimonialsSlider.scrollLeft <= 10;
            const atEnd = testimonialsSlider.scrollLeft + testimonialsSlider.clientWidth >= testimonialsSlider.scrollWidth - 15;
            testimonialSlidePrev.disabled = atStart;
            testimonialSlideNext.disabled = atEnd;
        }

        function slideTestimonialNext() {
            const atEnd = testimonialsSlider.scrollLeft + testimonialsSlider.clientWidth >= testimonialsSlider.scrollWidth - 15;
            if (atEnd) {
                testimonialsSlider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                testimonialsSlider.scrollBy({ left: getTestimonialScrollStep(), behavior: 'smooth' });
            }
        }

        function slideTestimonialPrev() {
            const atStart = testimonialsSlider.scrollLeft <= 15;
            if (atStart) {
                testimonialsSlider.scrollTo({ left: testimonialsSlider.scrollWidth, behavior: 'smooth' });
            } else {
                testimonialsSlider.scrollBy({ left: -getTestimonialScrollStep(), behavior: 'smooth' });
            }
        }

        testimonialSlidePrev.addEventListener('click', () => {
            slideTestimonialPrev();
            if (resetTestimonialAutoSlide) resetTestimonialAutoSlide();
        });

        testimonialSlideNext.addEventListener('click', () => {
            slideTestimonialNext();
            if (resetTestimonialAutoSlide) resetTestimonialAutoSlide();
        });

        testimonialsSlider.addEventListener('scroll', () => {
            updateTestimonialButtons();
        });

        function startTestimonialAutoSlide() {
            stopTestimonialAutoSlide();
            testimonialAutoSlideTimer = setInterval(() => {
                slideTestimonialNext();
            }, testimonialAutoSlideInterval);
        }

        function stopTestimonialAutoSlide() {
            if (testimonialAutoSlideTimer) {
                clearInterval(testimonialAutoSlideTimer);
                testimonialAutoSlideTimer = null;
            }
        }

        resetTestimonialAutoSlide = function() {
            stopTestimonialAutoSlide();
            startTestimonialAutoSlide();
        };

        const testHoverTarget = testimonialsContainer || testimonialsSlider;
        testHoverTarget.addEventListener('mouseenter', stopTestimonialAutoSlide);
        testHoverTarget.addEventListener('mouseleave', startTestimonialAutoSlide);
        testHoverTarget.addEventListener('touchstart', stopTestimonialAutoSlide, { passive: true });
        testHoverTarget.addEventListener('touchend', startTestimonialAutoSlide, { passive: true });

        startTestimonialAutoSlide();
        setTimeout(updateTestimonialButtons, 150);
    }

    if (testimonialFilterBtns.length > 0) {
        testimonialFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-testimonial-filter');

                // Reset filter buttons
                testimonialFilterBtns.forEach(b => {
                    b.classList.remove('bg-brand-600', 'text-white', 'border-brand-600', 'font-bold', 'active-filter');
                    b.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-200', 'hover:border-brand-500', 'hover:bg-white', 'font-semibold');
                    const icon = b.querySelector('i');
                    if (icon) {
                        icon.classList.remove('text-white');
                        icon.classList.add('text-brand-600');
                    }
                });

                // Set active button
                btn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200', 'hover:border-brand-500', 'hover:bg-white', 'font-semibold');
                btn.classList.add('bg-brand-600', 'text-white', 'border-brand-600', 'font-bold', 'active-filter');
                const activeIcon = btn.querySelector('i');
                if (activeIcon) {
                    activeIcon.classList.remove('text-brand-600');
                    activeIcon.classList.add('text-white');
                }

                // Filter cards
                testimonialCards.forEach(card => {
                    const category = card.getAttribute('data-category') || '';
                    if (filter === 'all' || category.includes(filter)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Reset slider scroll to beginning
                if (testimonialsSlider) {
                    testimonialsSlider.scrollTo({ left: 0, behavior: 'smooth' });
                    if (resetTestimonialAutoSlide) resetTestimonialAutoSlide();
                }
            });
        });
    }

});
