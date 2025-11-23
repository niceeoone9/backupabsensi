/**
 * Geolocation Functions
 */

let currentLocation = null;

// Get current location
function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation not supported'));
            return;
        }
        
        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                resolve(currentLocation);
            },
            (error) => {
                let message = 'Error getting location';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Permission denied. Please allow location access.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'Location unavailable';
                        break;
                    case error.TIMEOUT:
                        message = 'Location request timeout';
                        break;
                }
                reject(new Error(message));
            },
            options
        );
    });
}

// Calculate distance between two points (Haversine formula)
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Earth radius in meters
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c; // Distance in meters
}

// Check if within office geofence
function checkGeofence(latitude, longitude) {
    if (!CONFIG.SECURITY.REQUIRE_GEOFENCING) {
        return {
            valid: true,
            message: 'Geofencing disabled (Demo mode)',
            office: 'Any location allowed'
        };
    }
    
    for (const office of CONFIG.OFFICE_LOCATIONS) {
        const distance = calculateDistance(
            latitude,
            longitude,
            office.latitude,
            office.longitude
        );
        
        if (distance <= office.radius) {
            return {
                valid: true,
                office: office.name,
                distance: Math.round(distance),
                message: `Dalam area ${office.name}`
            };
        }
    }
    
    return {
        valid: false,
        message: 'Di luar area kantor',
        nearestOffice: CONFIG.OFFICE_LOCATIONS[0]?.name || 'Unknown'
    };
}
