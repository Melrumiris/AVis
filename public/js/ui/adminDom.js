/**
 * adminDom.js — Admin console DOM controller
 *
 * Handles manual accident entry and CSV file upload forms.
 */
document.addEventListener('DOMContentLoaded', () => {
    const showMessage = (elementId, text, isError = false) => {
        const el = document.getElementById(elementId);
        el.textContent = text;
        el.style.color = isError ? '#e53e3e' : '#38a169';
    };

    const pad = (n) => String(n).padStart(2, '0');

    // ─── Manual Entry ────────────────────────────────────────────
    document.getElementById('form-manual-accident').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const date    = document.getElementById('m-date').value;
        const hour    = parseInt(document.getElementById('m-hour').value, 10) || 0;
        const minute  = parseInt(document.getElementById('m-minute').value, 10) || 0;
        const second  = parseInt(document.getElementById('m-second').value, 10) || 0;
        const dateTime = `${date} ${pad(hour)}:${pad(minute)}:${pad(second)}`;

        const severity  = parseInt(document.getElementById('m-severity').value, 10);
        const latitude  = parseFloat(document.getElementById('m-latitude').value);
        const longitude = parseFloat(document.getElementById('m-longitude').value);
        const state     = document.getElementById('m-state').value.toUpperCase().trim();

        try {
            const result = await AccidentApi.insertManual(dateTime, severity, latitude, longitude, state);
            if (result.success) {
                showMessage('msg-manual', 'Accident added successfully.');
                this.reset();
            } else {
                showMessage('msg-manual', `Error: ${result.error}`, true);
            }
        } catch (err) {
            showMessage('msg-manual', 'Request failed. Please try again.', true);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Add Accident';
        }
    });

    // ─── Upload Mode Toggle ──────────────────────────────────────
    const replacePeriodSection = document.getElementById('csv-replace-period');
    document.querySelectorAll('input[name="upload_mode"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const isReplace = document.querySelector('input[name="upload_mode"]:checked').value === 'replace';
            replacePeriodSection.style.display = isReplace ? 'block' : 'none';
        });
    });

    // ─── CSV Upload ──────────────────────────────────────────────
    document.getElementById('form-csv-accident').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn  = this.querySelector('button[type="submit"]');
        const file = document.getElementById('csv-file').files[0];
        const mode = document.querySelector('input[name="upload_mode"]:checked').value;

        if (!file) {
            showMessage('msg-csv', 'Please select a CSV file.', true);
            return;
        }

        if (mode === 'replace') {
            const startTime = document.getElementById('csv-start-time').value;
            const endTime   = document.getElementById('csv-end-time').value;
            if (!startTime || !endTime) {
                showMessage('msg-csv', 'Please select both start and end date/time for Replace Period.', true);
                return;
            }
        }

        btn.disabled = true;
        btn.textContent = 'Uploading...';

        try {
            let result;
            if (mode === 'replace') {
                const startTime = document.getElementById('csv-start-time').value.replace('T', ' ') + ':00';
                const endTime   = document.getElementById('csv-end-time').value.replace('T', ' ') + ':00';
                result = await AccidentApi.replaceFile(file, startTime, endTime);
            } else {
                result = await AccidentApi.uploadFile(file);
            }

            if (result.success) {
                showMessage('msg-csv', `Successfully processed ${result.data.inserted} record(s).`);
                this.reset();
                replacePeriodSection.style.display = 'none';
            } else {
                showMessage('msg-csv', `Error: ${result.error}`, true);
            }
        } catch (err) {
            showMessage('msg-csv', 'Upload failed. Please try again.', true);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Upload File';
        }
    });
});
