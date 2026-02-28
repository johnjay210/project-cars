// Google Maps functionality for car locations

// IMPORTANT: Replace 'YOUR_GOOGLE_MAPS_API_KEY' with your actual Google Maps API key
// Get your API key from: https://console.cloud.google.com/google/maps-apis
const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with your API key

let map;
let marker;

// Load Google Maps script
function loadGoogleMaps() {
    if (window.google && window.google.maps) {
        initGoogleMap();
        return;
    }
    
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&callback=initGoogleMap`;
    script.async = true;
    script.defer = true;
    script.onerror = function() {
        console.error('Failed to load Google Maps. Please check your API key.');
        fallbackToOpenStreetMap();
    };
    document.head.appendChild(script);
}

// Initialize Google Map
function initGoogleMap() {
    const latitude = parseFloat(document.getElementById('car-latitude')?.value);
    const longitude = parseFloat(document.getElementById('car-longitude')?.value);
    const city = document.getElementById('car-city')?.value;
    const state = document.getElementById('car-state')?.value;
    const address = document.getElementById('car-address')?.value;
    
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;
    
    // If no API key is set, use fallback
    if (GOOGLE_MAPS_API_KEY === 'YOUR_GOOGLE_MAPS_API_KEY') {
        console.warn('Google Maps API key not configured. Using fallback map.');
        fallbackToOpenStreetMap();
        return;
    }
    
    let mapCenter;
    let mapZoom = 12;
    
    if (latitude && longitude && !isNaN(latitude) && !isNaN(longitude)) {
        mapCenter = { lat: latitude, lng: longitude };
        mapZoom = 15;
    } else if (city && state) {
        // Geocode city and state
        geocodeLocation(city + ', ' + state, function(coords) {
            if (coords) {
                mapCenter = coords;
                createGoogleMap(mapCenter, mapZoom, address || city + ', ' + state);
            } else {
                fallbackToOpenStreetMap();
            }
        });
        return;
    } else {
        fallbackToOpenStreetMap();
        return;
    }
    
    createGoogleMap(mapCenter, mapZoom, address || city + ', ' + state);
}

function createGoogleMap(center, zoom, locationName) {
    const mapContainer = document.getElementById('map');
    
    map = new google.maps.Map(mapContainer, {
        center: center,
        zoom: zoom,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true
    });
    
    marker = new google.maps.Marker({
        position: center,
        map: map,
        title: locationName || 'Car Location',
        animation: google.maps.Animation.DROP
    });
    
    // Add info window
    const infoWindow = new google.maps.InfoWindow({
        content: `<div style="padding: 0.5rem;"><strong>${locationName || 'Car Location'}</strong></div>`
    });
    
    marker.addListener('click', function() {
        infoWindow.open(map, marker);
    });
    
    // Add link to open in Google Maps
    const mapLink = document.createElement('a');
    mapLink.href = `https://www.google.com/maps/search/?api=1&query=${center.lat},${center.lng}`;
    mapLink.target = '_blank';
    mapLink.className = 'map-link';
    mapLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Google Maps';
    mapLink.style.display = 'inline-block';
    mapLink.style.marginTop = '0.5rem';
    mapLink.style.color = 'var(--primary-color)';
    mapLink.style.textDecoration = 'none';
    mapLink.style.fontSize = '0.875rem';
    
    mapContainer.appendChild(mapLink);
}

function geocodeLocation(address, callback) {
    fetch(`api/geocode.php?address=${encodeURIComponent(address)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                callback({ lat: data.latitude, lng: data.longitude });
            } else {
                callback(null);
            }
        })
        .catch(error => {
            console.error('Geocoding error:', error);
            callback(null);
        });
}

function fallbackToOpenStreetMap() {
    const latitude = document.getElementById('car-latitude')?.value;
    const longitude = document.getElementById('car-longitude')?.value;
    const city = document.getElementById('car-city')?.value;
    const state = document.getElementById('car-state')?.value;
    const address = document.getElementById('car-address')?.value;
    
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;
    
    let mapUrl = '';
    let locationText = '';
    
    if (latitude && longitude) {
        mapUrl = `https://www.openstreetmap.org/export/embed.html?bbox=${parseFloat(longitude) - 0.01},${parseFloat(latitude) - 0.01},${parseFloat(longitude) + 0.01},${parseFloat(latitude) + 0.01}&layer=mapnik&marker=${latitude},${longitude}`;
        locationText = `${latitude}, ${longitude}`;
    } else if (city && state) {
        const locationQuery = encodeURIComponent(`${city}, ${state}${address ? ', ' + address : ''}`);
        mapUrl = `https://www.openstreetmap.org/search?query=${locationQuery}`;
        locationText = `${city}, ${state}`;
    } else {
        return;
    }
    
    const iframe = document.createElement('iframe');
    iframe.width = '100%';
    iframe.height = '300';
    iframe.frameBorder = '0';
    iframe.scrolling = 'no';
    iframe.marginHeight = '0';
    iframe.marginWidth = '0';
    iframe.src = mapUrl;
    iframe.style.border = '1px solid var(--border-color)';
    iframe.style.borderRadius = '0.5rem';
    
    mapContainer.innerHTML = '';
    mapContainer.appendChild(iframe);
    
    const mapLink = document.createElement('a');
    mapLink.href = `https://www.openstreetmap.org/search?query=${encodeURIComponent(locationText)}`;
    mapLink.target = '_blank';
    mapLink.className = 'map-link';
    mapLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Maps';
    mapLink.style.display = 'inline-block';
    mapLink.style.marginTop = '0.5rem';
    mapLink.style.color = 'var(--primary-color)';
    mapLink.style.textDecoration = 'none';
    mapLink.style.fontSize = '0.875rem';
    
    mapContainer.appendChild(mapLink);
}

// Make initGoogleMap globally available for callback
window.initGoogleMap = initGoogleMap;

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('map-container')) {
        loadGoogleMaps();
    }
});
