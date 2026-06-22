<?php
session_start();
require_once 'db_connection.php'; // Make sure this path is correct

// Set proper headers first
header('Content-Type: application/json');

// Check authorization before any output
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $response = ['success' => false, 'error' => 'Invalid action'];

        switch ($action) {
            case 'get_requests':
                try {
                    // Get all cancellation requests with user details
                    $stmt = $pdo->prepare("
                        SELECT 
                            b.bookingID, 
                            b.cancelRequest,
                            b.touristID as user_id,
                            b.serviceType,
                            b.status,
                            t.fullName,
                            t.phoneNumber,
                            a.email,
                            CASE 
                                WHEN b.serviceType = 'hotel' THEN h.name
                                WHEN b.serviceType = 'tour' THEN tp.title
                                WHEN b.serviceType = 'ride' THEN r.provider_name
                                ELSE 'Service'
                            END as serviceName
                        FROM bookings b
                        JOIN tourists t ON b.touristID = t.touristID
                        JOIN accounts a ON t.touristID = a.accountID
                        LEFT JOIN hotels h ON b.serviceType = 'hotel' AND b.serviceID = h.hotelID
                        LEFT JOIN tour_packages tp ON b.serviceType = 'tour' AND b.serviceID = tp.packageID
                        LEFT JOIN rides r ON b.serviceType = 'ride' AND b.serviceID = r.rideID
                        WHERE b.cancelRequest IS NOT NULL AND b.cancelRequest != 'no'
                    ");
                    $stmt->execute();
                    $allRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Parse JSON data with proper error handling
                    $parseRequestData = function($item) {
                        $data = json_decode($item['cancelRequest'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            return null;
                        }
                        
                        return [
                            'bookingID' => $item['bookingID'],
                            'user_id' => $item['user_id'],
                            'fullName' => $item['fullName'],
                            'phoneNumber' => $item['phoneNumber'],
                            'email' => $item['email'],
                            'serviceType' => $item['serviceType'],
                            'serviceName' => $item['serviceName'],
                            'status' => $data['status'] ?? 'pending',
                            'type' => $data['type'] ?? '',
                            'reason' => $data['reason'] ?? '',
                            'timestamp' => $data['timestamp'] ?? '',
                            'admin_rejection_reason' => $data['admin_rejection_reason'] ?? $data['admin_message'] ?? '',
                            'userName' => $item['fullName'] ?: 'User #' . $item['user_id']
                        ];
                    };

                    $parsedRequests = array_filter(array_map($parseRequestData, $allRequests));
                    
                    // Separate pending and processed requests
                    $pendingRequests = array_filter($parsedRequests, function($req) {
                        return isset($req['status']) && $req['status'] === 'waiting_approval';
                    });
                    
                    $processedRequests = array_filter($parsedRequests, function($req) {
                        return isset($req['status']) && ($req['status'] === 'Approved' || $req['status'] === 'Rejected');
                    });

                    echo json_encode([
                        'success' => true,
                        'pendingRequests' => array_values($pendingRequests),
                        'processedRequests' => array_values($processedRequests)
                    ]);
                    exit();

                } catch (PDOException $e) {
                    error_log("Database error in get_requests: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => 'Database error: ' . $e->getMessage()
                    ]);
                    exit();
                }
                break;

            case 'process_request':
                $bookingID = $_POST['bookingID'] ?? null;
                $userID = $_POST['userID'] ?? null;
                $decision = $_POST['decision'] ?? '';
                $rejectionReason = trim($_POST['rejection_reason'] ?? $_POST['admin_rejection_reason'] ?? '');

                if (!$bookingID || !$userID || !in_array($decision, ['approve', 'reject'])) {
                    $response = ['success' => false, 'error' => 'Invalid parameters'];
                    break;
                }

                // Start transaction
                $pdo->beginTransaction();

                try {
                    // Get current data with lock
                    $stmt = $pdo->prepare("SELECT cancelRequest FROM bookings WHERE bookingID = ? FOR UPDATE");
                    $stmt->execute([$bookingID]);
                    $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$currentData) {
                        throw new Exception('Booking not found');
                    }

                    $cancellationData = json_decode($currentData['cancelRequest'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('Invalid cancellation data');
                    }

                    // Update cancellation data
                    $cancellationData['status'] = ($decision === 'approve') ? 'Approved' : 'Rejected';
                    $cancellationData['timestamp'] = date('Y-m-d H:i:s');
                    
                    if ($decision === 'reject') {
                        if (empty($rejectionReason)) {
                            throw new Exception('Rejection reason is required');
                        }
                        $cancellationData['admin_rejection_reason'] = $rejectionReason;
                        $cancellationData['admin_message'] = $rejectionReason;
                    }

                    $newStatus = ($decision === 'approve') ? 'cancelled' : 'Pending';

                    // Update database
                    $stmt = $pdo->prepare("UPDATE bookings SET cancelRequest = ?, status = ? WHERE bookingID = ?");
                    $stmt->execute([
                        json_encode($cancellationData, JSON_UNESCAPED_UNICODE),
                        $newStatus,
                        $bookingID
                    ]);

                    $pdo->commit();
                    
                    $response = [
                        'success' => true,
                        'message' => 'Request processed successfully',
                        'new_status' => $newStatus,
                        'updated_data' => $cancellationData
                    ];
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $response = ['success' => false, 'error' => $e->getMessage()];
                }
                break;

            default:
                $response = ['success' => false, 'error' => 'Invalid action'];
        }

        echo json_encode($response);
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit();
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    exit();
}

// Fallback response
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>