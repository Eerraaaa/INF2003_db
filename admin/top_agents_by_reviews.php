<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';
include 'lib/mongodb.php';

try {
    $mongodb = MongoDBConnection::getInstance();
    $sortOrder = isset($_POST['agent_sort']) ? $_POST['agent_sort'] : 'DESC';
    
    // Get all agents with their ratings, excluding NULL or 0 ratings
    $pipeline = [
        [
            '$match' => [
                'rating' => [
                    '$exists' => true,
                    '$ne' => null,
                    '$ne' => 0,
                    '$ne' => "NULL"
                ]
            ]
        ],
        [
            '$sort' => [
                'rating' => ($sortOrder === 'DESC') ? -1 : 1
            ]
        ]
    ];

    $command = new MongoDB\Driver\Command([
        'aggregate' => 'agent',
        'pipeline' => $pipeline,
        'cursor' => new stdClass
    ]);

    $cursor = $mongodb->getConnection()->executeCommand('realestate_db', $command);
    
    // Store the results using agentID as key to prevent duplicates
    $agentRatings = [];
    foreach ($cursor as $agent) {
        $agentData = json_decode(json_encode($agent), true);
        if (isset($agentData['userID']) && isset($agentData['agentID'])) {
            // Get user details from MySQL
            $stmt = $conn->prepare("SELECT CONCAT(fname, ' ', lname) as fullName FROM Users WHERE userID = ?");
            $stmt->bind_param("i", $agentData['userID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            
            if ($userData && !isset($agentRatings[$agentData['agentID']])) {
                $agentRatings[$agentData['agentID']] = [
                    'Agent Name' => $userData['fullName'],
                    'Average Rating' => number_format($agentData['rating'], 2)
                ];
            }
        }
    }
    
    // Convert to indexed array for display
    $agentRatings = array_values($agentRatings);
?>

<div class="container" style="padding-top: 150px;">
    <h2>Top Agents by Reviews</h2>

    <a href="report.php" class="btn btn-secondary mb-3">Back to Reports</a>

    <form method="post">
        <label for="agent_sort">Sort By:</label>
        <select name="agent_sort" id="agent_sort" onchange="this.form.submit()">
            <option value="DESC" <?php if ($sortOrder == 'DESC') echo 'selected'; ?>>Highest Rated</option>
            <option value="ASC" <?php if ($sortOrder == 'ASC') echo 'selected'; ?>>Lowest Rated</option>
        </select>
    </form>

    <table class="table table-bordered">
        <tr><th>Agent Name</th><th>Average Rating</th></tr>
        <?php if (!empty($agentRatings)): ?>
            <?php foreach ($agentRatings as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Agent Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Average Rating']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="2" class="text-center">No rated agents found</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}