<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration


$host = "sql207.infinityfree.com";
$user = "if0_42226342";
$password = "VqIUuAIZ38T0f8";
$dbname = "if0_42226342_allonone";

// Create connection with error handling
try {
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die(json_encode(['success' => false, 'error' => $e->getMessage()]));
}

// Helper functions with improved type safety
function getTableName(string $type): string {
    $tables = [
        'hotel' => 'hotels',
        'tour' => 'tours',
        'ride' => 'rides'
    ];
    
    if (!array_key_exists($type, $tables)) {
        throw new InvalidArgumentException("Invalid type: $type");
    }
    
    return $tables[$type];
}

function getIDColumn(string $type): string {
    $columns = [
        'hotel' => 'hotelID',
        'tour' => 'tourID',
        'ride' => 'rideID'
    ];
    
    if (!array_key_exists($type, $columns)) {
        throw new InvalidArgumentException("Invalid type: $type");
    }
    
    return $columns[$type];
}

// Main request handler
try {
    // Validate action parameter
    $action = $_GET['action'] ?? '';
    if (empty($action)) {
        throw new InvalidArgumentException("Action parameter is required");
    }

    // Prepare statement function
    function executeQuery(mysqli $conn, string $sql, array $params = []): array {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
        } else {
            $data = [];
        }
        
        $stmt->close();
        return $data;
    }

    switch ($action) {
        case 'all_hotels':
            $hotels = executeQuery($conn, 
                "SELECT hotelID, name, location, pricePerNight, featured 
                 FROM hotels 
                 WHERE status IN ('approved', 'disabled')");
            echo json_encode($hotels);
            break;

        case 'featured':
            $featuredHotels = executeQuery($conn,
                "SELECT hotelID, name, location, pricePerNight 
                 FROM hotels 
                 WHERE featured = 'YES' AND status = 'approved'");
            echo json_encode($featuredHotels);
            break;

        case 'add_featured':
            $hotelID = $_GET['hotelID'] ?? null;
            if (!$hotelID || !is_numeric($hotelID)) {
                throw new InvalidArgumentException("Valid hotelID is required");
            }
            
            executeQuery($conn,
                "UPDATE hotels SET featured = 'YES' 
                 WHERE hotelID = ? AND status = 'approved'",
                [$hotelID]);
            
            echo json_encode(['success' => true]);
            break;

        case 'remove_featured':
            $hotelID = $_GET['hotelID'] ?? null;
            if (!$hotelID || !is_numeric($hotelID)) {
                throw new InvalidArgumentException("Valid hotelID is required");
            }
            
            executeQuery($conn,
                "UPDATE hotels SET featured = NULL 
                 WHERE hotelID = ?",
                [$hotelID]);
            
            echo json_encode(['success' => true]);
            break;

        case 'get_all_for_exclusive':
            $services = executeQuery($conn, "
                SELECT 'hotel' as type, hotelID as id, name, pricePerNight as price, 
                       IF(exclusive_offer = 'YES', 'YES', 'NO') as exclusive_offer 
                FROM hotels
                WHERE status = 'approved'
                UNION
                SELECT 'tour' as type, tourID as id, provider_name as name, price, 
                       IF(exclusive_offer = 'YES', 'YES', 'NO') as exclusive_offer 
                FROM tours
                WHERE status = 'approved'
                UNION
                SELECT 'ride' as type, rideID as id, provider_name as name, 0 as price, 
                       IF(exclusive_offer = 'YES', 'YES', 'NO') as exclusive_offer 
                FROM rides
                WHERE status = 'approved'
            ");
            echo json_encode($services);
            break;

        case 'toggle_exclusive':
            $type = $_GET['type'] ?? '';
            $id = $_GET['id'] ?? '';
            $value = $_GET['value'] ?? '';
            
            if (!in_array($type, ['hotel', 'tour', 'ride'])) {
                throw new InvalidArgumentException("Invalid service type");
            }
            
            if (!is_numeric($id)) {
                throw new InvalidArgumentException("Invalid ID");
            }
            
            if (!in_array($value, ['YES', 'NO'])) {
                throw new InvalidArgumentException("Invalid value");
            }
            
            $table = getTableName($type);
            $idColumn = getIDColumn($type);
            $setValue = $value === 'YES' ? 'YES' : NULL;
            
            // Verify service exists and is approved
            $exists = executeQuery($conn,
                "SELECT 1 FROM $table WHERE $idColumn = ? AND status = 'approved'",
                [$id]);
                
            if (empty($exists)) {
                throw new Exception("Service not found or not approved");
            }
            
            executeQuery($conn,
                "UPDATE $table SET exclusive_offer = ? WHERE $idColumn = ?",
                [$setValue, $id]);
            
            echo json_encode(['success' => true]);
            break;

        case 'add_exclusive':
            $type = $_GET['type'] ?? '';
            $id = $_GET['id'] ?? '';

            if (!in_array($type, ['hotel', 'tour', 'ride']) || !is_numeric($id)) {
                throw new InvalidArgumentException("Invalid parameters");
            }

            $table = getTableName($type);
            $idColumn = getIDColumn($type);

            $result = executeQuery($conn,
                "UPDATE $table SET exclusive_offer = 'YES' WHERE $idColumn = ?",
                [$id]);

            echo "SUCCESS";
            break;

        case 'remove_exclusive':
            $type = $_GET['type'] ?? '';
            $id = $_GET['id'] ?? '';

            if (!in_array($type, ['hotel', 'tour', 'ride']) || !is_numeric($id)) {
                throw new InvalidArgumentException("Invalid parameters");
            }

            $table = getTableName($type);
            $idColumn = getIDColumn($type);

            $result = executeQuery($conn,
                "UPDATE $table SET exclusive_offer = NULL WHERE $idColumn = ?",
                [$id]);

            echo "SUCCESS";
            break;
  
    
            }

} catch (InvalidArgumentException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'type' => 'client_error']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'type' => 'server_error']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
