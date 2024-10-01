<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        session_start(); // Start the session if not already started

        include "inc/headproduct.inc.php";
        include 'lib/connection.php'; // Ensure you include your database connection here

        $isLoggedIn = isset($_SESSION['user_id']); // true if logged in, false otherwise

        // Pagination setup
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $itemsPerPage = 50; // 50 records per page
        $offset = ($page - 1) * $itemsPerPage;

        // Sorting setup
        $sortBy = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'newest';

        // Capture the selected location (if any) from the URL
        $selectedLocation = isset($_GET['deal_category']) ? $conn->real_escape_string($_GET['deal_category']) : '';

        // Capture the search query (if any)
        $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

        // Determine the ORDER BY clause based on sorting selection
        $orderByClause = "ORDER BY ";
        switch ($sortBy) {
            case 'price_low_high':
                $orderByClause .= "p.resalePrice ASC";
                break;
            case 'price_high_low':
                $orderByClause .= "p.resalePrice DESC";
                break;
            case 'oldest':
                $orderByClause .= "p.transactionDate ASC";
                break;
            case 'newest':
            default:
                $orderByClause .= "p.transactionDate DESC";
                break;
        }

        // Construct WHERE clause for search or location filtering
        $whereClause = 'WHERE 1=1 '; // Default condition to always be true
        
        if (!empty($search)) {
            // If search is provided, use search terms
            $searchTerms = explode(' ', strtolower($search));
            $searchClauses = [];
            foreach ($searchTerms as $term) {
                $searchClauses[] = "(LOWER(p.flatType) LIKE '%$term%' OR 
                                    LOWER(l.streetName) LIKE '%$term%' OR 
                                    LOWER(l.town) LIKE '%$term%' OR 
                                    LOWER(l.block) LIKE '%$term%')";
            }
            $whereClause .= " AND (" . implode(' OR ', $searchClauses) . ")";
        }
        
        // Apply location filtering if a location is selected
        if (!empty($selectedLocation)) {
            $whereClause .= " AND LOWER(l.town) = LOWER('$selectedLocation')";
        }

        // Query for property table with search or location, sorting, and pagination
        $propertyQuery = "SELECT p.flatType, 
                                CONCAT(l.streetName, ' Block ', l.block) AS locationName,
                                p.resalePrice, 
                                p.transactionDate 
                        FROM Property p
                        JOIN Location l ON p.locationID = l.locationID
                        $whereClause
                        $orderByClause
                        LIMIT $itemsPerPage OFFSET $offset";
        $propertyResult = $conn->query($propertyQuery);

        if (!$propertyResult) {
            die("Query failed: " . $conn->error);
        }

        // Get total number of properties
        $totalQuery = "SELECT COUNT(*) as total 
                    FROM Property p
                    JOIN Location l ON p.locationID = l.locationID
                    $whereClause";
        $totalResult = $conn->query($totalQuery);
        $totalProperties = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($totalProperties / $itemsPerPage);

        // Fetch all distinct locations from the database for the dynamic location list
        $locationQuery = "SELECT DISTINCT town FROM Location ORDER BY town ASC";
        $locationResult = $conn->query($locationQuery);
    ?>

    <title>Properties</title>
    <link rel="stylesheet" href="css/product.css">
    <script defer src="js/product.js"></script>
</head>

<body>
    <main class="product-container mt-3 mb-5">
        <?php include "inc/searchnav.inc.php"; ?>

        <div class="row mt-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-10 title"><h2>Properties</h2></div>
        </div>

        <!-- MAIN BODY -->
        <div class="row">
            <!-- SIDE BAR -->
            <div class="col-lg-2 mt-3 side-bar">
                <div class="Deals">
                    <h4>Locations</h4>
                    <ul>
                    <?php
                    // Display the dynamic list of locations
                    if ($locationResult->num_rows > 0) {
                        while ($row = $locationResult->fetch_assoc()) {
                            $town = htmlspecialchars($row['town']);  // Sanitize output
                            // Generate a link for each town/location
                            echo "<li><a href='index.php?deal_category=" . urlencode($town) . "'>" . $town . "</a></li>";
                        }
                    } else {
                        echo "<li>No locations available</li>";
                    }
                    ?>
                    </ul>
                </div>
                <div class="category">
                    <h4>Agents</h4>
                    <ul>
                        <li><a href="index.php?category=Agent Lee">Agent Lee</a></li>
                    </ul>
                </div>
            </div>


            <!-- MAIN BAR-->
            <div class="col-sm-12 col-lg-10 mt-3 main-content">
                <div class="filter-container">
                    <ul class="grid-container">
                        <li class="active"><i class="fa-sharp fa-solid fa-grip icon"></i></li>
                        <li><i class="fa-sharp fa-solid fa-list icon"></i></li>
                    </ul>
                    <div class="drop-down">
                        <label for="filter-type">Sort By:</label>
                        <select class="filter-type" id="filter-type">
                            <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest to Oldest</option>
                            <option value="oldest" <?php echo $sortBy == 'oldest' ? 'selected' : ''; ?>>Oldest to Newest</option>
                            <option value="price_low_high" <?php echo $sortBy == 'price_low_high' ? 'selected' : ''; ?>>Price: low to high</option>
                            <option value="price_high_low" <?php echo $sortBy == 'price_high_low' ? 'selected' : ''; ?>>Price: high to low</option>
                        </select>
                    </div>
                </div>

                <!-- Property Listings Table -->
                <div class="row mt-5">
                    <div class="col-12">
                        <h3>Recent Property Listings</h3>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Flat Type</th>
                                    <th>Location</th>
                                    <th>Resale Price</th>
                                    <th>Transaction Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($propertyResult->num_rows > 0) {
                                    while($row = $propertyResult->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['flatType']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['locationName']) . "</td>";
                                        echo "<td>$" . number_format($row['resalePrice']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['transactionDate']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No properties found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="row page-container">
                    <div class="col-xs-12 col-md-4 page-detail">
                        <span><?php echo ($offset + 1) . " - " . min($offset + $itemsPerPage, $totalProperties) . " property(s) of " . $totalProperties; ?></span>
                    </div>
                    <div class="col-xs-12 col-md-8 page-list">
                        <?php
                        $pagesToShow = 5; // Number of page links to show
                        $startPage = max($page - floor($pagesToShow / 2), 1);
                        $endPage = min($startPage + $pagesToShow - 1, $totalPages);

                        if ($startPage > 1) {
                            echo "<a href='?page=1&sort=$sortBy'><span>First</span></a>";
                            if ($startPage > 2) {
                                echo "<span>...</span>";
                            }
                        }

                        if ($page > 1) {
                            echo "<a href='?page=" . ($page - 1) . "&sort=$sortBy'><i class='fa-solid fa-chevron-left'></i><span>Previous</span></a>";
                        }

                        for ($i = $startPage; $i <= $endPage; $i++) {
                            if ($i == $page) {
                                echo "<span class='active'>$i</span>";
                            } else {
                                echo "<a href='?page=$i&sort=$sortBy'><span>$i</span></a>";
                            }
                        }

                        if ($page < $totalPages) {
                            echo "<a href='?page=" . ($page + 1) . "&sort=$sortBy'><span>Next</span><span class='arrow-right'><i class='fa-solid fa-chevron-right'></i></span></a>";
                        }

                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo "<span>...</span>";
                            }
                            echo "<a href='?page=$totalPages&sort=$sortBy'><span>Last</span></a>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>

    <script>
        // Function to update URL parameters by replacing all existing ones
        function updateURLParameter(param, value, resetOthers = true) {
            const url = new URL(window.location.href);
            if (resetOthers) {
                // Clear out all existing parameters except the new one
                url.search = ''; 
            }
            url.searchParams.set(param, value);
            window.history.replaceState({}, '', url);
        }

        // Sorting functionality
        document.getElementById('filter-type').addEventListener('change', function() {
            updateURLParameter('sort', this.value, true); // Overwrite all parameters
            location.reload();
        });

        // Search functionality
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.querySelector('input[name="search"]').value;
            updateURLParameter('search', searchTerm, true); // Overwrite all parameters
            location.reload();
        });

        // Handle clicking on location links
        document.querySelectorAll('.deal-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const location = e.target.innerText; // Get the text of the clicked location
                updateURLParameter('deal_category', location, true); // Overwrite all parameters with only location
                location.reload(); // Reload the page with the new filter
            });
        });

    </script>
</body>
</html>
