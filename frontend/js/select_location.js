/**
 * Select Location Component JavaScript
 * Allows users to select a location using an interactive map
 */

// Configuration
const SELECT_LOCATION_CONFIG = {
    GEOCODING_URL: '/backend/src/api/geocode.php',
    // Default map center (if no location is selected)
    DEFAULT_LAT: 40.7128,
    DEFAULT_LNG: -74.0060,
    DEFAULT_ZOOM: 12
};

// Maps API loader
let googleMapsLoaded = false;
let mapInstances = {};
let geocoders = {};
let searchBoxes = {};
let selectedMarkers = {};
let overlayElements = {};

// Cached component data
const componentData = {};

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeSelectLocation();
    });
} else {
    initializeSelectLocation();
}

/**
 * Initialize the select location components on the page
 */
function initializeSelectLocation() {
    // Find all location components
    const selectLocationContainers = document.querySelectorAll('.select-location-container');
    
    if (selectLocationContainers.length === 0) {
        return;
    }
    
    selectLocationContainers.forEach(container => {
        const componentId = getComponentId(container);
        if (!componentId) return;
        
        setupComponentEvents(componentId);
        
        // Store initial values
        const latInput = document.getElementById(`location-latitude-${componentId}`);
        const lngInput = document.getElementById(`location-longitude-${componentId}`);
        const nameInput = document.getElementById(`location-name-${componentId}`);
        
        if (latInput && lngInput) {
            componentData[componentId] = {
                latitude: latInput.value || '',
                longitude: lngInput.value || '',
                locationName: nameInput ? nameInput.value || '' : ''
            };
        }
    });
    
    // Load Google Maps API asynchronously
    loadGoogleMapsApi();
}

/**
 * Extract component ID from container element
 * @param {HTMLElement} container - The component container element
 * @returns {string|null} - The component ID or null if not found
 */
function getComponentId(container) {
    // Try to find from the button element
    const buttonEl = container.querySelector('button[id^="select-location-button-"]');
    if (buttonEl && buttonEl.id) {
        const match = buttonEl.id.match(/select-location-button-(\w+)/);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    // Try to find from the input element
    const inputEl = container.querySelector('input[id^="location-display-"]');
    if (inputEl && inputEl.id) {
        const match = inputEl.id.match(/location-display-(\w+)/);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    return null;
}

/**
 * Set up event listeners for component elements
 * @param {string} componentId - The component ID
 */
function setupComponentEvents(componentId) {
    const selectButton = document.getElementById(`select-location-button-${componentId}`);
    const closeButton = document.getElementById(`close-map-button-${componentId}`);
    const confirmButton = document.getElementById(`confirm-location-button-${componentId}`);
    const removeButton = document.getElementById(`remove-location-button-${componentId}`);
    const mapContainer = document.getElementById(`map-container-${componentId}`);
    
    if (selectButton) {
        selectButton.addEventListener('click', function() {
            openMap(componentId);
        });
    }
    
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            closeMap(componentId);
        });
    }
    
    if (confirmButton) {
        confirmButton.addEventListener('click', function() {
            confirmLocation(componentId);
        });
    }
    
    if (removeButton) {
        removeButton.addEventListener('click', function() {
            removeLocation(componentId);
        });
    }
    
    // Make location display clickable too
    const locationDisplay = document.getElementById(`location-display-${componentId}`);
    if (locationDisplay) {
        locationDisplay.addEventListener('click', function() {
            openMap(componentId);
        });
    }
}

/**
 * Load Google Maps API script
 */
function loadGoogleMapsApi() {
    if (googleMapsLoaded) {
        return;
    }
    
    // Create a script element to load Google Maps API
    const script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places&callback=googleMapsCallback';
    script.async = true;
    script.defer = true;
    
    document.head.appendChild(script);
    
    // Define global callback for Google Maps API
    window.googleMapsCallback = function() {
        googleMapsLoaded = true;
    };
}

/**
 * Open the map dialog
 * @param {string} componentId - The component ID
 */
function openMap(componentId) {
    const mapContainer = document.getElementById(`map-container-${componentId}`);
    if (!mapContainer) return;
    
    // Create overlay element
    const overlay = document.createElement('div');
    overlay.className = 'map-overlay';
    overlay.id = `map-overlay-${componentId}`;
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeMap(componentId);
        }
    });
    document.body.appendChild(overlay);
    overlayElements[componentId] = overlay;
    
    // Show map container
    mapContainer.style.display = 'flex';
    
    // Initialize map if not already done
    initializeMap(componentId);
    
    // Add body class to prevent scrolling
    document.body.style.overflow = 'hidden';
    
    // Handle escape key
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            closeMap(componentId);
            document.removeEventListener('keydown', escapeHandler);
        }
    });
}

/**
 * Close the map dialog
 * @param {string} componentId - The component ID
 */
function closeMap(componentId) {
    const mapContainer = document.getElementById(`map-container-${componentId}`);
    if (!mapContainer) return;
    
    // Hide map container
    mapContainer.style.display = 'none';
    
    // Remove overlay
    if (overlayElements[componentId]) {
        overlayElements[componentId].remove();
        delete overlayElements[componentId];
    }
    
    // Restore body scrolling
    document.body.style.overflow = '';
}

/**
 * Initialize the Google Map
 * @param {string} componentId - The component ID
 */
function initializeMap(componentId) {
    if (!googleMapsLoaded) {
        setTimeout(() => initializeMap(componentId), 100);
        return;
    }
    
    const mapView = document.getElementById(`map-view-${componentId}`);
    if (!mapView || mapInstances[componentId]) return;
    
    // Get stored coordinates or use defaults
    let latitude = parseFloat(componentData[componentId]?.latitude || SELECT_LOCATION_CONFIG.DEFAULT_LAT);
    let longitude = parseFloat(componentData[componentId]?.longitude || SELECT_LOCATION_CONFIG.DEFAULT_LNG);
    
    // Create map
    const map = new google.maps.Map(mapView, {
        center: { lat: latitude, lng: longitude },
        zoom: SELECT_LOCATION_CONFIG.DEFAULT_ZOOM,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false
    });
    mapInstances[componentId] = map;
    
    // Create geocoder instance
    geocoders[componentId] = new google.maps.Geocoder();
    
    // Create search box
    const searchInput = document.getElementById(`map-search-input-${componentId}`);
    if (searchInput) {
        const searchBox = new google.maps.places.SearchBox(searchInput);
        searchBoxes[componentId] = searchBox;
        
        // Bias search box results to current map viewport
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });
        
        // Listen for search box selection
        searchBox.addListener('places_changed', function() {
            const places = searchBox.getPlaces();
            if (places.length === 0) return;
            
            const place = places[0];
            if (!place.geometry || !place.geometry.location) return;
            
            // Center map on search result
            map.setCenter(place.geometry.location);
            map.setZoom(15);
            
            // Update marker position
            if (selectedMarkers[componentId]) {
                selectedMarkers[componentId].setPosition(place.geometry.location);
            } else {
                addMarker(componentId, place.geometry.location);
            }
        });
    }
    
    // If coordinates are already set, add a marker
    if (isValidCoordinate(latitude, longitude)) {
        const position = { lat: latitude, lng: longitude };
        addMarker(componentId, position);
        map.setCenter(position);
        map.setZoom(15);
    }
    
    // Click listener to set marker
    map.addListener('click', function(e) {
        const position = e.latLng;
        
        // Update or create marker
        if (selectedMarkers[componentId]) {
            selectedMarkers[componentId].setPosition(position);
        } else {
            addMarker(componentId, position);
        }
        
        // Reverse geocode to get address
        reverseGeocode(position, componentId);
    });
}

/**
 * Add a marker to the map
 * @param {string} componentId - The component ID
 * @param {google.maps.LatLng} position - The marker position
 */
function addMarker(componentId, position) {
    if (!mapInstances[componentId]) return;
    
    // Remove existing marker if any
    if (selectedMarkers[componentId]) {
        selectedMarkers[componentId].setMap(null);
    }
    
    // Create new marker
    const marker = new google.maps.Marker({
        position: position,
        map: mapInstances[componentId],
        animation: google.maps.Animation.DROP,
        draggable: true
    });
    selectedMarkers[componentId] = marker;
    
    // Add drag end listener to update location
    marker.addListener('dragend', function() {
        const newPosition = marker.getPosition();
        reverseGeocode(newPosition, componentId);
    });
    
    // Center map on marker
    mapInstances[componentId].panTo(position);
}

/**
 * Reverse geocode coordinates to address
 * @param {google.maps.LatLng} position - The position to geocode
 * @param {string} componentId - The component ID
 */
function reverseGeocode(position, componentId) {
    if (!geocoders[componentId]) return;
    
    const latitude = position.lat();
    const longitude = position.lng();
    
    // Update temporary component data
    componentData[componentId] = {
        ...componentData[componentId],
        latitude: latitude,
        longitude: longitude,
        locationName: ''
    };
    
    // Call our secure proxy endpoint
    fetch(`${SELECT_LOCATION_CONFIG.GEOCODING_URL}?lat=${latitude}&lng=${longitude}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Proxy error:', data.error);
                return;
            }
            
            if (data && data.status === 'OK' && data.results && data.results.length > 0) {
                // Format the location name
                const locationName = formatLocationDisplay(data);
                
                // Update component data
                componentData[componentId].locationName = locationName;
                
                // Update search input with the address
                const searchInput = document.getElementById(`map-search-input-${componentId}`);
                if (searchInput) {
                    searchInput.value = locationName;
                }
            }
        })
        .catch(error => {
            console.error('Error in reverse geocoding:', error);
        });
}

/**
 * Confirm the selected location
 * @param {string} componentId - The component ID
 */
function confirmLocation(componentId) {
    if (!componentData[componentId] || 
        !isValidCoordinate(componentData[componentId].latitude, componentData[componentId].longitude)) {
        return;
    }
    
    // Update hidden input values
    const latInput = document.getElementById(`location-latitude-${componentId}`);
    const lngInput = document.getElementById(`location-longitude-${componentId}`);
    const nameInput = document.getElementById(`location-name-${componentId}`);
    const displayInput = document.getElementById(`location-display-${componentId}`);
    
    if (latInput) latInput.value = componentData[componentId].latitude;
    if (lngInput) lngInput.value = componentData[componentId].longitude;
    if (nameInput) nameInput.value = componentData[componentId].locationName;
    if (displayInput) displayInput.value = componentData[componentId].locationName;
    
    // Close the map
    closeMap(componentId);
    
    // Trigger change event on inputs
    [latInput, lngInput, nameInput].forEach(input => {
        if (input) {
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
        }
    });
}

/**
 * Remove the location
 * @param {string} componentId - The component ID
 */
function removeLocation(componentId) {
    // Clear hidden input values
    const latInput = document.getElementById(`location-latitude-${componentId}`);
    const lngInput = document.getElementById(`location-longitude-${componentId}`);
    const nameInput = document.getElementById(`location-name-${componentId}`);
    const displayInput = document.getElementById(`location-display-${componentId}`);
    
    if (latInput) latInput.value = '';
    if (lngInput) lngInput.value = '';
    if (nameInput) nameInput.value = '';
    if (displayInput) {
        displayInput.value = '';
        displayInput.placeholder = 'No location selected';
    }
    
    // Reset component data
    componentData[componentId] = {
        latitude: '',
        longitude: '',
        locationName: ''
    };
    
    // Remove marker
    if (selectedMarkers[componentId]) {
        selectedMarkers[componentId].setMap(null);
        delete selectedMarkers[componentId];
    }
    
    // Reset search input
    const searchInput = document.getElementById(`map-search-input-${componentId}`);
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Close the map
    closeMap(componentId);
    
    // Trigger change event on inputs
    [latInput, lngInput, nameInput].forEach(input => {
        if (input) {
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
        }
    });
}

/**
 * Check if provided coordinates are valid numbers
 * @param {string|number} longitude - The longitude value
 * @param {string|number} latitude - The latitude value
 * @returns {boolean} - True if coordinates are valid
 */
function isValidCoordinate(latitude, longitude) {
    const lat = parseFloat(latitude);
    const lng = parseFloat(longitude);
    
    return !isNaN(lat) && !isNaN(lng) && 
           lng >= -180 && lng <= 180 && 
           lat >= -90 && lat <= 90;
}

/**
 * Format location display from Google Maps API response
 * @param {Object} data - The Google Maps geocoding data
 * @returns {string} - Formatted location string
 */
function formatLocationDisplay(data) {
    // Start with an empty result
    let result = '';
    
    // Get the first result which is usually the most accurate
    const firstResult = data.results[0];
    
    if (firstResult && firstResult.address_components) {
        // Google Maps returns address components with types array
        // Extract the components we need for a friendly display
        const locality = findAddressComponent(firstResult.address_components, 'locality');
        const sublocality = findAddressComponent(firstResult.address_components, 'sublocality');
        const administrativeArea = findAddressComponent(firstResult.address_components, 'administrative_area_level_1');
        const country = findAddressComponent(firstResult.address_components, 'country');
        
        // Build the location string, starting with the most specific
        if (locality) {
            result = locality;
        } else if (sublocality) {
            result = sublocality;
        }
        
        // Add administrative area (state/province)
        if (administrativeArea) {
            result = result ? `${result}, ${administrativeArea}` : administrativeArea;
        }
        
        // Add country
        if (country) {
            result = result ? `${result}, ${country}` : country;
        }
    }
    
    // If we still couldn't format the address, use formatted_address as fallback
    if (!result && firstResult && firstResult.formatted_address) {
        // Take just part of the formatted address to keep it short
        const parts = firstResult.formatted_address.split(',');
        result = parts.slice(0, 3).join(',');
    }
    
    return result || 'Unknown location';
}

/**
 * Find a specific address component by type from Google Maps API response
 * @param {Array} components - Array of address components
 * @param {string} type - The type of component to find
 * @returns {string|null} - The long_name of the component or null if not found
 */
function findAddressComponent(components, type) {
    const component = components.find(comp => comp.types.includes(type));
    return component ? component.long_name : null;
}
