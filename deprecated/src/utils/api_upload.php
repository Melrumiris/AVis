<?php
header('Content-Type: application/json');

try {
    $conn = new PDO("pgsql:host=localhost;port=5432;dbname=postgres", "postgres", "password123");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 1. Import din CSV
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
            $file = $_FILES['csv_file']['tmp_name'];
            if (($handle = fopen($file, "r")) !== FALSE) {
                $stmt = $conn->prepare("INSERT INTO accidente (id_accident, data_ora, severitate, latitudine, longitudine) VALUES (?, ?, ?, ?, ?)");
                fgetcsv($handle); 
                $id_curent = time(); 
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $id_accident = $id_curent++; 
                    $stmt->execute([$id_accident, $data[0], $data[1], $data[2], $data[3]]);
                }
                fclose($handle);
                echo json_encode(["status" => "success", "message" => "Fișier CSV importat cu succes!"]);
                exit;
            }
        } 
        // 2. Inserare Manuală
        else if (isset($_POST['data_ora'], $_POST['severitate'], $_POST['latitudine'], $_POST['longitudine'])) {
            $id_accident = time();             
            $stmt = $conn->prepare("INSERT INTO accidente (id_accident, data_ora, severitate, latitudine, longitudine) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_accident, $_POST['data_ora'], $_POST['severitate'], $_POST['latitudine'], $_POST['longitudine']]);
            echo json_encode(["status" => "success", "message" => "Accident adăugat manual cu succes!"]);
            exit;
        }
    }
    
    echo json_encode(["status" => "error", "message" => "Date incomplete primite de server."]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Eroare SQL: " . $e->getMessage()]);
}
?>