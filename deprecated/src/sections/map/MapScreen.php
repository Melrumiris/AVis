<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Harta Accidente</title>
    <link rel="stylesheet" href="/projectWEB/public/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

<?php require ROOT . '/src/components/Navbar.php'; ?>

<main style="padding-top: 80px;">
    <h1 style="text-align: center;">Harta Accidentelor</h1>
    
    <div style="text-align: center; margin-bottom: 20px;">
        <label>De la: <input type="date" id="sdate"></label>
        <label>Până la: <input type="date" id="fdate"></label>
        <button id="btn-filtreaza">Filtrează Harta</button>
    </div>

    <div id="harta-container"></div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const harta = L.map('harta-container').setView([37.0902, -95.7129], 4);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(harta);

function getBasePath() {
    const pathArray = window.location.pathname.split('/');
    return '/' + pathArray[1] + '/' + pathArray[2];
}
const grupMarkere = L.layerGroup().addTo(harta);
function incarcaAccidente(sdate = '', fdate = '') {
    grupMarkere.clearLayers();
    const query = `?sdate=${sdate}&fdate=${fdate}`;
    fetch(getBasePath() + '/api/map' + query) 
        .then(response => response.json())
        .then(dateAccidente => {
            dateAccidente.forEach(accident => {
                if(accident.lat && accident.lng) {
                    L.marker([accident.lat, accident.lng])
                     .bindPopup(`<b>Accident</b><br>Severitate: ${accident.severitate}`)
                     .addTo(grupMarkere);
                }
            });
        });
}
incarcaAccidente();
document.getElementById('btn-filtreaza').addEventListener('click', () => {
    const sdate = document.getElementById('sdate').value;
    const fdate = document.getElementById('fdate').value;
    incarcaAccidente(sdate, fdate);
});
</script>

</body>
</html>