/**
 * homeDom.js — Unified dashboard controller
 *
 * Manages: Leaflet map, Chart.js statistics, and data table/export.
 * All three sections share the same filter bar.
 */
document.addEventListener('DOMContentLoaded', () => {

    // ─── Map Setup ───────────────────────────────────────────────
    let initialLat = 37.0902;
    let initialLng = -95.7129;
    let initialZoom = 4;

    try {
        const storedView = JSON.parse(localStorage.getItem('avis_map_view'));
        if (storedView && storedView.lat && storedView.lng && storedView.zoom) {
            initialLat = storedView.lat;
            initialLng = storedView.lng;
            initialZoom = storedView.zoom;
        }
    } catch (e) {}

    const map = L.map('map-container').setView([initialLat, initialLng], initialZoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const markerGroup = L.layerGroup().addTo(map);

    // Save map position on move
    map.on('moveend', () => {
        const center = map.getCenter();
        const zoom = map.getZoom();
        localStorage.setItem('avis_map_view', JSON.stringify({ lat: center.lat, lng: center.lng, zoom: zoom }));
    });

    // Ask for user location and display it
    let userLocationLayer = L.layerGroup().addTo(map);
    map.on('locationfound', (e) => {
        userLocationLayer.clearLayers();
        const radius = e.accuracy / 2;
        L.marker(e.latlng).addTo(userLocationLayer)
            .bindPopup(`Your location (within ${Math.round(radius)} meters)`);
        L.circle(e.latlng, radius).addTo(userLocationLayer);
    });
    map.locate({ setView: false });

    const loadMapPoints = async (sdate, fdate) => {
        markerGroup.clearLayers();
        try {
            const result = await MapApi.getPoints(sdate, fdate);
            if (!result.success) return;
            result.data.forEach(point => {
                if (point.lat && point.lng) {
                    L.marker([point.lat, point.lng])
                        .bindPopup(`<b>Accident</b><br>Severity: ${point.severity}`)
                        .addTo(markerGroup);
                }
            });
        } catch (err) {
            console.error('Failed to load map points:', err);
        }
    };

    // ─── Chart Setup ─────────────────────────────────────────────
    const ctx = document.getElementById('stats-chart').getContext('2d');
    let statsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Accident Count',
                data: [],
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const loadStatistics = async (sdate, fdate, severity, region, groupBy) => {
        try {
            const result = await StatisticsApi.getData(sdate, fdate, severity, region, groupBy);
            if (!result.success) return;

            statsChart.data.labels = result.data.map(r => r.label);
            statsChart.data.datasets[0].data = result.data.map(r => parseInt(r.total, 10));
            statsChart.update();
        } catch (err) {
            console.error('Failed to load statistics:', err);
        }
    };

    // ─── Data Table & Export ─────────────────────────────────────
    let loadedData = [];

    const toggleExportButtons = (enabled) => {
        document.getElementById('btn-export-csv').disabled  = !enabled;
        document.getElementById('btn-export-svg').disabled  = !enabled;
        document.getElementById('btn-export-webp').disabled = !enabled;
    };

    const triggerDownload = (url, filename) => {
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
    };

    const buildSvg = (rows) => {
        const subset = rows.slice(0, 35);
        const height = 60 + subset.length * 25;
        let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="850" height="${height}" style="background:#fff; font-family:Arial, sans-serif;">`;
        svg += `<text x="20" y="30" font-size="16" font-weight="bold" fill="#333">Accident Report (First ${subset.length} rows)</text>`;
        svg += `<text x="20"  y="55" font-weight="bold" font-size="12" fill="#555">DATE TIME</text>`;
        svg += `<text x="250" y="55" font-weight="bold" font-size="12" fill="#555">SEVERITY</text>`;
        svg += `<text x="400" y="55" font-weight="bold" font-size="12" fill="#555">LATITUDE</text>`;
        svg += `<text x="550" y="55" font-weight="bold" font-size="12" fill="#555">LONGITUDE</text>`;
        svg += `<text x="700" y="55" font-weight="bold" font-size="12" fill="#555">STATE</text>`;
        subset.forEach((r, i) => {
            const y = 80 + i * 25;
            svg += `<text x="20"  y="${y}" font-size="11" fill="#666">${r.date_time}</text>`;
            svg += `<text x="250" y="${y}" font-size="11" fill="#666">${r.severity}</text>`;
            svg += `<text x="400" y="${y}" font-size="11" fill="#666">${r.latitude}</text>`;
            svg += `<text x="550" y="${y}" font-size="11" fill="#666">${r.longitude}</text>`;
            svg += `<text x="700" y="${y}" font-size="11" fill="#666">${r.state || ''}</text>`;
        });
        svg += '</svg>';
        return svg;
    };

    const loadReportData = async (sdate, fdate) => {
        try {
            const result = await ReportApi.getData(sdate, fdate);

            if (!result.success) {
                alert(`Error: ${result.error}`);
                return;
            }

            loadedData = result.data;

            const table = document.getElementById('report-table');
            const tbody = document.getElementById('report-table-body');

            if (loadedData.length === 0) {
                table.style.display = 'none';
                toggleExportButtons(false);
                return;
            }

            tbody.innerHTML = loadedData.map(r => `<tr>
                <td>${r.date_time}</td>
                <td>${r.severity}</td>
                <td>${r.latitude}</td>
                <td>${r.longitude}</td>
                <td>${r.state || ''}</td>
            </tr>`).join('');

            table.style.display = 'table';
            toggleExportButtons(true);
        } catch (err) {
            console.error('Failed to load report data:', err);
        }
    };

    // ─── Export handlers ─────────────────────────────────────────

    document.getElementById('btn-export-csv').addEventListener('click', () => {
        // Server-side CSV download via FileResponder
        const sdate = document.getElementById('dash-sdate').value;
        const fdate = document.getElementById('dash-fdate').value;
        const params = new URLSearchParams({ sdate, fdate });
        window.location.href = `/api/v0/report/file?${params}`;
    });

    document.getElementById('btn-export-svg').addEventListener('click', () => {
        if (!loadedData.length) return;
        const blob = new Blob([buildSvg(loadedData)], { type: 'image/svg+xml;charset=utf-8' });
        triggerDownload(URL.createObjectURL(blob), 'accident_report.svg');
    });

    document.getElementById('btn-export-webp').addEventListener('click', () => {
        if (!loadedData.length) return;
        const blob = new Blob([buildSvg(loadedData)], { type: 'image/svg+xml;charset=utf-8' });
        const url  = URL.createObjectURL(blob);
        const img  = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width  = 850;
            canvas.height = img.height;
            canvas.getContext('2d').drawImage(img, 0, 0);
            URL.revokeObjectURL(url);
            triggerDownload(canvas.toDataURL('image/webp', 1.0), 'accident_report.webp');
        };
        img.src = url;
    });

    // ─── Apply Filters (shared) ──────────────────────────────────

    const applyFilters = () => {
        const sdate    = document.getElementById('dash-sdate').value;
        const fdate    = document.getElementById('dash-fdate').value;
        const severity = document.getElementById('dash-severity').value;
        const region   = document.getElementById('dash-region').value;
        const groupBy  = document.getElementById('dash-group-by').value;

        // Save filters to localStorage
        const filters = { sdate, fdate, severity, region, groupBy };
        localStorage.setItem('avis_dashboard_filters', JSON.stringify(filters));

        loadMapPoints(sdate, fdate);
        loadStatistics(sdate, fdate, severity, region, groupBy);
        loadReportData(sdate, fdate);
    };

    document.getElementById('btn-apply-filters').addEventListener('click', applyFilters);

    // ─── Initial Load ────────────────────────────────────────────
    let initSdate = '', initFdate = '', initSeverity = 'ALL', initRegion = 'ALL', initGroupBy = 'severity';
    try {
        const stored = JSON.parse(localStorage.getItem('avis_dashboard_filters'));
        if (stored) {
            initSdate = stored.sdate || '';
            initFdate = stored.fdate || '';
            initSeverity = stored.severity || 'ALL';
            initRegion = stored.region || 'ALL';
            initGroupBy = stored.groupBy || 'severity';

            document.getElementById('dash-sdate').value = initSdate;
            document.getElementById('dash-fdate').value = initFdate;
            document.getElementById('dash-severity').value = initSeverity;
            document.getElementById('dash-region').value = initRegion;
            document.getElementById('dash-group-by').value = initGroupBy;
        }
    } catch (e) {}

    loadMapPoints(initSdate, initFdate);
    loadStatistics(initSdate, initFdate, initSeverity, initRegion, initGroupBy);
    loadReportData(initSdate, initFdate);
});
