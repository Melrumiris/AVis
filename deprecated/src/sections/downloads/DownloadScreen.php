<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Descarcă Date</title>
    <link rel="stylesheet" href="/projectWEB/public/style.css">
</head>
<body>

<?php require ROOT . '/src/components/Navbar.php'; ?>

<main class="main-download">
    <h1 style="text-align: center;">Descărcare și Export Date SQL</h1>
    
    <div class="download-box">
        <label>De la: <input type="date" id="sdate"></label>
        <label>Până la: <input type="date" id="fdate"></label>
        <button id="btn-afiseaza" class="btn-export btn-afiseaza">Afișează Date</button>
    </div>

    <div class="download-box">
        <button id="export-csv" class="btn-export btn-csv" disabled>Export CSV</button>
        <button id="export-svg" class="btn-export btn-svg" disabled>Export SVG</button>
        <button id="export-webp" class="btn-export btn-webp" disabled>Export WebP</button>
    </div>

    <div id="tabel-container">
        <table id="tabel-date" class="tabel-date">
            <thead>
                <tr>
                    <th>Data și Ora</th>
                    <th>Severitate</th>
                    <th>Latitudine</th>
                    <th>Longitudine</th>
                </tr>
            </thead>
            <tbody id="tabel-corp"></tbody>
        </table>
    </div>
</main>

<script>
let dateIncarcate = [];

function getBasePath() {
    const pathArray = window.location.pathname.split('/');
    return '/' + pathArray[1] + '/' + pathArray[2];
}

// 1. Preluarea datelor din baza de date
document.getElementById('btn-afiseaza').addEventListener('click', () => {
    const sdate = document.getElementById('sdate').value;
    const fdate = document.getElementById('fdate').value;
    
    // Dezactivăm butonul temporar ca să nu fie apăsat de 2 ori
    const btnAfiseaza = document.getElementById('btn-afiseaza');
    btnAfiseaza.innerText = "Se încarcă...";
    btnAfiseaza.disabled = true;

    fetch(getBasePath() + `/api/download?sdate=${sdate}&fdate=${fdate}`)
        .then(res => res.json())
        .then(data => {
            dateIncarcate = data;
            const tbody = document.getElementById('tabel-corp');
            
            if(data.length === 0) {
                alert("Nu s-au găsit date în perioada selectată.");
                document.getElementById('tabel-date').style.display = 'none';
                dezactiveazaButoane(true);
                btnAfiseaza.innerText = "Afișează Date";
                btnAfiseaza.disabled = false;
                return;
            }

            // OPTIMIZARE: Construim tabelul în memorie, nu pe ecran
            let htmlTabel = '';
            data.forEach(rand => {
                htmlTabel += `<tr>
                    <td>${rand.data_ora}</td>
                    <td>${rand.severitate}</td>
                    <td>${rand.latitudine}</td>
                    <td>${rand.longitudine}</td>
                </tr>`;
            });
            
            // Afișăm totul dintr-o singură mișcare!
            tbody.innerHTML = htmlTabel;
            
            document.getElementById('tabel-date').style.display = 'table';
            dezactiveazaButoane(false);

            // Revenim la butonul normal
            btnAfiseaza.innerText = "Afișează Date";
            btnAfiseaza.disabled = false;
        })
        .catch(eroare => {
            console.error("Eroare la preluare:", eroare);
            btnAfiseaza.innerText = "Afișează Date";
            btnAfiseaza.disabled = false;
        });
});

function dezactiveazaButoane(stare) {
    document.getElementById('export-csv').disabled = stare;
    document.getElementById('export-svg').disabled = stare;
    document.getElementById('export-webp').disabled = stare;
}

document.getElementById('export-csv').addEventListener('click', () => {
    if(!dateIncarcate.length) return;
    let csv = 'Data_Ora,Severitate,Latitudine,Longitudine\n';
    dateIncarcate.forEach(r => {
        csv += `${r.data_ora},${r.severitate},${r.latitudine},${r.longitudine}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    descarcaElement(URL.createObjectURL(blob), 'accidente.csv');
});

function genereazaCodSVG(date) {
    const randuriMape = date.slice(0, 35);
    let inaltime = 60 + (randuriMape.length * 25);
    
    let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="750" height="${inaltime}" style="background:#fff; font-family:Arial, sans-serif;">`;
    svg += `<text x="20" y="30" font-size="16" font-weight="bold" fill="#333">Raport Accidente (Primele ${randuriMape.length} randuri)</text>`;
    svg += `<text x="20" y="55" font-weight="bold" font-size="12" fill="#555">DATA ORA</text>`;
    svg += `<text x="250" y="55" font-weight="bold" font-size="12" fill="#555">SEVERITATE</text>`;
    svg += `<text x="400" y="55" font-weight="bold" font-size="12" fill="#555">LATITUDINE</text>`;
    svg += `<text x="550" y="55" font-weight="bold" font-size="12" fill="#555">LONGITUDINE</text>`;
    
    randuriMape.forEach((r, idx) => {
        let y = 80 + (idx * 25);
        svg += `<text x="20" y="${y}" font-size="11" fill="#666">${r.data_ora}</text>`;
        svg += `<text x="250" y="${y}" font-size="11" fill="#666">${r.severitate}</text>`;
        svg += `<text x="400" y="${y}" font-size="11" fill="#666">${r.latitudine}</text>`;
        svg += `<text x="550" y="${y}" font-size="11" fill="#666">${r.longitudine}</text>`;
    });
    svg += `</svg>`;
    return svg;
}

document.getElementById('export-svg').addEventListener('click', () => {
    const codSVG = genereazaCodSVG(dateIncarcate);
    const blob = new Blob([codSVG], { type: 'image/svg+xml;charset=utf-8' });
    descarcaElement(URL.createObjectURL(blob), 'raport_accidente.svg');
});

document.getElementById('export-webp').addEventListener('click', () => {
    const codSVG = genereazaCodSVG(dateIncarcate);
    const blob = new Blob([codSVG], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    
    const img = new Image();
    img.onload = function() {
        const canvas = document.createElement('canvas');
        canvas.width = 750;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        URL.revokeObjectURL(url);
        
        const webpUrl = canvas.toDataURL('image/webp', 1.0);
        descarcaElement(webpUrl, 'raport_accidente.webp');
    };
    img.src = url;
});

function descarcaElement(url, numeFisier) {
    const a = document.createElement('a');
    a.href = url;
    a.download = numeFisier;
    a.click();
}
</script>

</body>
</html>