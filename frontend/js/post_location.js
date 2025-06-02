/**
 * Post Location Component JavaScript
 * Retrieves and displays location information based on coordinates
 */

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
 * Fetch location details using an external API
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
        
        // Use OpenStreetMap's Nominatim API for reverse geocoding
        // This is a free service with usage policy: https://operations.osmfoundation.org/policies/nominatim/
        const apiUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=14&addressdetails=1`;
        
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'User-Agent': 'SocialConnectApp/1.0' // Identify our application as per API usage policy
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.display_name) {
                // Format the location display based on available data
                let locationDisplay = formatLocationDisplay(data);
                setLocationText(postId, locationDisplay, 'success');
            } else {
                setLocationText(postId, 'Location unavailable', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching location:', error);
            setLocationText(postId, 'Could not retrieve location', 'error');
        });
    }
}

/**
 * Format location display from API response
 * @param {Object} data - The location data from API
 * @returns {string} - Formatted location string
 */
function formatLocationDisplay(data) {
    // Start with an empty result
    let result = '';
    
    // Try to get the most relevant parts of the address
    if (data.address) {
        const address = data.address;
        
        // City/town/village level
        if (address.city) {
            result = address.city;
        } else if (address.town) {
            result = address.town;
        } else if (address.village) {
            result = address.village;
        }
        
        // Add state/province if available
        if (address.state || address.province) {
            const region = address.state || address.province;
            result = result ? `${result}, ${region}` : region;
        }
        
        // Add country
        if (address.country) {
            result = result ? `${result}, ${address.country}` : address.country;
        }
    }
    
    // If we couldn't format the address, use the display name as fallback
    // but limit it to avoid too long strings
    if (!result && data.display_name) {
        // Split the display name and take just a few parts to keep it short
        const parts = data.display_name.split(',');
        result = parts.slice(0, 3).join(',');
    }
    
    return result || 'Unknown location';
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
