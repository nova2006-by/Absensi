import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapElement = document.getElementById('peta-lokasi-sekolah');
const latitudeInput = document.getElementById('latitude');
const longitudeInput = document.getElementById('longitude');
const radiusInput = document.getElementById('radius');
const initial = window.LOKASI_SEKOLAH;

if (mapElement && latitudeInput && longitudeInput && radiusInput && initial) {
    const latitude = Number(initial.latitude);
    const longitude = Number(initial.longitude);
    const hasLocation = initial.latitude !== null
        && initial.latitude !== ''
        && initial.longitude !== null
        && initial.longitude !== ''
        && Number.isFinite(latitude)
        && Number.isFinite(longitude);
    const map = L.map(mapElement).setView(
        hasLocation ? [latitude, longitude] : [-2.5, 118],
        hasLocation ? 17 : 5,
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    let marker = null;
    let circle = null;

    const updateLocation = (lat, lng) => {
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);

        const point = [lat, lng];
        if (!marker) {
            marker = L.marker(point, { draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const moved = marker.getLatLng();
                latitudeInput.value = moved.lat.toFixed(7);
                longitudeInput.value = moved.lng.toFixed(7);
                updateCircle();
            });
        } else {
            marker.setLatLng(point);
        }

        updateCircle();
    };

    const updateCircle = () => {
        if (!marker) {
            return;
        }

        const radius = Number(radiusInput.value);
        if (!Number.isFinite(radius) || radius < 1) {
            return;
        }

        if (!circle) {
            circle = L.circle(marker.getLatLng(), {
                radius,
                color: '#059669',
                fillColor: '#10b981',
                fillOpacity: 0.15,
            }).addTo(map);
        } else {
            circle.setLatLng(marker.getLatLng()).setRadius(radius);
        }
    };

    map.on('click', (event) => updateLocation(event.latlng.lat, event.latlng.lng));
    radiusInput.addEventListener('input', updateCircle);

    const updateMapFromInputs = () => {
        const lat = Number(latitudeInput.value);
        const lng = Number(longitudeInput.value);
        if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            updateLocation(lat, lng);
            map.panTo([lat, lng]);
        }
    };

    latitudeInput.addEventListener('change', updateMapFromInputs);
    longitudeInput.addEventListener('change', updateMapFromInputs);

    if (hasLocation) {
        updateLocation(latitude, longitude);
    }

    window.setTimeout(() => map.invalidateSize(), 0);
}
