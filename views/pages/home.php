<?php
/**
 * views/pages/home.php
 *
 * Unified dashboard page fragment — injected into views/layouts/main.php.
 * Contains: Map (Leaflet), Statistics (Chart.js), Data Table & Export.
 * All data fetching is done client-side via ApiHandler.js.
 */
?>

<section id="dashboard-page" class="page-section">
    <h1 class="page-title">Dashboard</h1>

    <!-- Global Data Query -->
    <div id="query-bar" class="filter-bar island-card" role="search" aria-label="Global Data Query">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%; margin-bottom: var(--spacing-sm);">
            <div>
                <h3 style="font-size: 1.1rem; margin: 0; color: var(--text-main);">Global Query</h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Configure dataset bounds applied to Map, Charts, and Table.</p>
            </div>
            <button id="btn-toggle-ai" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">✨ AI Mode</button>
        </div>

        <div id="dynamic-filters-container" style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); width: 100%;">
            <div class="form-group" style="flex: 0 1 auto; margin-bottom: 0;">
                <label for="dash-sdate" class="form-label">From</label>
                <input type="date" id="dash-sdate" name="sdate" class="form-control">
            </div>

            <div class="form-group" style="flex: 0 1 auto; margin-bottom: 0;">
                <label for="dash-fdate" class="form-label">To</label>
                <input type="date" id="dash-fdate" name="fdate" class="form-control">
            </div>

            <!-- Dynamic filters will be injected here before the Add Filter select -->

            <div class="form-group" style="flex: 0 1 auto; margin-bottom: 0; margin-right: auto;">
                <label for="filter-add-select" class="form-label">Add Filter</label>
                <div style="display: flex; gap: var(--spacing-sm);">
                    <select id="filter-add-select" class="form-control" style="width: 160px;">
                        <option value="" disabled selected>Select column...</option>
                    </select>
                    <button id="btn-add-filter" class="btn btn-secondary">+</button>
                </div>
            </div>

            <div class="form-group" style="flex: 0 1 auto; margin-bottom: 0; align-self: flex-end;">
                <button id="btn-apply-filters" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>

        <!-- AI Query Container -->
        <div id="ai-query-container" style="display: none; width: 100%; flex-direction: column; gap: var(--spacing-sm); margin-top: var(--spacing-md);">
            <label for="ai-query-input" class="form-label" style="font-size: 0.95rem; font-weight: 500;">Ask in plain English (e.g. "Show me severe accidents in California during rain"):</label>
            <div style="display: flex; gap: var(--spacing-md);">
                <input type="text" id="ai-query-input" class="form-control" style="flex: 1;" placeholder="Describe what data you want to see...">
                <button id="btn-generate-ai" class="btn btn-primary">Generate Data</button>
            </div>
            <div id="ai-query-message" class="form-message"></div>
        </div>
    </div>

    <!-- Dashboard grid -->
    <div id="dashboard-grid" class="dashboard-grid" style="margin-top: var(--spacing-xl);">

        <!-- Map Panel -->
        <div class="dashboard-panel island-card" id="panel-map">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-sm);">
                <h2 class="panel-title" style="border: none; margin: 0; padding: 0;">Accident Map</h2>
                <button id="btn-toggle-map" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.85rem;">⊟ Minimize</button>
            </div>
            <div id="map-container" role="region" aria-label="Accident map"></div>
        </div>

        <!-- Statistics Panel -->
        <div class="dashboard-panel island-card" id="panel-stats">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-sm);">
                <h2 class="panel-title" style="border: none; margin: 0; padding: 0;">Statistics</h2>
                <button id="btn-add-chart" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">+ Add Chart</button>
            </div>
            <div id="charts-container" role="region" aria-label="Statistics charts">
                <!-- Dynamic chart cards appended here -->
            </div>
        </div>

        <!-- Data Table & Export Panel -->
        <div class="dashboard-panel island-card" id="panel-data">
            <h2 class="panel-title">Data Table</h2>

            <div id="export-actions" class="export-bar" role="toolbar" aria-label="Export options">
                <div class="export-group data-export">
                    <span class="export-group-label">Data:</span>
                    <button id="btn-export-csv" class="btn btn-secondary" disabled>Export CSV</button>
                    <button id="btn-export-json" class="btn btn-secondary" disabled>Export JSON</button>
                </div>
                
                <div class="export-separator" aria-hidden="true"></div>
                
                <div class="export-group chart-export">
                    <span class="export-group-label">Chart:</span>
                    <select id="export-chart-select" class="form-control" style="width: auto; max-width: 200px;" disabled>
                        <option value="">No charts available</option>
                    </select>
                    <button id="btn-export-svg" class="btn btn-secondary" disabled>Export SVG</button>
                    <button id="btn-export-webp" class="btn btn-secondary" disabled>Export WebP</button>
                </div>
            </div>

            <div id="report-table-container" class="table-responsive" role="region" aria-label="Accident data table">
                <table id="report-table" class="data-table" style="display:none; white-space: nowrap;">
                    <thead>
                        <tr>
                            <th scope="col">Date &amp; Time</th>
                            <th scope="col">Severity</th>
                            <th scope="col">Latitude</th>
                            <th scope="col">Longitude</th>
                            <th scope="col">State</th>
                            <th scope="col">City</th>
                            <th scope="col">County</th>
                            <th scope="col">Weather</th>
                            <th scope="col">Temp (F)</th>
                            <th scope="col">Visibility (mi)</th>
                            <th scope="col">Crossing</th>
                            <th scope="col">Junction</th>
                            <th scope="col">Traffic Signal</th>
                            <th scope="col">Day/Night</th>
                        </tr>
                    </thead>
                    <tbody id="report-table-body"></tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<!-- API modules -->
<script src="/js/api/MapApi.js"></script>
<script src="/js/api/ReportApi.js"></script>
<script src="/js/api/StatisticsApi.js"></script>

<!-- Dashboard DOM controller -->
<script src="/js/ui/homeDom.js"></script>