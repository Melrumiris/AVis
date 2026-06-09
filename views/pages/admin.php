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

        <div class="upload-panel island-card" id="panel-manual">
            <h2 class="panel-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: var(--spacing-sm); margin-bottom: var(--spacing-lg);">Manual Entry</h2>

            <form id="form-manual-accident" novalidate>
                <div class="form-group">
                    <label class="form-label">Date &amp; Time <span style="font-weight: 400;">(24-hour)</span></label>
                    <div style="display: flex; align-items: stretch;">
                        <input type="date" id="m-date" name="date" class="form-control"
                               style="flex: 2; border-radius: var(--radius-sm) 0 0 var(--radius-sm); border-right: none;" required>
                        <div style="display: flex; align-items: center; padding: 0 2px; background: var(--bg-body); border: 1px solid var(--border-color); border-left: none; border-right: none; color: var(--text-muted); font-size: 0.85rem; flex-shrink: 0;">─</div>
                        <input type="number" id="m-hour" name="hour" class="form-control"
                               min="0" max="23" placeholder="HH"
                               style="width: 58px; border-radius: 0; border-right: none; text-align: center; padding-left: 0.25rem; padding-right: 0.25rem;" required>
                        <span style="display: flex; align-items: center; padding: 0 1px; background: var(--bg-body); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">:</span>
                        <input type="number" id="m-minute" name="minute" class="form-control"
                               min="0" max="59" placeholder="MM"
                               style="width: 58px; border-radius: 0; border-right: none; border-left: none; text-align: center; padding-left: 0.25rem; padding-right: 0.25rem;" required>
                        <span style="display: flex; align-items: center; padding: 0 1px; background: var(--bg-body); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">:</span>
                        <input type="number" id="m-second" name="second" class="form-control"
                               min="0" max="59" placeholder="SS" value="0"
                               style="width: 58px; border-radius: 0 var(--radius-sm) var(--radius-sm) 0; border-left: none; text-align: center; padding-left: 0.25rem; padding-right: 0.25rem;">
                    </div>
                </div>
                <div class="form-group">
                    <label for="m-severity" class="form-label">Severity</label>
                    <select id="m-severity" name="severity" class="form-control" required>
                        <option value="">— select —</option>
                        <option value="1">1 — Minor</option>
                        <option value="2">2 — Moderate</option>
                        <option value="3">3 — Severe</option>
                        <option value="4">4 — Fatal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="m-latitude" class="form-label">Latitude</label>
                    <input type="number" step="any" id="m-latitude" name="latitude" class="form-control"
                           placeholder="e.g. 37.0902" required>
                </div>

                <div class="form-group">
                    <label for="m-longitude" class="form-label">Longitude</label>
                    <input type="number" step="any" id="m-longitude" name="longitude" class="form-control"
                           placeholder="e.g. -95.7129" required>
                </div>

                <div class="form-group">
                    <label for="m-state" class="form-label">State (2-letter code)</label>
                    <input type="text" maxlength="2" id="m-state" name="state" class="form-control"
                           placeholder="e.g. CA" style="text-transform:uppercase;">
                </div>

                <details style="margin-bottom: var(--spacing-lg); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: var(--spacing-sm);">
                    <summary style="cursor: pointer; font-weight: 600; color: var(--text-main);">Optional Details</summary>
                    <div style="margin-top: var(--spacing-md);">
                        <div class="form-group">
                            <label for="m-city" class="form-label">City</label>
                            <input type="text" id="m-city" name="city" class="form-control" placeholder="e.g. Los Angeles">
                        </div>
                        <div class="form-group">
                            <label for="m-county" class="form-label">County</label>
                            <input type="text" id="m-county" name="county" class="form-control" placeholder="e.g. Los Angeles">
                        </div>
                        <div class="form-group">
                            <label for="m-weather" class="form-label">Weather Condition</label>
                            <input type="text" id="m-weather" name="weather_condition" class="form-control" placeholder="e.g. Clear">
                        </div>
                        <div style="display: flex; gap: var(--spacing-sm);">
                            <div class="form-group" style="flex: 1;">
                                <label for="m-temp" class="form-label">Temperature (°F)</label>
                                <input type="number" step="any" id="m-temp" name="temperature" class="form-control" placeholder="e.g. 75.5">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="m-visibility" class="form-label">Visibility (mi)</label>
                                <input type="number" step="any" id="m-visibility" name="visibility" class="form-control" placeholder="e.g. 10.0">
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--spacing-sm);">
                            <div class="form-group" style="flex: 1;">
                                <label for="m-crossing" class="form-label">Crossing</label>
                                <select id="m-crossing" name="crossing" class="form-control">
                                    <option value="">—</option>
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="m-junction" class="form-label">Junction</label>
                                <select id="m-junction" name="junction" class="form-control">
                                    <option value="">—</option>
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="m-signal" class="form-label">Traffic Signal</label>
                                <select id="m-signal" name="traffic_signal" class="form-control">
                                    <option value="">—</option>
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="m-daynight" class="form-label">Day/Night</label>
                            <select id="m-daynight" name="sunrise_sunset" class="form-control">
                                <option value="">—</option>
                                <option value="Day">Day</option>
                                <option value="Night">Night</option>
                            </select>
                        </div>
                    </div>
                </details>

                <button type="submit" id="btn-submit-manual" class="btn btn-primary">
                    Add Accident
                </button>

                <p id="msg-manual" class="form-message" role="status" aria-live="polite"></p>
            </form>
        </div>

        <div class="upload-panel island-card" id="panel-csv">
            <h2 class="panel-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: var(--spacing-sm); margin-bottom: var(--spacing-lg);">Import from CSV</h2>

            <form id="form-csv-accident" novalidate>
                <p class="form-hint">
                    Accepted format (header row + data rows):<br>
                    <code>Date_Time, Severity, Latitude, Longitude, State, City, County, Weather_Condition, Temperature, Visibility, Crossing, Junction, Traffic_Signal, Sunrise_Sunset</code>
                </p>

                <div class="form-group">
                    <label class="form-label">Upload Mode</label>
                    <div class="upload-mode-toggle" style="margin-top: var(--spacing-xs);">
                        <input type="radio" name="upload_mode" id="mode-append" value="append" checked>
                        <label for="mode-append">Append Records</label>
                        <input type="radio" name="upload_mode" id="mode-replace" value="replace">
                        <label for="mode-replace">Replace Period</label>
                    </div>
                </div>

                <div id="csv-replace-period" style="display: none; margin-bottom: var(--spacing-lg);">
                    <div style="display: flex; gap: var(--spacing-md);">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label for="csv-start-time" class="form-label">Start Date &amp; Time</label>
                            <input type="datetime-local" id="csv-start-time" name="start_time" class="form-control">
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label for="csv-end-time" class="form-label">End Date &amp; Time</label>
                            <input type="datetime-local" id="csv-end-time" name="end_time" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="csv-file" class="form-label">Select CSV file</label>
                    <input type="file" id="csv-file" name="csv_file" accept=".csv" class="form-control" required style="padding: 0.45rem;">
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
