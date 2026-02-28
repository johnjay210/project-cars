// Nearby Car Search Functionality

// IMPORTANT: Replace with your Google Maps API key (same as in map.js)
const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with your API key

let userLocation = null;
let nearbyMap = null;
let nearbyMarkers = [];
let googleMapsLoaded = false;

document.addEventListener('DOMContentLoaded', function() {
    const nearbySearchToggle = document.getElementById('nearby-search-toggle');
    const nearbySearchPanel = document.getElementById('nearby-search-panel');
    const useCurrentLocationBtn = document.getElementById('use-current-location');
    const searchNearbyForm = document.getElementById('search-nearby-form');
    const nearbyResults = document.getElementById('nearby-results');
    const nearbyMapContainer = document.getElementById('nearby-map');
    
    if (nearbySearchToggle) {
        nearbySearchToggle.addEventListener('click', function() {
            nearbySearchPanel.classList.toggle('active');
            if (nearbySearchPanel.classList.contains('active') && nearbyMapContainer && !nearbyMap) {
                loadGoogleMapsForNearby();
            }
        });
    }
    
    if (useCurrentLocationBtn) {
        useCurrentLocationBtn.addEventListener('click', function() {
            getCurrentLocation();
        });
    }
    
    if (searchNearbyForm) {
        searchNearbyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            searchNearby();
        });
    }
});

function getCurrentLocation() {
    const statusText = document.getElementById('location-status');
    const useCurrentLocationBtn = document.getElementById('use-current-location');
    
    if (!navigator.geolocation) {
        showNotification('Geolocation is not supported by your browser', 'error');
        return;
    }
    
    if (statusText) statusText.textContent = 'Getting your location...';
    if (useCurrentLocationBtn) useCurrentLocationBtn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            userLocation = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            
            if (statusText) statusText.textContent = 'Location found!';
            if (useCurrentLocationBtn) useCurrentLocationBtn.disabled = false;
            
            // Auto-fill form
            document.getElementById('nearby-latitude').value = userLocation.latitude;
            document.getElementById('nearby-longitude').value = userLocation.longitude;
            
            // Geocode to get address
            reverseGeocode(userLocation.latitude, userLocation.longitude);
            
            showNotification('Location found! You can now search for nearby cars.', 'success');
        },
        function(error) {
            if (statusText) statusText.textContent = 'Could not get location';
            if (useCurrentLocationBtn) useCurrentLocationBtn.disabled = false;
            showNotification('Could not get your location. Please enter it manually.', 'error');
        }
    );
}

function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.address) {
                const address = data.address;
                if (document.getElementById('nearby-city')) {
                    document.getElementById('nearby-city').value = address.city || address.town || address.village || '';
                }
                if (document.getElementById('nearby-state')) {
                    document.getElementById('nearby-state').value = address.state || '';
                }
                if (document.getElementById('nearby-zip')) {
                    document.getElementById('nearby-zip').value = address.postcode || '';
                }
            }
        })
        .catch(error => console.error('Reverse geocoding error:', error));
}

function searchNearby() {
    const latitude = parseFloat(document.getElementById('nearby-latitude').value);
    const longitude = parseFloat(document.getElementById('nearby-longitude').value);
    const radius = parseFloat(document.getElementById('nearby-radius').value) || 50;
    const make = document.getElementById('nearby-make')?.value || '';
    const minPrice = document.getElementById('nearby-min-price')?.value || '';
    const maxPrice = document.getElementById('nearby-max-price')?.value || '';
    
    if (!latitude || !longitude || isNaN(latitude) || isNaN(longitude)) {
        showNotification('Please provide valid coordinates or use "Use Current Location"', 'error');
        return;
    }
    
    const nearbyResults = document.getElementById('nearby-results');
    if (nearbyResults) {
        nearbyResults.innerHTML = '<p>Searching...</p>';
    }
    
    const params = new URLSearchParams({
        latitude: latitude,
        longitude: longitude,
        radius: radius,
        make: make,
        min_price: minPrice,
        max_price: maxPrice
    });
    
    fetch(`api/nearby-cars.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNearbyResults(data.cars, latitude, longitude);
                loadGoogleMapsForNearby(function() {
                    updateNearbyMap(data.cars, latitude, longitude);
                });
            } else {
                if (nearbyResults) {
                    nearbyResults.innerHTML = `<p class="error">${data.message || 'No cars found nearby'}</p>`;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error searching for nearby cars', 'error');
        });
}

function displayNearbyResults(cars, centerLat, centerLng) {
    const nearbyResults = document.getElementById('nearby-results');
    if (!nearbyResults) return;
    
    if (cars.length === 0) {
        nearbyResults.innerHTML = '<p class="no-results">No cars found nearby. Try increasing the search radius.</p>';
        return;
    }
    
    let html = `<h3>Found ${cars.length} car(s) nearby</h3>`;
    html += '<div class="nearby-cars-list">';
    
    cars.forEach(car => {
        const distance = car.distance ? car.distance.toFixed(1) : 'N/A';
        html += `
            <div class="nearby-car-item">
                <div class="nearby-car-image">
                    ${car.image_path && car.image_path !== '' ? 
                        `<img src="${car.image_path}" alt="${car.make} ${car.model}">` : 
                        '<img src="assets/images/placeholder-car.jpg" alt="Car placeholder">'}
                </div>
                <div class="nearby-car-info">
                    <h4><a href="car-details.php?id=${car.id}">${car.year} ${car.make} ${car.model}</a></h4>
                    <p class="nearby-car-price">$${parseFloat(car.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    <p class="nearby-car-distance"><i class="fas fa-map-marker-alt"></i> ${distance} miles away</p>
                    <p class="nearby-car-location">${car.city || ''}${car.city && car.state ? ', ' : ''}${car.state || ''}</p>
                    <a href="car-details.php?id=${car.id}" class="btn btn-primary btn-sm">View Details</a>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    nearbyResults.innerHTML = html;
}

function loadGoogleMapsForNearby(callback) {
    if (googleMapsLoaded || (typeof google !== 'undefined' && google.maps)) {
        googleMapsLoaded = true;
        if (callback) callback();
        return;
    }
    
    if (GOOGLE_MAPS_API_KEY === 'YOUR_GOOGLE_MAPS_API_KEY') {
        const mapContainer = document.getElementById('nearby-map');
        if (mapContainer) {
            mapContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: var(--text-light);">Please configure Google Maps API key in nearby-search.js to view map</p>';
        }
        return;
    }
    
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&callback=initNearbyMapCallback`;
    script.async = true;
    script.defer = true;
    script.onerror = function() {
        console.error('Failed to load Google Maps for nearby search');
        const mapContainer = document.getElementById('nearby-map');
        if (mapContainer) {
            mapContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: var(--text-light);">Failed to load map. Please check your API key.</p>';
        }
    };
    document.head.appendChild(script);
    
    window.initNearbyMapCallback = function() {
        googleMapsLoaded = true;
        if (callback) callback();
        initNearbyMap();
    };
}

function initNearbyMap() {
    const mapContainer = document.getElementById('nearby-map');
    if (!mapContainer) return;
    
    // Check if Google Maps is available
    if (typeof google !== 'undefined' && google.maps) {
        nearbyMap = new google.maps.Map(mapContainer, {
            center: { lat: 39.8283, lng: -98.5795 }, // Center of USA
            zoom: 4,
            mapTypeControl: true,
            streetViewControl: false
        });
    } else {
        mapContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: var(--text-light);">Map requires Google Maps API key</p>';
    }
}

function updateNearbyMap(cars, centerLat, centerLng) {
    if (!nearbyMap || !cars || cars.length === 0) return;
    
    // Clear existing markers
    nearbyMarkers.forEach(marker => marker.setMap(null));
    nearbyMarkers = [];
    
    // Center map on search location
    nearbyMap.setCenter({ lat: centerLat, lng: centerLng });
    nearbyMap.setZoom(10);
    
    // Add marker for search center
    const centerMarker = new google.maps.Marker({
        position: { lat: centerLat, lng: centerLng },
        map: nearbyMap,
        title: 'Search Location',
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: '#4285F4',
            fillOpacity: 1,
            strokeColor: '#fff',
            strokeWeight: 2
        }
    });
    nearbyMarkers.push(centerMarker);
    
    // Add markers for each car
    cars.forEach(car => {
        if (car.latitude && car.longitude) {
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(car.latitude), lng: parseFloat(car.longitude) },
                map: nearbyMap,
                title: `${car.year} ${car.make} ${car.model} - $${car.price}`,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    fillColor: '#FF0000',
                    fillOpacity: 1,
                    strokeColor: '#fff',
                    strokeWeight: 2
                }
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 0.5rem;">
                        <strong><a href="car-details.php?id=${car.id}" style="color: var(--primary-color); text-decoration: none;">${car.year} ${car.make} ${car.model}</a></strong><br>
                        <span>$${parseFloat(car.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span><br>
                        <span>${car.distance ? car.distance.toFixed(1) + ' miles away' : ''}</span>
                    </div>
                `
            });
            
            marker.addListener('click', function() {
                infoWindow.open(nearbyMap, marker);
            });
            
            nearbyMarkers.push(marker);
        }
    });
}

function showNotification(message, type) {
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

