<?php
/**
 * views/pages/upload.php
 *
 * Upload / Admin data-entry page fragment — injected into views/layouts/main.php.
 * Only reachable by users with role = 'admin' (enforced in ViewUploadAction).
 * All API calls are handled via ApiHandler.js → /api/v0/admin/accident and
 * /api/v0/admin/accident/file.
 * No PHP business logic here.
 */
?>

<section id="upload-page" class="page-section">
    <h1 class="page-title">Add Accident Data</h1>

    <div id="upload-panels" class="upload-grid">

        <div class="upload-panel" id="panel-manual">
            <h2 class="panel-title">Manual Entry</h2>

            <form id="form-manual-accident" novalidate>
                <div class="form-group">
                    <label for="m-data-ora">Date &amp; Time</label>
                    <input type="datetime-local" id="m-data-ora" name="data_ora" required>
                </div>

                <div class="form-group">
                    <label for="m-severitate">Severity</label>
                    <select id="m-severitate" name="severitate" required>
                        <option value="">— select —</option>
                        <option value="1">1 — Minor</option>
                        <option value="2">2 — Moderate</option>
                        <option value="3">3 — Severe</option>
                        <option value="4">4 — Fatal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="m-latitudine">Latitude</label>
                    <input type="number" step="any" id="m-latitudine" name="latitudine"
                           placeholder="e.g. 37.0902" required>
                </div>

                <div class="form-group">
                    <label for="m-longitudine">Longitude</label>
                    <input type="number" step="any" id="m-longitudine" name="longitudine"
                           placeholder="e.g. -95.7129" required>
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
                    <code>Data_Ora, Severitate, Latitudine, Longitudine</code>
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
<script src="/js/ui/uploadDom.js"></script>
