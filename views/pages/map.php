<?php
/**
 * views/pages/map.php
 *
 * Map page fragment — injected into views/layouts/main.php.
 * All data fetching is done client-side via ApiHandler.js → /api/v0/map.
 * No PHP business logic here.
 */
?>

<section id="map-page" class="page-section">
    <h1 class="page-title">Accident Map</h1>

    <div id="map-filters" class="filter-bar" role="search" aria-label="Map date filters">
        <label for="map-sdate">From:</label>
        <input type="date" id="map-sdate" name="sdate">

        <label for="map-fdate">To:</label>
        <input type="date" id="map-fdate" name="fdate">

        <button id="btn-filter-map" class="btn btn-primary">Filter Map</button>
    </div>

    <div id="map-container" role="region" aria-label="Accident map"></div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLEg=" crossorigin="anonymous"></script>

<script src="/js/api/MapApi.js"></script>
<script src="/js/ui/mapDom.js"></script>
