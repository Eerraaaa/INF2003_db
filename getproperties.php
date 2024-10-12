<?php
include 'lib/connection.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$dealCategory = isset($_GET['deal_category']) ? $_GET['deal_category'] : '';
$flatType = isset($_GET['flat_type']) ? $_GET['flat_type'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$itemsPerPage = 50;
$offset = ($page - 1) * $itemsPerPage;

$whereClause = ["p.approvalStatus = 'approved'"]; // Only show approved properties
$params = [];

// Filter by availability
if ($filter === 'sold') {
    $whereClause[] = "p.availability = 'sold'";
} elseif ($filter === 'available') {
    $whereClause[] = "p.availability = 'available'";
}

// Filter by deal category (location)
if (!empty($dealCategory)) {
    $whereClause[] = "l.town = ?";
    $params[] = $dealCategory;
}

// Filter by flat type
if (!empty($flatType) && $flatType !== 'all') {
    $whereClause[] = "p.flatType = ?";
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

    $whereClause[] = "(" . implode(" AND ", $searchClauses) . ")";
}

$whereClause = implode(" AND ", $whereClause);

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

$query = "SELECT p.propertyID, p.flatType, 
                 CONCAT(l.town, ' ', l.streetName, ' Block ', l.block) AS locationName,
                 p.resalePrice, 
                 p.transactionDate,
                 p.availability,
                 COALESCE(u.fname, '') AS agentName,
                 COALESCE(u.phone_number, '') AS agentPhone,
                 COALESCE(u.email, '') AS agentEmail
          FROM Property p
          JOIN Location l ON p.locationID = l.locationID
          LEFT JOIN Agent a ON p.agentID = a.agentID  -- Use LEFT JOIN instead of JOIN
          LEFT JOIN Users u ON a.userID = u.userID
          WHERE $whereClause
          $orderBy
          LIMIT ? OFFSET ?";


// Updated Count Query remains the same as before
$countQuery = "SELECT COUNT(*) as total FROM Property p JOIN Location l ON p.locationID = l.locationID WHERE $whereClause";


$countQuery = "SELECT COUNT(*) as total FROM Property p JOIN Location l ON p.locationID = l.locationID WHERE $whereClause";

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

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

$totalPages = ceil($totalProperties / $itemsPerPage);

$response = [
    'properties' => $properties,
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'total' => $totalProperties,
    'start' => $offset + 1,
    'end' => min($offset + $itemsPerPage, $totalProperties)
];

header('Content-Type: application/json');
echo json_encode($response);
?>