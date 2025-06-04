/**
 * Post Location Component JavaScript
 * Retrieves and displays location information based on coordinates using Google Maps API
 */

// Configuration
const LOCATION_CONFIG = {
    // Using our secure proxy instead of directly calling Google Maps API
    GEOCODING_URL: '/backend/src/api/geocode.php'
};

// Initialize API handler only if not already defined
if (typeof window.locationAPI === 'undefined') {
    // Using the existing APIHandler if available
    if (typeof APIHandler !== 'undefined') {
        window.locationAPI = new APIHandler();
    } else {
        console.error('APIHandler class is required but not available');
    }
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializePostLocation();
    });
} else {
    initializePostLocation();
}

/**
 * Initialize the post location components on the page
 */
function initializePostLocation() {
    // Find all location components
    const locationContainers = document.querySelectorAll('.post-location-container');
    
    locationContainers.forEach(container => {
        const postId = getPostIdFromContainer(container);
        if (postId) {
            const longitudeEl = document.getElementById(`post-longitude-${postId}`);
            const latitudeEl = document.getElementById(`post-latitude-${postId}`);
            
            if (longitudeEl && latitudeEl) {
                const longitude = longitudeEl.value;
                const latitude = latitudeEl.value;
                
                if (isValidCoordinate(longitude, latitude)) {
                    fetchLocationDetails(longitude, latitude, postId);
                } else {
                    setLocationText(postId, 'Invalid location data', 'error');
                }
            }
        }
    });
}

/**
 * Extract post ID from container element or its children
 * @param {HTMLElement} container - The location container element
 * @returns {string|null} - The post ID or null if not found
 */
function getPostIdFromContainer(container) {
    // First try to find from the location text element
    const locationTextEl = container.querySelector('.location-text');
    if (locationTextEl && locationTextEl.id) {
        const match = locationTextEl.id.match(/location-text-(\w+)/);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    // Then try to find from the hidden input elements
    const longitudeEl = container.querySelector('input[id^="post-longitude-"]');
    if (longitudeEl && longitudeEl.id) {
        const match = longitudeEl.id.match(/post-longitude-(\w+)/);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    return null;
}

/**
 * Check if provided coordinates are valid numbers
 * @param {string|number} longitude - The longitude value
 * @param {string|number} latitude - The latitude value
 * @returns {boolean} - True if coordinates are valid
 */
function isValidCoordinate(longitude, latitude) {
    const lon = parseFloat(longitude);
    const lat = parseFloat(latitude);
    
    return !isNaN(lon) && !isNaN(lat) && 
           lon >= -180 && lon <= 180 && 
           lat >= -90 && lat <= 90;
}

/**
 * Fetch location details using Google Maps API
 * @param {string|number} longitude - The longitude value
 * @param {string|number} latitude - The latitude value
 * @param {string} postId - The post ID
 */
function fetchLocationDetails(longitude, latitude, postId) {
    const locationTextEl = document.getElementById(`location-text-${postId}`);
    
    if (locationTextEl) {
        // Add loading class
        locationTextEl.textContent = 'Loading location...';
        locationTextEl.className = 'location-text loading';
        // Use our secure proxy instead of directly calling Google Maps API
        const apiUrl = `${LOCATION_CONFIG.GEOCODING_URL}?lat=${latitude}&lng=${longitude}`;
        
        fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })        .then(data => {
            // Check for error from our proxy
            if (data.error) {
                console.error('Proxy error:', data.error);
                setLocationText(postId, 'Location service error', 'error');
                return;
            }
            
            // Process Google Maps API response
            if (data && data.status === 'OK' && data.results && data.results.length > 0) {
                // Format the location display based on available data
                let locationDisplay = formatLocationDisplay(data);
                setLocationText(postId, locationDisplay, 'success');
            } else {
                // Handle specific Google Maps API error statuses
                let errorMessage = 'Location unavailable';
                
                if (data) {
                    switch(data.status) {
                        case 'ZERO_RESULTS':
                            errorMessage = 'No location found';
                            break;
                        case 'OVER_QUERY_LIMIT':
                            errorMessage = 'Location service temporarily unavailable';
                            console.error('Google Maps API query limit exceeded');
                            break;
                        case 'REQUEST_DENIED':
                            errorMessage = 'Location service error';
                            console.error('Google Maps API request denied. Check your API key.');
                            break;
                        case 'INVALID_REQUEST':
                            errorMessage = 'Invalid location data';
                            break;
                    }
                }
                
                setLocationText(postId, errorMessage, 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching location:', error);
            setLocationText(postId, 'Could not retrieve location', 'error');
        });
    }
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

/**
 * Update the location text with the provided message and class
 * @param {string} postId - The post ID
 * @param {string} text - The text to display
 * @param {string} className - The CSS class to apply (success, error)
 */
function setLocationText(postId, text, className) {
    const locationTextEl = document.getElementById(`location-text-${postId}`);
    
    if (locationTextEl) {
        locationTextEl.textContent = text;
        locationTextEl.className = `location-text ${className || ''}`;
    }
}
