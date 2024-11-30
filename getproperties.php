<?php
include 'lib/connection.php';
include 'lib/mongodb.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$dealCategory = isset($_GET['deal_category']) ? $_GET['deal_category'] : '';
$flatType = isset($_GET['flat_type']) ? $_GET['flat_type'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$itemsPerPage = 50;
$offset = ($page - 1) * $itemsPerPage;

$params = [];

// Fix the WHERE clauses
if ($filter === 'sold') {
    $whereClause = "WHERE p.availability = 'sold'";
} elseif ($filter === 'available') {
    $whereClause = "WHERE p.availability = 'available' AND p.approvalStatus = 'approved'";
} else {
    // For 'all' properties, we need to explicitly check approval status for available properties
    $whereClause = "WHERE (p.availability = 'sold' OR (p.availability = 'available' AND p.approvalStatus = 'approved'))";
}

// Filter by deal category (location)
if (!empty($dealCategory)) {
    $whereClause .= " AND l.town = ?";
    $params[] = $dealCategory;
}

// Filter by flat type
if (!empty($flatType) && $flatType !== 'all') {
    $whereClause .= " AND p.flatType = ?";
    $params[] = $flatType;
}

// Search logic
if (!empty($search)) {
    $searchTerms = explode(' ', strtolower($search));
    $searchClauses = [];

    foreach ($searchTerms as $term) {
        $termClauses = [];
        $termClauses[] = "LOWER(p.flatType) LIKE ?";
        $termClauses[] = "LOWER(l.streetName) LIKE ?";
        $termClauses[] = "LOWER(l.town) LIKE ?";
        $termClauses[] = "LOWER(l.block) LIKE ?";

        if (is_numeric($term)) {
            $termClauses[] = "l.block = ?";
            $params = array_merge($params, ["%$term%", "%$term%", "%$term%", "%$term%", $term]);
        } else {
            $params = array_merge($params, ["%$term%", "%$term%", "%$term%", "%$term%"]);
        }

        $searchClauses[] = "(" . implode(" OR ", $termClauses) . ")";
    }

    $whereClause .= " AND (" . implode(" AND ", $searchClauses) . ")";
}

// Sorting
$orderBy = "ORDER BY ";
switch ($sortBy) {
    case 'oldest':
        $orderBy .= "p.transactionDate ASC";
        break;
    case 'price_low_high':
        $orderBy .= "p.resalePrice ASC";
        break;
    case 'price_high_low':
        $orderBy .= "p.resalePrice DESC";
        break;
    case 'newest':
    default:
        $orderBy .= "p.transactionDate DESC";
        break;
}

// Updated main query to include approvalStatus
$query = "SELECT p.propertyID, p.flatType, p.agentID, p.approvalStatus,
                 CONCAT(l.town, ' ', l.streetName, ' Block ', l.block) AS locationName,
                 p.resalePrice, p.transactionDate, p.availability
          FROM Property p
          JOIN Location l ON p.locationID = l.locationID
          $whereClause
          $orderBy
          LIMIT ? OFFSET ?";

$countQuery = "SELECT COUNT(*) as total FROM Property p JOIN Location l ON p.locationID = l.locationID $whereClause";

$stmt = $conn->prepare($query);
$countStmt = $conn->prepare($countQuery);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types . "ii", ...array_merge($params, [$itemsPerPage, $offset]));
    $countStmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $itemsPerPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$countStmt->execute();
$countResult = $countStmt->get_result();
$totalProperties = $countResult->fetch_assoc()['total'];

// Initialize MongoDB connection
try {
    $mongodb = MongoDBConnection::getInstance();
} catch (Exception $e) {
    error_log("MongoDB Connection Error: " . $e->getMessage());
    $mongodb = null;
}

$properties = [];
while ($row = $result->fetch_assoc()) {
    // Additional safety check
    if ($row['availability'] === 'available' && $row['approvalStatus'] !== 'approved') {
        continue; // Skip this property
    }

    if ($mongodb) {
        try {
            $agentInfo = $mongodb->findOne('agent', ['agentID' => (int)$row['agentID']]);
            
            if ($agentInfo) {
                $userQuery = "SELECT fname, lname, email, phone_number FROM Users WHERE userID = ?";
                $userStmt = $conn->prepare($userQuery);
                $userId = (int)$agentInfo['userID'];
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $userDetails = $userResult->fetch_assoc();
                
                if ($userDetails) {
                    $row['agentName'] = $userDetails['fname'] . ' ' . $userDetails['lname'];
                    $row['agentPhone'] = $userDetails['phone_number'];
                    $row['agentEmail'] = $userDetails['email'];
                    $row['agentArea'] = $agentInfo['areaInCharge'] ?? '';
                } else {
                    $row['agentName'] = '';
                    $row['agentPhone'] = '';
                    $row['agentEmail'] = '';
                    $row['agentArea'] = $agentInfo['areaInCharge'] ?? '';
                }
            } else {
                $row['agentName'] = '';
                $row['agentPhone'] = '';
                $row['agentEmail'] = '';
                $row['agentArea'] = '';
            }
        } catch (Exception $e) {
            error_log("Error fetching agent data: " . $e->getMessage());
            $row['agentName'] = '';
            $row['agentPhone'] = '';
            $row['agentEmail'] = '';
            $row['agentArea'] = '';
        }
    } else {
        $row['agentName'] = '';
        $row['agentPhone'] = '';
        $row['agentEmail'] = '';
        $row['agentArea'] = '';
    }
    
    // Price formatting without decimal places
    if (is_numeric($row['resalePrice'])) {
        $row['resalePrice'] = '$' . number_format($row['resalePrice'], 0, '.', ',');
    } else {
        $row['resalePrice'] = 'Price not available';
    }
    
    // Only format the date if it's not for an available property
    if ($row['availability'] === 'available') {
        $row['transactionDate'] = '';
    } else if ($row['transactionDate']) {
        $row['transactionDate'] = date('Y-m-d', strtotime($row['transactionDate']));
    }
    
    $properties[] = $row;
}

$response = [
    'properties' => $properties,
    'currentPage' => $page,
    'totalPages' => ceil($totalProperties / $itemsPerPage),
    'total' => $totalProperties,
    'start' => $offset + 1,
    'end' => min($offset + $itemsPerPage, $totalProperties)
];

header('Content-Type: application/json');
echo json_encode($response);
?>