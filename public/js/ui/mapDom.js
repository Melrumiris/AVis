document.addEventListener('DOMContentLoaded', () => {
    const map = L.map('map-container').setView([37.0902, -95.7129], 4);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const markerGroup = L.layerGroup().addTo(map);

    const loadPoints = async (sdate = '', fdate = '') => {
        markerGroup.clearLayers();
        try {
            const result = await MapApi.getPoints(sdate, fdate);
            if (!result.success) return;
            result.data.forEach(point => {
                if (point.lat && point.lng) {
                    L.marker([point.lat, point.lng])
                        .bindPopup(`<b>Accident</b><br>Severity: ${point.severitate}`)
                        .addTo(markerGroup);
                }
            });
        } catch (err) {
            console.error('Failed to load map points:', err);
        }
    };

    loadPoints();

    document.getElementById('btn-filter-map').addEventListener('click', () => {
        const sdate = document.getElementById('map-sdate').value;
        const fdate = document.getElementById('map-fdate').value;
        loadPoints(sdate, fdate);
    });
});
