/* spaces.js
Page: spaces.html / spaces.php
Role: afficher les espaces + filtrer par ville et prix.
*/
var allSpaces = [];

function loadSpaces() {
    fillCityFilter();
    fetchSpaces();

    var button = document.getElementById('applyFilters');
    if (button) {
        button.addEventListener('click', applyFilters);
    }
}

function fetchSpaces() {
    fetch('/nomadnest/api/spaces.php')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                allSpaces = data.data;
                renderSpaces(allSpaces);
                fillCityFilterFromSpaces(allSpaces);
            } else {
                displayNoResults();
            }
        })
        .catch(function(error) {
            console.log(error);
            displayNoResults();
        });
}

function renderSpaces(spaces) {
    var grid = document.getElementById('spacesGrid');
    grid.innerHTML = '';

    if (spaces.length === 0) {
        displayNoResults();
        return;
    }

    for (var i = 0; i < spaces.length; i++) {
        grid.innerHTML += createSpaceCard(spaces[i]);
    }
}

function createSpaceCard(space) {
    // Status badge
    var statusClass = 'badge-green';
    var statusLabel = space.availability_status;
    if (space.availability_status === 'limited') statusClass = 'badge-amber';
    if (space.availability_status === 'full')    statusClass = 'badge-red';

    // Image
    var imageHtml = space.image
        ? '<img src="' + space.image + '" alt="' + space.name + '">'
        : '';

    // Amenity pills (max 3)
    var amenities = Array.isArray(space.amenities) ? space.amenities.slice(0, 3) : [];
    var amenityHtml = amenities.map(function(a) {
        return '<span class="amenity-pill">' + a + '</span>';
    }).join('');

    return `
        <div class="space-card" onclick="openSpaceDetails(${space.id})" style="cursor:pointer;">
            <div class="space-card-img">
                ${imageHtml}
            </div>
            <div class="space-card-body">
                <div class="space-card-top">
                    <span class="space-card-name">${space.name}</span>
                    <span class="space-card-rating">${space.rating}</span>
                </div>
                <div class="space-card-location">📍 ${space.city}</div>
                <div class="space-card-amenities">${amenityHtml}</div>
                <div class="space-card-footer">
                    <span class="space-card-price">${space.price_per_day} DT <span>/ day</span></span>
                    <span class="badge ${statusClass}">${statusLabel}</span>
                </div>
            </div>
        </div>
    `;
}

function applyFilters() {
    var city     = document.getElementById('cityFilter').value;
    var maxPrice = document.getElementById('maxPrice').value;
    fetchFilteredSpaces(city, maxPrice);
}

function fetchFilteredSpaces(city, maxPrice) {
    var url = '/nomadnest/api/spaces.php?city=' + encodeURIComponent(city);
    if (maxPrice !== '') {
        url += '&max_price=' + encodeURIComponent(maxPrice);
    }

    fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                renderSpaces(data.data);
            } else {
                displayNoResults();
            }
        })
        .catch(function(error) {
            console.log(error);
            displayNoResults();
        });
}

function sortSpaces(type) {
    if (type === 'price') {
        allSpaces.sort(function(a, b) { return a.price_per_day - b.price_per_day; });
    }
    if (type === 'rating') {
        allSpaces.sort(function(a, b) { return b.rating - a.rating; });
    }
    renderSpaces(allSpaces);
}

function openSpaceDetails(id) {
    window.location.href = '/nomadnest/pages/space.php?id=' + id;
}

function displayNoResults() {
    var grid = document.getElementById('spacesGrid');
    grid.innerHTML = `
        <div class="empty-state" style="grid-column:1/-1;">
            <div class="empty-icon">🏢</div>
            <p>No spaces found. Try adjusting your filters.</p>
        </div>
    `;
}

function fillCityFilter() {
    var cityFilter = document.getElementById('cityFilter');
    if (!cityFilter) return;
    if (typeof CITIES !== 'undefined') {
        CITIES.forEach(function(city) {
            cityFilter.innerHTML += '<option value="' + city + '">' + city + '</option>';
        });
    }
}

function fillCityFilterFromSpaces(spaces) {
    var cityFilter = document.getElementById('cityFilter');
    if (!cityFilter || cityFilter.options.length > 1) return;
    var cities = [];
    spaces.forEach(function(space) {
        if (cities.indexOf(space.city) === -1) {
            cities.push(space.city);
            cityFilter.innerHTML += '<option value="' + space.city + '">' + space.city + '</option>';
        }
    });
}

loadSpaces();