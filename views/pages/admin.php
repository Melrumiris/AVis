<?php
/**
 * views/pages/admin.php
 *
 * Admin console page fragment — injected into views/layouts/main.php.
 * Only reachable by users with role = 'admin' (enforced in ViewAdminAction).
 * Contains manual accident entry and CSV upload forms.
 */
?>

<section id="admin-page" class="page-section">
    <h1 class="page-title">Admin Console</h1>

    <div id="admin-panels" class="upload-grid">

        <div class="upload-panel" id="panel-manual">
            <h2 class="panel-title">Manual Entry</h2>

            <form id="form-manual-accident" novalidate>
                <div class="form-group">
                    <label for="m-date-time">Date &amp; Time</label>
                    <input type="datetime-local" id="m-date-time" name="date_time" required>
                </div>

                <div class="form-group">
                    <label for="m-severity">Severity</label>
                    <select id="m-severity" name="severity" required>
                        <option value="">— select —</option>
                        <option value="1">1 — Minor</option>
                        <option value="2">2 — Moderate</option>
                        <option value="3">3 — Severe</option>
                        <option value="4">4 — Fatal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="m-latitude">Latitude</label>
                    <input type="number" step="any" id="m-latitude" name="latitude"
                           placeholder="e.g. 37.0902" required>
                </div>

                <div class="form-group">
                    <label for="m-longitude">Longitude</label>
                    <input type="number" step="any" id="m-longitude" name="longitude"
                           placeholder="e.g. -95.7129" required>
                </div>

                <div class="form-group">
                    <label for="m-state">State (2-letter code)</label>
                    <input type="text" maxlength="2" id="m-state" name="state"
                           placeholder="e.g. CA" style="text-transform:uppercase;">
                </div>

                <button type="submit" id="btn-submit-manual" class="btn btn-primary">
                    Add Accident
                </button>

                <p id="msg-manual" class="form-message" role="status" aria-live="polite"></p>
            </form>
        </div>

        <div class="upload-panel" id="panel-csv">
            <h2 class="panel-title">Import from CSV</h2>

            <form id="form-csv-accident" novalidate>
                <p class="form-hint">
                    Accepted format (header row + data rows):<br>
                    <code>Date_Time, Severity, Latitude, Longitude, State</code>
                </p>

                <div class="form-group">
                    <label for="csv-file">Select CSV file</label>
                    <input type="file" id="csv-file" name="csv_file" accept=".csv" required>
                </div>

                <button type="submit" id="btn-submit-csv" class="btn btn-primary">
                    Upload File
                </button>

                <p id="msg-csv" class="form-message" role="status" aria-live="polite"></p>
            </form>
        </div>

    </div>
</section>

<script src="/js/api/AccidentApi.js"></script>
<script src="/js/ui/adminDom.js"></script>
