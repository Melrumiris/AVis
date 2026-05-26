<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Date</title>
    <link rel="stylesheet" href="/projectWEB/public/style.css">
</head>
<body>

<?php require ROOT . '/src/components/Navbar.php'; ?>

<main style="padding-top: 80px;">
    <h1 style="text-align: center;">Adăugare Date în Sistem</h1>
    
    <div class="upload-container">
        <div class="upload-box">
            <h3>Introducere Manuală</h3>
            <form id="form-manual">
                <input type="datetime-local" id="m_data" required>
                <select id="m_sev" required>
                    <option value="1">1 - Ușor</option>
                    <option value="2">2 - Moderat</option>
                    <option value="3">3 - Sever</option>
                    <option value="4">4 - Foarte Sever</option>
                </select>
                <input type="number" step="any" id="m_lat" placeholder="Latitudine (ex: 37.0902)" required>
                <input type="number" step="any" id="m_lng" placeholder="Longitudine (ex: -95.7129)" required>
                <button type="submit" class="btn-submit">Adaugă Accident</button>
            </form>
        </div>

        <div class="upload-box">
            <h3>Import din CSV</h3>
            <form id="form-csv">
                <p style="font-size: 12px; color: #666;">Format acceptat: Data_Ora, Severitate, Lat, Lng</p>
                <input type="file" id="csv_file" accept=".csv" required>
                <button type="submit" class="btn-submit btn-submit-csv">Încarcă Fișier</button>
            </form>
        </div>
    </div>
</main>

<script>
function getBasePath() {
    const pathArray = window.location.pathname.split('/');
    return '/' + pathArray[1] + '/' + pathArray[2];
}

// Trimitere Date Manuale
document.getElementById('form-manual').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let formData = new FormData();
    formData.append('data_ora', document.getElementById('m_data').value.replace('T', ' ') + ':00');
    formData.append('severitate', document.getElementById('m_sev').value);
    formData.append('latitudine', document.getElementById('m_lat').value);
    formData.append('longitudine', document.getElementById('m_lng').value);

    fetch(getBasePath() + '/api/upload', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => { alert(data.message); this.reset(); });
});

// Trimitere Fișier CSV
document.getElementById('form-csv').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let formData = new FormData();
    formData.append('csv_file', document.getElementById('csv_file').files[0]);

    const btn = this.querySelector('button');
    btn.innerText = "Se încarcă...";
    
    fetch(getBasePath() + '/api/upload', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => { 
            alert(data.message); 
            this.reset(); 
            btn.innerText = "Încarcă Fișier"; 
        });
});
</script>

</body>
</html>