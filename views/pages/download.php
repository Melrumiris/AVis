<?php
/**
 * views/pages/download.php
 *
 * Download / Export page fragment — injected into views/layouts/main.php.
 * All data fetching is done client-side via ApiHandler.js → /api/v0/report.
 * CSV/SVG/WebP generation is also done entirely client-side.
 * No PHP business logic here.
 */
?>

<section id="download-page" class="page-section">
    <h1 class="page-title">Download &amp; Export Data</h1>

    <div id="download-filters" class="filter-bar" role="search" aria-label="Download date filters">
        <label for="dl-sdate">From:</label>
        <input type="date" id="dl-sdate" name="sdate">

        <label for="dl-fdate">To:</label>
        <input type="date" id="dl-fdate" name="fdate">

        <button id="btn-load-data" class="btn btn-primary">Load Data</button>
    </div>

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
                </tr>
            </thead>
            <tbody id="report-table-body"></tbody>
        </table>
    </div>
</section>

<script src="/js/api/ReportApi.js"></script>
<script src="/js/ui/downloadDom.js"></script>
