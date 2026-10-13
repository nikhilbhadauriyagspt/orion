<?php
/**
 * Complete Database Exporter
 * Generates an SQL dump file for easy import on any machine/laptop.
 */

require_once __DIR__ . '/config/db.php';

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed. Please check config/db.php\n");
}

$dbName = DB_NAME;
$outputFile = __DIR__ . '/orionadvent_db.sql';

$sql = "-- Orion Advent Database Dump\n";
$sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Compatible with cPanel, Localhost & Cloud Servers\n\n";
$sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$sql .= "START TRANSACTION;\n";
$sql .= "SET time_zone = \"+00:00\";\n";
$sql .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
$sql .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
$sql .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
$sql .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

$tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
$tables = [];
while ($row = $tablesStmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    $sql .= "\n-- --------------------------------------------------------\n";
    $sql .= "-- Table structure for table `" . $table . "`\n";
    $sql .= "-- --------------------------------------------------------\n\n";
    $sql .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
    
    $createStmt = $pdo->query("SHOW CREATE TABLE `" . $table . "`");
    $createRow = $createStmt->fetch(PDO::FETCH_NUM);
    $sql .= $createRow[1] . ";\n\n";
    
    // Dump Table Data
    $dataStmt = $pdo->query("SELECT * FROM `" . $table . "`");
    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($rows)) {
        $sql .= "-- Dumping data for table `" . $table . "` (" . count($rows) . " rows)\n";
        
        // Chunk inserts for efficiency
        $chunks = array_chunk($rows, 50);
        foreach ($chunks as $chunk) {
            $columnNames = array_keys($chunk[0]);
            $quotedColumns = array_map(function($c) { return "`" . $c . "`"; }, $columnNames);
            
            $sql .= "INSERT INTO `" . $table . "` (" . implode(", ", $quotedColumns) . ") VALUES\n";
            
            $rowStrings = [];
            foreach ($chunk as $row) {
                // Mask sensitive keys for public git security
                if (isset($row['setting_key'])) {
                    if ($row['setting_key'] === 'google_client_id') {
                        $row['setting_value'] = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
                    } elseif ($row['setting_key'] === 'google_client_secret') {
                        $row['setting_value'] = 'YOUR_GOOGLE_CLIENT_SECRET';
                    } elseif ($row['setting_key'] === 'razorpay_key_secret' && !empty($row['setting_value'])) {
                        $row['setting_value'] = 'YOUR_RAZORPAY_SECRET';
                    }
                }

                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = "NULL";
                    } else {
                        $values[] = $pdo->quote($val);
                    }
                }
                $rowStrings[] = "(" . implode(", ", $values) . ")";
            }
            $sql .= implode(",\n", $rowStrings) . ";\n";
        }
        $sql .= "\n";
    }
}

$sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
$sql .= "COMMIT;\n\n";
$sql .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
$sql .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
$sql .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

file_put_contents($outputFile, $sql);

echo "Database export complete!\n";
echo "Saved to: " . $outputFile . "\n";
echo "Total Tables: " . count($tables) . " (" . implode(', ', $tables) . ")\n";
echo "File Size: " . round(filesize($outputFile) / 1024, 2) . " KB\n";
