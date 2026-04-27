<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

check_role(['admin']);

/**
 * Basic Database Backup Utility
 */
try {
    $tables = array();
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $return = "";
    foreach ($tables as $table) {
        $result = $pdo->query("SELECT * FROM $table");
        $num_fields = $result->columnCount();

        $return .= "DROP TABLE IF EXISTS $table;";
        
        $row2 = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
        $return .= "\n\n" . $row2[1] . ";\n\n";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $return .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) {
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= '""';
                }
                if ($j < ($num_fields - 1)) {
                    $return .= ",";
                }
            }
            $return .= ");\n";
        }
        $return .= "\n\n\n";
    }

    // Save file
    $backup_file = 'db-backup-' . time() . '.sql';
    $handle = fopen($backup_file, 'w+');
    fwrite($handle, $return);
    fclose($handle);

    // Provide download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($backup_file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_file));
    readfile($backup_file);
    
    // Cleanup temporary file
    unlink($backup_file);
    exit();

} catch (Exception $e) {
    echo "Error during backup: " . $e->getMessage();
}
?>
