<?php
$conn = new PDO("pgsql:host=localhost;port=5432;dbname=postgres", "postgres", "password123");
$sdate = isset($_GET['sdate']) ? $_GET['sdate'] : '';
$fdate = isset($_GET['fdate']) ? $_GET['fdate'] : '';
$severitate = isset($_GET['severitate']) ? $_GET['severitate'] : 'ALL';
$regiune = isset($_GET['region']) ? $_GET['region'] : 'ALL';
$grupare = isset($_GET['criteriu_grupare']) ? $_GET['criteriu_grupare'] : 'severitate';
$unde = "WHERE 1=1 ";
if(!empty($sdate)){
    $unde .= " AND data_ora >= '$sdate 00:00:00'";
}
if(!empty($fdate)){
    $unde .= " AND data_ora <= '$fdate 23:59:59'";
}
if($severitate != 'ALL'){
    $sev = intval($severitate);
    $unde .= " AND severitate = $sev";
}
if($regiune == 'NE') { $unde .= " AND latitudine >= 39.8 AND longitudine >= -98.5"; }
else if($regiune == 'NW') { $unde .= " AND latitudine >= 39.8 AND longitudine < -98.5"; }
else if($regiune == 'SE') { $unde .= " AND latitudine < 39.8 AND longitudine >= -98.5"; }
else if($regiune == 'SW') { $unde .= " AND latitudine < 39.8 AND longitudine < -98.5"; }
if($grupare == 'severitate'){
    $sql = "SELECT severitate::text AS eticheta, COUNT(*) as total FROM accidente $unde GROUP BY severitate ORDER BY severitate";
}
else if($grupare == 'an'){
    $sql = "SELECT EXTRACT(YEAR FROM data_ora)::text AS eticheta, COUNT(*) as total FROM accidente $unde GROUP BY EXTRACT(YEAR FROM data_ora) ORDER BY eticheta";
}
else if($grupare == 'luna'){
    $sql = "SELECT EXTRACT(MONTH FROM data_ora)::text AS eticheta, COUNT(*) as total FROM accidente $unde GROUP BY EXTRACT(MONTH FROM data_ora) ORDER BY eticheta";
}
else if($grupare == 'ziua'){
    $sql = "SELECT EXTRACT(DOW FROM data_ora)::text AS eticheta, COUNT(*) as total FROM accidente $unde GROUP BY EXTRACT(DOW FROM data_ora) ORDER BY eticheta";
}
else if($grupare == 'location'){
    $sql = "SELECT 
                CASE 
                    WHEN latitudine >= 39.8 AND longitudine >= -98.5 THEN 'North-East'
                    WHEN latitudine >= 39.8 AND longitudine < -98.5 THEN 'North-West'
                    WHEN latitudine < 39.8 AND longitudine >= -98.5 THEN 'South-East'
                    WHEN latitudine < 39.8 AND longitudine < -98.5 THEN 'South-West'
                    ELSE 'Necunoscut'
                END AS eticheta, 
                COUNT(*) as total 
            FROM accidente 
            $unde 
            GROUP BY eticheta";
}
else{
    $sql = "SELECT severitate::text AS eticheta, COUNT(*) as total FROM accidente $unde GROUP BY severitate";
}
$stmt = $conn->query($sql);
$rezultate = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($rezultate);
?>