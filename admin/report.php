<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';
?>

<div class="container">
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <title>Report</title>
        <!-- Bootstrap JS for collapsible behavior -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </head>
    <body>

        <div class="accordion" id="reportAccordion" style="padding-top: 150px;">

            <!-- Properties by Location Section -->
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h4 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Properties by Location
                        </button>
                    </h4>
                </div>

                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#reportAccordion">
                    <div class="card-body">
                        <?php
                        // Properties by Location
                        $result = $conn->query("SELECT Location.town AS 'Location', COUNT(Property.propertyID) AS 'Total Properties'
                                                FROM Property
                                                JOIN Location ON Property.locationID = Location.locationID
                                                GROUP BY Location.town
                                                ORDER BY Location.town ASC");
                        echo '<table class="table table-bordered">';
                        echo '<tr><th>Location</th><th>Total Properties</th></tr>';
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr><td>{$row['Location']}</td><td>{$row['Total Properties']}</td></tr>";
                        }
                        echo '</table>';
                        ?>
                    </div>
                </div>
            </div>

            <!-- Average Property Price by Location Section -->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h4 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Average Property Price by Location
                        </button>
                    </h4>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#reportAccordion">
                    <div class="card-body">
                        <?php
                        // Average Property Price by Location
                        $result = $conn->query("SELECT Location.town AS 'Location', AVG(Property.resalePrice) AS 'Average Price'
                                                FROM Property
                                                JOIN Location ON Property.locationID = Location.locationID
                                                GROUP BY Location.town
                                                ORDER BY AVG(Property.resalePrice) DESC");

                        echo '<table class="table table-bordered">';
                        echo '<tr><th>Location</th><th>Average Property Price</th></tr>';
                        while ($row = $result->fetch_assoc()) {
                            $formatted_price = '$' . number_format($row['Average Price'], 2);
                            echo "<tr><td>{$row['Location']}</td><td>{$formatted_price}</td></tr>";
                        }
                        echo '</table>';
                        ?>
                    </div>
                </div>
            </div>

            <!-- Median Property Prices by Location Section -->
            <div class="card">
                <div class="card-header" id="headingThree">
                    <h4 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Median Property Prices by Location
                        </button>
                    </h4>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#reportAccordion">
                    <div class="card-body">
                        <?php
                        // Median Property Prices by Location
                        $result = $conn->query("SELECT Location.town AS 'Location', AVG(resalePrice) AS 'Median Price'
                                                FROM Property
                                                JOIN Location ON Property.locationID = Location.locationID
                                                GROUP BY Location.town
                                                HAVING COUNT(resalePrice) % 2 = 0");

                        echo '<table class="table table-bordered">';
                        echo '<tr><th>Location</th><th>Median Price</th></tr>';
                        while ($row = $result->fetch_assoc()) {
                            $formatted_price = '$' . number_format($row['Median Price'], 2);
                            echo "<tr><td>{$row['Location']}</td><td>{$formatted_price}</td></tr>";
                        }
                        echo '</table>';
                        ?>
                    </div>
                </div>
            </div>

            <!-- Top Agents by Reviews Section -->
            <div class="card">
                <div class="card-header" id="headingFour">
                    <h4 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            Top Agents by Reviews
                        </button>
                    </h4>
                </div>
                <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#reportAccordion">
                    <div class="card-body">
                        <?php
                        // Top Agents by Reviews
                        $result = $conn->query("SELECT CONCAT(Users.fname, ' ', Users.lname) AS 'Agent Name', 
                                                ROUND(AVG(agentReview.rating), 2) AS 'Average Rating'
                                                FROM agentReview
                                                JOIN Agent ON agentReview.agentID = Agent.agentID
                                                JOIN Users ON Agent.userID = Users.userID
                                                GROUP BY agentReview.agentID
                                                ORDER BY AVG(agentReview.rating) DESC");

                        echo '<table class="table table-bordered">';
                        echo '<tr><th>Agent Name</th><th>Average Rating</th></tr>';
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr><td>{$row['Agent Name']}</td><td>{$row['Average Rating']}</td></tr>";
                        }
                        echo '</table>';
                        ?>
                    </div>
                </div>
            </div>

        </div>

    </body>
    </html>
</div>
