<?php
$conn = new PDO("pgsql:host=localhost;port=5432;dbname=postgres", "postgres", "password123");

$sdate = isset($_GET['sdate']) ? $_GET['sdate'] : '';
$fdate = isset($_GET['fdate']) ? $_GET['fdate'] : '';

$unde = "WHERE latitudine IS NOT NULL AND longitudine IS NOT NULL";

if(!empty($sdate)){
    $unde .= " AND data_ora >= '$sdate 00:00:00'";
}
if(!empty($fdate)){
    $unde .= " AND data_ora <= '$fdate 23:59:59'";
}

$sql = "SELECT latitudine AS lat, longitudine AS lng, severitate 
        FROM accidente 
        $unde 
        LIMIT 1500";

$stmt = $conn->query($sql);
$rezultate = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($rezultate);
exit;
?>