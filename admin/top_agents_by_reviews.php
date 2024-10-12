<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';
?>

<div class="container" style="padding-top: 150px;">
    <h2>Top Agents by Reviews</h2>

    <!-- Sorting Dropdown Form -->
    <form method="post">
        <label for="agent_sort">Sort By:</label>
        <select name="agent_sort" id="agent_sort" onchange="this.form.submit()">
            <option value="DESC" <?php if (isset($_POST['agent_sort']) && $_POST['agent_sort'] == 'DESC') echo 'selected'; ?>>Highest Rated</option>
            <option value="ASC" <?php if (isset($_POST['agent_sort']) && $_POST['agent_sort'] == 'ASC') echo 'selected'; ?>>Lowest Rated</option>
        </select>
    </form>

    <?php
    $sortOrder = isset($_POST['agent_sort']) ? $_POST['agent_sort'] : 'DESC';

    $result = $conn->query("SELECT CONCAT(Users.fname, ' ', Users.lname) AS 'Agent Name', 
                            ROUND(AVG(agentReview.rating), 2) AS 'Average Rating'
                            FROM agentReview
                            JOIN Agent ON agentReview.agentID = Agent.agentID
                            JOIN Users ON Agent.userID = Users.userID
                            GROUP BY agentReview.agentID
                            ORDER BY AVG(agentReview.rating) $sortOrder");

    echo '<table class="table table-bordered">';
    echo '<tr><th>Agent Name</th><th>Average Rating</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Agent Name']}</td><td>{$row['Average Rating']}</td></tr>";
    }
    echo '</table>';
    ?>
</div>
