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

    // ─── Manual Entry ────────────────────────────────────────────
    document.getElementById('form-manual-accident').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const dateTime  = document.getElementById('m-date-time').value.replace('T', ' ') + ':00';
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

    // ─── CSV Upload ──────────────────────────────────────────────
    document.getElementById('form-csv-accident').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn  = this.querySelector('button[type="submit"]');
        const file = document.getElementById('csv-file').files[0];

        if (!file) {
            showMessage('msg-csv', 'Please select a CSV file.', true);
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Uploading...';

        try {
            const result = await AccidentApi.uploadFile(file);
            if (result.success) {
                showMessage('msg-csv', `Successfully imported ${result.data.inserted} record(s).`);
                this.reset();
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
