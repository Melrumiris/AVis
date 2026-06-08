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

    <!-- Shared filters -->
    <div id="dashboard-filters" class="filter-bar" role="search" aria-label="Dashboard filters">
        <label for="dash-sdate">From:</label>
        <input type="date" id="dash-sdate" name="sdate">

        <label for="dash-fdate">To:</label>
        <input type="date" id="dash-fdate" name="fdate">

        <label for="dash-severity">Severity:</label>
        <select id="dash-severity" name="severity">
            <option value="ALL">All</option>
            <option value="1">1 — Minor</option>
            <option value="2">2 — Moderate</option>
            <option value="3">3 — Severe</option>
            <option value="4">4 — Fatal</option>
        </select>

        <label for="dash-region">Region:</label>
        <select id="dash-region" name="region">
            <option value="ALL">All</option>
            <option value="NE">North-East</option>
            <option value="NW">North-West</option>
            <option value="SE">South-East</option>
            <option value="SW">South-West</option>
        </select>

        <label for="dash-group-by">Group by:</label>
        <select id="dash-group-by" name="group_by">
            <option value="severity">Severity</option>
            <option value="year">Year</option>
            <option value="month">Month</option>
            <option value="day">Day of Week</option>
            <option value="location">Location</option>
        </select>

        <button id="btn-apply-filters" class="btn btn-primary">Apply Filters</button>
    </div>

    <!-- Dashboard grid -->
    <div id="dashboard-grid" class="dashboard-grid">

        <!-- Map Panel -->
        <div class="dashboard-panel" id="panel-map">
            <h2 class="panel-title">Accident Map</h2>
            <div id="map-container" role="region" aria-label="Accident map"></div>
        </div>

        <!-- Statistics Panel -->
        <div class="dashboard-panel" id="panel-stats">
            <h2 class="panel-title">Statistics</h2>
            <div id="chart-container" role="img" aria-label="Statistics chart">
                <canvas id="stats-chart"></canvas>
            </div>
        </div>

        <!-- Data Table & Export Panel -->
        <div class="dashboard-panel" id="panel-data">
            <h2 class="panel-title">Data Table</h2>

            <div id="export-actions" class="export-bar" role="toolbar" aria-label="Export options">
                <button id="btn-export-csv"  class="btn btn-secondary" disabled>Export CSV</button>
                <button id="btn-export-svg"  class="btn btn-secondary" disabled>Export SVG</button>
                <button id="btn-export-webp" class="btn btn-secondary" disabled>Export WebP</button>
            </div>

            <div id="report-table-container" role="region" aria-label="Accident data table">
                <table id="report-table" class="data-table" style="display:none;">
                    <thead>
                        <tr>
                            <th scope="col">Date &amp; Time</th>
                            <th scope="col">Severity</th>
                            <th scope="col">Latitude</th>
                            <th scope="col">Longitude</th>
                            <th scope="col">State</th>
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