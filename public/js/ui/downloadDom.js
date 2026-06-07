document.addEventListener('DOMContentLoaded', () => {
    let loadedData = [];

    const toggleExportButtons = (enabled) => {
        document.getElementById('btn-export-csv').disabled  = !enabled;
        document.getElementById('btn-export-svg').disabled  = !enabled;
        document.getElementById('btn-export-webp').disabled = !enabled;
    };

    const triggerDownload = (url, filename) => {
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
    };

    const buildSvg = (rows) => {
        const subset = rows.slice(0, 35);
        const height = 60 + subset.length * 25;
        let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="750" height="${height}" style="background:#fff; font-family:Arial, sans-serif;">`;
        svg += `<text x="20" y="30" font-size="16" font-weight="bold" fill="#333">Accident Report (First ${subset.length} rows)</text>`;
        svg += `<text x="20"  y="55" font-weight="bold" font-size="12" fill="#555">DATE TIME</text>`;
        svg += `<text x="250" y="55" font-weight="bold" font-size="12" fill="#555">SEVERITY</text>`;
        svg += `<text x="400" y="55" font-weight="bold" font-size="12" fill="#555">LATITUDE</text>`;
        svg += `<text x="550" y="55" font-weight="bold" font-size="12" fill="#555">LONGITUDE</text>`;
        subset.forEach((r, i) => {
            const y = 80 + i * 25;
            svg += `<text x="20"  y="${y}" font-size="11" fill="#666">${r.data_ora}</text>`;
            svg += `<text x="250" y="${y}" font-size="11" fill="#666">${r.severitate}</text>`;
            svg += `<text x="400" y="${y}" font-size="11" fill="#666">${r.latitudine}</text>`;
            svg += `<text x="550" y="${y}" font-size="11" fill="#666">${r.longitudine}</text>`;
        });
        svg += '</svg>';
        return svg;
    };

    document.getElementById('btn-load-data').addEventListener('click', async () => {
        const sdate = document.getElementById('dl-sdate').value;
        const fdate = document.getElementById('dl-fdate').value;
        const btn   = document.getElementById('btn-load-data');

        btn.textContent = 'Loading...';
        btn.disabled    = true;

        try {
            const result = await ReportApi.getData(sdate, fdate);
            btn.textContent = 'Load Data';
            btn.disabled    = false;

            if (!result.success) {
                alert(`Error: ${result.error}`);
                return;
            }

            loadedData = result.data;

            const table = document.getElementById('report-table');
            const tbody = document.getElementById('report-table-body');

            if (loadedData.length === 0) {
                alert('No data found for the selected period.');
                table.style.display = 'none';
                toggleExportButtons(false);
                return;
            }

            tbody.innerHTML = loadedData.map(r => `<tr>
                <td>${r.data_ora}</td>
                <td>${r.severitate}</td>
                <td>${r.latitudine}</td>
                <td>${r.longitudine}</td>
            </tr>`).join('');

            table.style.display = 'table';
            toggleExportButtons(true);
        } catch (err) {
            console.error('Failed to load report data:', err);
            btn.textContent = 'Load Data';
            btn.disabled    = false;
        }
    });

    document.getElementById('btn-export-csv').addEventListener('click', () => {
        if (!loadedData.length) return;
        const csv = 'Data_Ora,Severitate,Latitudine,Longitudine\n'
            + loadedData.map(r => `${r.data_ora},${r.severitate},${r.latitudine},${r.longitudine}`).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        triggerDownload(URL.createObjectURL(blob), 'accidente.csv');
    });

    document.getElementById('btn-export-svg').addEventListener('click', () => {
        if (!loadedData.length) return;
        const blob = new Blob([buildSvg(loadedData)], { type: 'image/svg+xml;charset=utf-8' });
        triggerDownload(URL.createObjectURL(blob), 'raport_accidente.svg');
    });

    document.getElementById('btn-export-webp').addEventListener('click', () => {
        if (!loadedData.length) return;
        const blob = new Blob([buildSvg(loadedData)], { type: 'image/svg+xml;charset=utf-8' });
        const url  = URL.createObjectURL(blob);
        const img  = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width  = 750;
            canvas.height = img.height;
            canvas.getContext('2d').drawImage(img, 0, 0);
            URL.revokeObjectURL(url);
            triggerDownload(canvas.toDataURL('image/webp', 1.0), 'raport_accidente.webp');
        };
        img.src = url;
    });
});
