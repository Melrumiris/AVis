/**
 * homeDom.js — Unified dashboard controller (Phase 2)
 *
 * Manages: Leaflet map, multi-chart statistics, and data table.
 * Includes a dynamic filter query builder.
 */
document.addEventListener('DOMContentLoaded', () => {

    // ─── Filter Definitions & Dynamic UI ─────────────────────────

    const FILTER_DEFINITIONS = {
        severity: { label: 'Severity', type: 'select', options: [
            {value: 'ALL', text: 'All'}, {value: '1', text: '1 — Minor'}, {value: '2', text: '2 — Moderate'}, {value: '3', text: '3 — Severe'}, {value: '4', text: '4 — Fatal'}
        ]},
        region: { label: 'Region', type: 'select', options: [
            {value: 'ALL', text: 'All'}, {value: 'NE', text: 'North-East'}, {value: 'NW', text: 'North-West'}, {value: 'SE', text: 'South-East'}, {value: 'SW', text: 'South-West'}
        ]},
        city: { label: 'City', type: 'text' },
        county: { label: 'County', type: 'text' },
        weather_condition: { label: 'Weather', type: 'text' },
        temperature: { label: 'Temperature (°F)', type: 'number' },
        visibility: { label: 'Visibility (mi)', type: 'number' },
        crossing: { label: 'Crossing', type: 'select', options: [{value: '', text: 'Any'}, {value: 'true', text: 'Yes'}, {value: 'false', text: 'No'}] },
        junction: { label: 'Junction', type: 'select', options: [{value: '', text: 'Any'}, {value: 'true', text: 'Yes'}, {value: 'false', text: 'No'}] },
        traffic_signal: { label: 'Traffic Signal', type: 'select', options: [{value: '', text: 'Any'}, {value: 'true', text: 'Yes'}, {value: 'false', text: 'No'}] },
        sunrise_sunset: { label: 'Day/Night', type: 'select', options: [{value: '', text: 'Any'}, {value: 'Day', text: 'Day'}, {value: 'Night', text: 'Night'}] }
    };

    const activeFilters = new Set();
    const selectAdd = document.getElementById('filter-add-select');
    const dynamicContainer = document.getElementById('dynamic-filters');

    // Populate select
    Object.entries(FILTER_DEFINITIONS).forEach(([key, def]) => {
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = def.label;
        selectAdd.appendChild(opt);
    });

    const addFilterUI = (key, initialValue = '') => {
        if (activeFilters.has(key)) return;
        activeFilters.add(key);

        const def = FILTER_DEFINITIONS[key];
        const wrapper = document.createElement('div');
        wrapper.className = 'form-group';
        wrapper.style.cssText = 'flex: 0 1 auto; margin-bottom: 0; min-width: 150px;';
        wrapper.dataset.key = key;

        let inputHtml = '';
        if (def.type === 'select') {
            const opts = def.options.map(o => `<option value="${o.value}" ${o.value === initialValue ? 'selected' : ''}>${o.text}</option>`).join('');
            inputHtml = `<select class="form-control filter-input" data-key="${key}">${opts}</select>`;
        } else {
            inputHtml = `<input type="${def.type}" class="form-control filter-input" data-key="${key}" value="${initialValue}">`;
        }

        wrapper.innerHTML = `
            <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span>${def.label}</span>
                <button type="button" class="btn-remove-filter" aria-label="Remove" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem; line-height: 1; padding: 0 0 0 0.5rem;">&times;</button>
            </label>
            ${inputHtml}
        `;

        wrapper.querySelector('.btn-remove-filter').addEventListener('click', () => {
            wrapper.remove();
            activeFilters.delete(key);
            updateSelectOptions();
        });
        
        wrapper.querySelector('.btn-remove-filter').addEventListener('mouseover', function() { this.style.color = 'var(--color-error)'; });
        wrapper.querySelector('.btn-remove-filter').addEventListener('mouseout', function() { this.style.color = 'var(--text-muted)'; });

        const addBlock = selectAdd.closest('.form-group');
        addBlock.parentNode.insertBefore(wrapper, addBlock);
        updateSelectOptions();
    };

    const updateSelectOptions = () => {
        Array.from(selectAdd.options).forEach(opt => {
            if (opt.value === '') return;
            opt.disabled = activeFilters.has(opt.value);
        });
        selectAdd.value = '';
    };

    document.getElementById('btn-add-filter').addEventListener('click', () => {
        const key = selectAdd.value;
        if (key) addFilterUI(key);
    });

    const collectFilters = () => {
        const filters = {
            sdate: document.getElementById('dash-sdate').value,
            fdate: document.getElementById('dash-fdate').value
        };
        document.querySelectorAll('.filter-input').forEach(input => {
            if (input.value !== '') {
                filters[input.dataset.key] = input.value;
            }
        });
        return filters;
    };


    // ─── Map Setup ───────────────────────────────────────────────
    // Fix missing Leaflet marker icons by explicitly setting CDN paths
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    let initialLat = 37.0902, initialLng = -95.7129, initialZoom = 4;
    try {
        const storedView = JSON.parse(localStorage.getItem('avis_map_view'));
        if (storedView) { initialLat = storedView.lat; initialLng = storedView.lng; initialZoom = storedView.zoom; }
    } catch (e) {}

    const map = L.map('map-container').setView([initialLat, initialLng], initialZoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
    const markerGroup = L.layerGroup().addTo(map);

    map.on('moveend', () => {
        const center = map.getCenter();
        localStorage.setItem('avis_map_view', JSON.stringify({ lat: center.lat, lng: center.lng, zoom: map.getZoom() }));
    });

    // Invalidate map size on viewport resize (critical for mobile responsiveness)
    let mapResizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(mapResizeTimeout);
        mapResizeTimeout = setTimeout(() => map.invalidateSize(), 200);
    });
    // Also observe the container itself for layout-driven size changes
    if (typeof ResizeObserver !== 'undefined') {
        new ResizeObserver(() => map.invalidateSize()).observe(document.getElementById('map-container'));
    }

    document.getElementById('btn-toggle-map').addEventListener('click', () => {
        const btn = document.getElementById('btn-toggle-map');
        const pMap = document.getElementById('panel-map');
        const pStats = document.getElementById('panel-stats');
        pMap.classList.toggle('map-minimized');
        pStats.classList.toggle('expanded');
        btn.textContent = pMap.classList.contains('map-minimized') ? '⊞ Maximize' : '⊟ Minimize';
        setTimeout(() => map.invalidateSize(), 300);
    });

    const loadMapPoints = async (filters) => {
        markerGroup.clearLayers();
        try {
            const result = await MapApi.getPoints(filters);
            if (!result.success) return;
            result.data.forEach(p => {
                if (p.lat && p.lng) {
                    L.marker([p.lat, p.lng]).bindPopup(`<b>Accident</b><br>Severity: ${p.severity}`).addTo(markerGroup);
                }
            });
        } catch (err) { console.error('Failed to load map points:', err); }
    };

    // ─── Chart Setup ─────────────────────────────────────────────
    
    // Instead of re-fetching from API for each chart, we aggregate loadedData client-side.
    // loadedData is fetched once by ReportApi.
    const chartsMap = new Map(); // id -> Chart instance
    let chartCounter = 0;

    const aggregateData = (data, groupBy) => {
        const counts = {};
        data.forEach(r => {
            let key = 'Unknown';
            switch(groupBy) {
                case 'severity': key = r.severity; break;
                case 'year': key = r.date_time.substring(0,4); break;
                case 'month': key = r.date_time.substring(5,7); break;
                case 'city': key = r.city || 'Unknown'; break;
                case 'county': key = r.county || 'Unknown'; break;
                case 'weather_condition': key = r.weather_condition || 'Unknown'; break;
                case 'sunrise_sunset': key = r.sunrise_sunset || 'Unknown'; break;
                case 'location':
                    const lat = parseFloat(r.latitude), lng = parseFloat(r.longitude);
                    if (lat >= 39.8 && lng >= -98.5) key = 'North-East';
                    else if (lat >= 39.8 && lng < -98.5) key = 'North-West';
                    else if (lat < 39.8 && lng >= -98.5) key = 'South-East';
                    else if (lat < 39.8 && lng < -98.5) key = 'South-West';
                    break;
                case 'day':
                    const d = new Date(r.date_time).getDay(); // 0=Sun, 1=Mon...
                    key = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d];
                    break;
            }
            counts[key] = (counts[key] || 0) + 1;
        });

        // Sort descending by value, limit to 25 like backend does
        return Object.entries(counts)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 25)
            .map(([label, total]) => ({ label: String(label), total }));
    };

    const redrawChart = (id) => {
        const chartObj = chartsMap.get(id);
        if (!chartObj || !loadedData || loadedData.length === 0) return;
        
        const agg = aggregateData(loadedData, chartObj.groupBy);
        chartObj.chart.data.labels = agg.map(a => a.label);
        chartObj.chart.data.datasets[0].data = agg.map(a => a.total);
        chartObj.chart.update();
    };

    const redrawAllCharts = () => {
        chartsMap.forEach((_, id) => redrawChart(id));
    };

    const createChartInstance = (id, type) => {
        const isPie = type === 'pie' || type === 'doughnut';
        const colorPalette = [
            'rgba(226, 88, 34, 0.7)', 'rgba(15, 23, 42, 0.7)', 'rgba(16, 185, 129, 0.7)', 
            'rgba(239, 68, 68, 0.7)', 'rgba(59, 130, 246, 0.7)', 'rgba(245, 158, 11, 0.7)'
        ];
        const chartIndex = Math.max(0, chartCounter - 1) % colorPalette.length;
        const color = isPie ? colorPalette : colorPalette[chartIndex];
        const borderColor = isPie ? colorPalette.map(c => c.replace('0.7', '1')) : colorPalette[chartIndex].replace('0.7', '1');

        const ctx = document.getElementById(id).getContext('2d');
        return new Chart(ctx, {
            type: type,
            data: {
                labels: [],
                datasets: [{
                    label: 'Accident Count',
                    data: [],
                    backgroundColor: color,
                    borderColor: borderColor,
                    borderWidth: type === 'line' ? 3 : 1,
                    pointRadius: type === 'line' ? 0 : 3,
                    fill: false
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: type !== 'bar' && type !== 'line', position: 'top' } }
            }
        });
    };

    const addChart = (initialType = 'bar', initialGroupBy = 'severity') => {
        const id = `chart-${++chartCounter}`;
        const container = document.createElement('div');
        container.className = 'chart-card island-card';
        container.id = `card-${id}`;
        container.style.cssText = 'padding: var(--spacing-md);';
        
        const groupByOptions = `
            <option value="severity" ${initialGroupBy==='severity'?'selected':''}>Severity</option>
            <option value="year" ${initialGroupBy==='year'?'selected':''}>Year</option>
            <option value="month" ${initialGroupBy==='month'?'selected':''}>Month</option>
            <option value="day" ${initialGroupBy==='day'?'selected':''}>Day of Week</option>
            <option value="location" ${initialGroupBy==='location'?'selected':''}>Location</option>
            <option value="city" ${initialGroupBy==='city'?'selected':''}>City</option>
            <option value="county" ${initialGroupBy==='county'?'selected':''}>County</option>
            <option value="weather_condition" ${initialGroupBy==='weather_condition'?'selected':''}>Weather</option>
            <option value="sunrise_sunset" ${initialGroupBy==='sunrise_sunset'?'selected':''}>Day/Night</option>
        `;
        
        const typeOptions = `
            <option value="bar" ${initialType==='bar'?'selected':''}>Bar</option>
            <option value="line" ${initialType==='line'?'selected':''}>Line</option>
            <option value="pie" ${initialType==='pie'?'selected':''}>Pie</option>
            <option value="doughnut" ${initialType==='doughnut'?'selected':''}>Doughnut</option>
        `;

        container.innerHTML = `
            <div style="display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-md); flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 100px;">
                    <label class="form-label" style="font-size: 0.75rem;">Group By</label>
                    <select class="form-control chart-groupby" style="font-size: 0.85rem; padding: 0.2rem 0.5rem;">${groupByOptions}</select>
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 100px;">
                    <label class="form-label" style="font-size: 0.75rem;">Type</label>
                    <select class="form-control chart-type" style="font-size: 0.85rem; padding: 0.2rem 0.5rem;">${typeOptions}</select>
                </div>
                <button class="btn btn-secondary chart-close-btn" data-id="${id}" style="padding: 0.2rem 0.6rem; font-size: 1.2rem; line-height: 1;">&times;</button>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="${id}"></canvas>
            </div>
        `;
        document.getElementById('charts-container').appendChild(container);

        container.querySelector('.chart-close-btn').addEventListener('click', (e) => {
            const cid = e.target.dataset.id;
            chartsMap.get(cid).chart.destroy();
            chartsMap.delete(cid);
            document.getElementById(`card-${cid}`).remove();
            if (typeof updateExportSelect === 'function') updateExportSelect();
        });

        const selectGroupBy = container.querySelector('.chart-groupby');
        const selectType = container.querySelector('.chart-type');

        selectGroupBy.addEventListener('change', (e) => {
            const chartObj = chartsMap.get(id);
            chartObj.groupBy = e.target.value;
            redrawChart(id);
        });

        selectType.addEventListener('change', (e) => {
            const chartObj = chartsMap.get(id);
            chartObj.type = e.target.value;
            chartObj.chart.destroy();
            chartObj.chart = createChartInstance(id, chartObj.type);
            redrawChart(id);
        });

        chartsMap.set(id, { chart: createChartInstance(id, initialType), type: initialType, groupBy: initialGroupBy });
        
        if (typeof updateExportSelect === 'function') updateExportSelect();
        
        if (loadedData && loadedData.length > 0) {
            redrawChart(id);
        }
    };

    document.getElementById('btn-add-chart').addEventListener('click', () => {
        addChart('bar', 'severity');
    });

    // ─── Data Table & Export ─────────────────────────────────────
    let loadedData = [];

    const toggleExportButtons = (enabled) => {
        document.getElementById('btn-export-csv').disabled  = !enabled;
        document.getElementById('btn-export-json').disabled = !enabled;
        const hasCharts = chartsMap.size > 0;
        document.getElementById('btn-export-svg').disabled  = !enabled || !hasCharts;
        document.getElementById('btn-export-webp').disabled = !enabled || !hasCharts;
        const select = document.getElementById('export-chart-select');
        if (select) select.disabled = !hasCharts;
        updateExportSelect();
    };

    const updateExportSelect = () => {
        const select = document.getElementById('export-chart-select');
        if (!select) return;
        select.innerHTML = '';
        if (chartsMap.size === 0) {
            select.innerHTML = '<option value="">No charts available</option>';
            return;
        }
        let i = 1;
        chartsMap.forEach((_, id) => {
            select.innerHTML += `<option value="${id}">Chart ${i++}</option>`;
        });
    };

    const triggerDownload = (url, filename) => {
        const a = document.createElement('a'); a.href = url; a.download = filename; a.click();
    };

    const loadReportData = async (filters) => {
        try {
            const result = await ReportApi.getData(filters);
            if (!result.success) { alert(`Error: ${result.error}`); return; }

            loadedData = result.data;
            redrawAllCharts();

            const table = document.getElementById('report-table');
            const tbody = document.getElementById('report-table-body');

            if (loadedData.length === 0) {
                table.style.display = 'none';
                toggleExportButtons(false);
                return;
            }

            tbody.innerHTML = loadedData.map(r => `<tr>
                <td>${r.date_time}</td><td>${r.severity}</td><td>${r.latitude}</td><td>${r.longitude}</td>
                <td>${r.state || ''}</td><td>${r.city || ''}</td><td>${r.county || ''}</td>
                <td>${r.weather_condition || ''}</td><td>${r.temperature !== null ? r.temperature : ''}</td>
                <td>${r.visibility !== null ? r.visibility : ''}</td>
                <td>${r.crossing === true ? 'Yes' : r.crossing === false ? 'No' : ''}</td>
                <td>${r.junction === true ? 'Yes' : r.junction === false ? 'No' : ''}</td>
                <td>${r.traffic_signal === true ? 'Yes' : r.traffic_signal === false ? 'No' : ''}</td>
                <td>${r.sunrise_sunset || ''}</td>
            </tr>`).join('');

            table.style.display = 'table';
            toggleExportButtons(true);
        } catch (err) { console.error('Failed to load report data:', err); }
    };

    // Export handlers
    document.getElementById('btn-export-csv').addEventListener('click', () => {
        const params = new URLSearchParams(collectFilters());
        params.set('format', 'csv');
        window.location.href = `/api/v0/report/file?${params}`;
    });

    document.getElementById('btn-export-json').addEventListener('click', () => {
        const params = new URLSearchParams(collectFilters());
        params.set('format', 'json');
        window.location.href = `/api/v0/report/file?${params}`;
    });

    const exportChart = (mimeType, ext) => {
        if (chartsMap.size === 0) return;
        const select = document.getElementById('export-chart-select');
        const targetId = select && select.value ? select.value : chartsMap.keys().next().value;
        const canvas = document.getElementById(targetId);
        triggerDownload(canvas.toDataURL(mimeType, 1.0), `accident_chart.${ext}`);
    };

    // Note: Chart.js doesn't natively export SVG via toDataURL. WebP/PNG is natively supported.
    // We'll export the canvas image for both buttons to satisfy the interface until SVG renderer is added.
    document.getElementById('btn-export-svg').addEventListener('click', () => exportChart('image/png', 'png'));
    document.getElementById('btn-export-webp').addEventListener('click', () => exportChart('image/webp', 'webp'));


    // ─── Apply Filters (shared) ──────────────────────────────────
    const applyFilters = () => {
        const filters = collectFilters();
        localStorage.setItem('avis_dashboard_filters', JSON.stringify(filters));
        
        loadMapPoints(filters);
        loadReportData(filters); // loadedData update will trigger redrawAllCharts
    };
    document.getElementById('btn-apply-filters').addEventListener('click', applyFilters);

    // ─── AI Query Mode ───────────────────────────────────────────
    const btnToggleAi = document.getElementById('btn-toggle-ai');
    const filtersContainer = document.getElementById('dynamic-filters-container');
    const aiContainer = document.getElementById('ai-query-container');
    let isAiMode = false;

    if (btnToggleAi) {
        btnToggleAi.addEventListener('click', () => {
            isAiMode = !isAiMode;
            if (isAiMode) {
                filtersContainer.style.display = 'none';
                aiContainer.style.display = 'flex';
                btnToggleAi.textContent = '⚙️ Standard Mode';
            } else {
                filtersContainer.style.display = 'flex';
                aiContainer.style.display = 'none';
                btnToggleAi.textContent = '✨ AI Mode';
            }
        });
    }

    const btnGenerateAi = document.getElementById('btn-generate-ai');
    const aiInput = document.getElementById('ai-query-input');
    const aiMessage = document.getElementById('ai-query-message');

    if (btnGenerateAi) {
        btnGenerateAi.addEventListener('click', async () => {
            const prompt = aiInput.value.trim();
            if (!prompt) return;

            aiMessage.className = 'form-message';
            aiMessage.textContent = 'Generating and executing SQL...';
            btnGenerateAi.disabled = true;

            try {
                const result = await ApiHandler.request('/api/v0/accidents/ask', {
                    method: 'QUERY',
                    headers: {
                        'Content-Type': 'text/plain'
                    },
                    body: prompt
                });

                if (!result.success) {
                    throw new Error(result.error || 'Failed to process AI query');
                }

                aiMessage.className = 'form-message success';
                aiMessage.textContent = `Generated SQL: ${result.query}`;
                
                loadedData = result.data;
                redrawAllCharts();
                
                const table = document.getElementById('report-table');
                const tbody = document.getElementById('report-table-body');
                
                if (loadedData.length === 0) {
                    table.style.display = 'none';
                    toggleExportButtons(false);
                } else {
                    tbody.innerHTML = loadedData.map(r => `<tr>
                        <td>${r.date_time || ''}</td><td>${r.severity || ''}</td><td>${r.latitude || ''}</td><td>${r.longitude || ''}</td>
                        <td>${r.state || ''}</td><td>${r.city || ''}</td><td>${r.county || ''}</td>
                        <td>${r.weather_condition || ''}</td><td>${r.temperature !== null && r.temperature !== undefined ? r.temperature : ''}</td>
                        <td>${r.visibility !== null && r.visibility !== undefined ? r.visibility : ''}</td>
                        <td>${r.crossing === true ? 'Yes' : r.crossing === false ? 'No' : ''}</td>
                        <td>${r.junction === true ? 'Yes' : r.junction === false ? 'No' : ''}</td>
                        <td>${r.traffic_signal === true ? 'Yes' : r.traffic_signal === false ? 'No' : ''}</td>
                        <td>${r.sunrise_sunset || ''}</td>
                    </tr>`).join('');
                    table.style.display = 'table';
                    toggleExportButtons(true);
                }

                markerGroup.clearLayers();
                loadedData.forEach(p => {
                    if (p.latitude && p.longitude) {
                        L.marker([p.latitude, p.longitude]).bindPopup(`<b>Accident</b><br>Severity: ${p.severity}`).addTo(markerGroup);
                    }
                });

            } catch (err) {
                aiMessage.className = 'form-message error';
                aiMessage.textContent = err.message;
            } finally {
                btnGenerateAi.disabled = false;
            }
        });
    }

    // ─── Initial Load ────────────────────────────────────────────
    try {
        const stored = JSON.parse(localStorage.getItem('avis_dashboard_filters'));
        if (stored) {
            if (stored.sdate) document.getElementById('dash-sdate').value = stored.sdate;
            if (stored.fdate) document.getElementById('dash-fdate').value = stored.fdate;
            
            Object.keys(stored).forEach(key => {
                if (key !== 'sdate' && key !== 'fdate' && FILTER_DEFINITIONS[key]) {
                    addFilterUI(key, stored[key]);
                }
            });
        }
    } catch (e) {}
    
    // Always add severity and region initially if not present
    if (!activeFilters.has('severity')) addFilterUI('severity', 'ALL');
    if (!activeFilters.has('region')) addFilterUI('region', 'ALL');

    // Default first chart
    addChart('bar', 'severity');
    
    applyFilters();
});
