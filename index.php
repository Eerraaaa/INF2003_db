<?php
session_start();
include "inc/headproduct.inc.php";
include 'lib/connection.php';

$isLoggedIn = isset($_SESSION['userID']);
$userType = $_SESSION['user_type'] ?? '';
$isBuyer = $isLoggedIn && $userType === 'buyer';
$buyerName = $isBuyer ? ($_SESSION['user_first_name'] ?? 'Buyer') : '';

// Sorting setup
$sortBy = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'newest';

// Capture the selected location (if any) from the URL
$selectedLocation = isset($_GET['deal_category']) ? $conn->real_escape_string($_GET['deal_category']) : '';

// Capture the search query (if any)
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch all distinct locations from the database for the dynamic location list
$locationQuery = "SELECT DISTINCT town FROM Location ORDER BY town ASC";
$locationResult = $conn->query($locationQuery);

// Fetch distinct flat types from the Property table
$flatTypeQuery = "SELECT DISTINCT flatType FROM Property ORDER BY flatType ASC";
$flatTypeResult = $conn->query($flatTypeQuery);

$pageTitle = $isBuyer ? "Welcome, $buyerName" : 'Properties';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/product.css">
    <script defer src="js/product.js"></script>
</head>

<body>
    <main class="product-container mt-3 mb-5">
        <?php include "inc/searchnav.inc.php"; ?>

        <div class="row mt-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-10 title">
                <h2><?php echo $pageTitle; ?></h2>
            </div>
        </div>

        <!-- MAIN BODY -->
        <div class="row">
            <!-- SIDE BAR -->
            <div class="col-lg-2 mt-3 side-bar">
                <div class="Deals">
                    <h4>Locations</h4>
                    <ul>
                        <li><a href="#" data-location="" class="active">ALL LOCATIONS</a></li>
                        <?php
                        if ($locationResult->num_rows > 0) {
                            while ($row = $locationResult->fetch_assoc()) {
                                $town = htmlspecialchars($row['town']);
                                $isActive = ($selectedLocation == $town) ? 'class="active"' : '';
                                echo "<li><a href='#' data-location='" . urlencode($town) . "' $isActive>" . $town . "</a></li>";
                            }
                        } else {
                            echo "<li>No locations available</li>";
                        }
                        ?>
                    </ul>
                </div>
                <?php if ($isBuyer): ?>
                    <div class="category">
                        <h4>Buyer Actions</h4>
                        <ul>

                            <li><a href="buyer/past_trans.php">Past Transactions</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
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
                            <option value="newest">Newest to Oldest</option>
                            <option value="oldest">Oldest to Newest</option>
                            <option value="price_low_high">Price: low to high</option>
                            <option value="price_high_low">Price: high to low</option>
                        </select>
                    </div>
                    <div class="drop-down">
                        <label for="property-type">Properties:</label>
                        <select class="filter-type" id="property-type">
                            <option value="all">ALL PROPERTIES</option>
                            <option value="sold">SOLD PROPERTIES</option>
                            <option value="available">AVAILABLE PROPERTIES</option>
                        </select>
                    </div>
                    <div class="drop-down">
                        <label for="flat-type">Flat Type:</label>
                        <select class="filter-type" id="flat-type">
                            <option value="all">All Flat Types</option>
                            <?php
                            if ($flatTypeResult->num_rows > 0) {
                                while ($row = $flatTypeResult->fetch_assoc()) {
                                    $flatType = htmlspecialchars($row['flatType']);
                                    echo "<option value='$flatType'>$flatType</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Property Listings Table -->
                <div class="row mt-5">
                    <div class="col-12">
                        <h3>Property Listings</h3>
                        <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Flat Type</th>
                                <th>Location</th>
                                <th>Resale Price</th>
                                <th>Transaction Date</th>
                                <th>Availability</th>
                                <th>Agent Name</th> <!-- New column for Agent Name -->
                                <th>Agent Phone Number</th>
                                <th>Agent Email</th>
                                <?php if ($isBuyer): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>

                            <tbody id="propertyTableBody">
                                <!-- Properties will be populated here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="row page-container">
                    <div class="col-xs-12 col-md-4 page-detail">
                        <span id="paginationInfo"></span>
                    </div>
                    <div class="col-xs-12 col-md-8 page-list" id="paginationControls">
                        <!-- Pagination controls will be populated here via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>

    <script>
        <?php if ($isBuyer): ?>

            function addToCart(propertyId) {
                console.log('Adding property to cart:', propertyId); // Debug log
                fetch('buyer/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'propertyID=' + encodeURIComponent(propertyId)
                    })
                    .then(response => {
                        console.log('Response status:', response.status); // Debug log
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data); // Debug log
                        if (data.success) {
                            alert(data.message);
                            updateCartCount();
                        } else {
                            alert(data.message || 'An error occurred while adding the property to the cart.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while adding the property to the cart: ' + error.message);
                    });
            }

            // Add this function to update the cart count in the UI
            function updateCartCount() {
                console.log('Updating cart count'); // Debug log
                fetch('buyer/get_cart_count.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Cart count data:', data); // Debug log
                        document.getElementById('cartCount').textContent = data.count;
                    })
                    .catch(error => console.error('Error updating cart count:', error));
            }

        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', function() {
            const sortSelect = document.getElementById('filter-type');
            const propertyTypeSelect = document.getElementById('property-type');
            const flatTypeSelect = document.getElementById('flat-type');
            const propertyTableBody = document.getElementById('propertyTableBody');
            const paginationInfo = document.getElementById('paginationInfo');
            const paginationControls = document.getElementById('paginationControls');
            const locationLinks = document.querySelectorAll('.Deals a');
            const searchForm = document.querySelector('form');

            let currentFilter = 'all';
            let currentFlatType = 'all';
            let currentSort = sortSelect.value;
            let currentPage = 1;
            let currentLocation = '';
            let currentSearch = '';

            function fetchProperties() {
                const url = `getproperties.php?filter=${currentFilter}&flat_type=${currentFlatType}&sort=${currentSort}&page=${currentPage}&deal_category=${currentLocation}&search=${encodeURIComponent(currentSearch)}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        updatePropertyTable(data.properties);
                        updatePagination(data);
                    })
                    .catch(error => console.error('Error:', error));
            }


            function updatePropertyTable(properties) {
                propertyTableBody.innerHTML = '';
                if (properties.length === 0) {
                    propertyTableBody.innerHTML = '<tr><td colspan="<?php echo $isBuyer ? '8' : '7'; ?>">No properties found</td></tr>';
                } else {
                    properties.forEach(property => {
                        const row = `
                            <tr>
                                <td>${property.flatType}</td>
                                <td>${property.locationName}</td>
                                <td>$${Number(property.resalePrice).toLocaleString()}</td>
                                <td>${property.transactionDate}</td>
                                <td>${property.availability}</td>
                                <td>${property.agentName || ''}</td> 
                                <td>${property.agentPhone || ''}</td>
                                <td>${property.agentEmail || ''}</td>
                                <?php if ($isBuyer): ?>
                                <td>
                                    ${property.availability === 'available' 
                                        ? `<button onclick="addToCart(${property.propertyID})" class="btn btn-sm btn-primary add-to-cart">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>`
                                        : 'Not Available'}
                                </td>
                                <?php endif; ?>
                            </tr>
                        `;
                        propertyTableBody.innerHTML += row;
                    });
                }
            }





            function updatePagination(data) {
                paginationInfo.textContent = `${data.start} - ${data.end} of ${data.total} properties`;

                paginationControls.innerHTML = '';
                if (data.totalPages > 1) {
                    let paginationHTML = '';
                    const pagesToShow = 5;
                    const startPage = Math.max(1, data.currentPage - Math.floor(pagesToShow / 2));
                    const endPage = Math.min(data.totalPages, startPage + pagesToShow - 1);

                    if (startPage > 1) {
                        paginationHTML += `<a href="#" data-page="1"><span>First</span></a>`;
                        if (startPage > 2) {
                            paginationHTML += `<span>...</span>`;
                        }
                    }

                    if (data.currentPage > 1) {
                        paginationHTML += `<a href="#" data-page="${data.currentPage - 1}"><i class='fa-solid fa-chevron-left'></i><span>Previous</span></a>`;
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        if (i === data.currentPage) {
                            paginationHTML += `<span class="active">${i}</span>`;
                        } else {
                            paginationHTML += `<a href="#" data-page="${i}"><span>${i}</span></a>`;
                        }
                    }

                    if (data.currentPage < data.totalPages) {
                        paginationHTML += `<a href="#" data-page="${data.currentPage + 1}"><span>Next</span><span class='arrow-right'><i class='fa-solid fa-chevron-right'></i></span></a>`;
                    }

                    if (endPage < data.totalPages) {
                        if (endPage < data.totalPages - 1) {
                            paginationHTML += `<span>...</span>`;
                        }
                        paginationHTML += `<a href="#" data-page="${data.totalPages}"><span>Last</span></a>`;
                    }

                    paginationControls.innerHTML = paginationHTML;

                    paginationControls.querySelectorAll('a').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            currentPage = parseInt(this.dataset.page);
                            fetchProperties();
                        });
                    });
                }
            }

            propertyTypeSelect.addEventListener('change', function() {
                currentFilter = this.value;
                currentPage = 1;
                fetchProperties();
            });

            flatTypeSelect.addEventListener('change', function() {
                currentFlatType = this.value;
                currentPage = 1;
                fetchProperties();
            });

            sortSelect.addEventListener('change', function() {
                currentSort = this.value;
                currentPage = 1;
                fetchProperties();
            });

            locationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    locationLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    currentLocation = this.dataset.location;
                    currentSearch = '';
                    currentPage = 1;
                    fetchProperties();
                });
            });

            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                currentSearch = this.querySelector('input[name="search"]').value;
                currentLocation = '';
                currentPage = 1;
                fetchProperties();

                locationLinks.forEach(link => link.classList.remove('active'));
                document.querySelector('.Deals a[data-location=""]').classList.add('active');
            });

            // Initial fetch
            fetchProperties();
            <?php if ($isBuyer): ?>
                updateCartCount();
            <?php endif; ?>

        });
    </script>
</body>

</html>