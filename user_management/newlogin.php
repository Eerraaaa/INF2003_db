<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    session_start();

    // Check for error message in session and display
    if (isset($_SESSION['error_message'])) {
        echo "<script>alert('" . $_SESSION['error_message'] . "');</script>";
        unset($_SESSION['error_message']);
    }

    // Check for success message in session and display
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }

    // Check if a redirect is needed after login
    if (isset($_SESSION['user_id'])) {
        // Default redirect to user dashboard
        $redirectPage = 'user_dashboard.php'; 

        // If there's a redirect URL set in the session, use it
        if (isset($_SESSION['redirect_url'])) {
            $redirectPage = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']); // Clear it after use
        }

        // Redirect to the desired page
        header('Location: ' . $redirectPage);
        exit();
    }

    ?>
    <?php include "../inc/head.inc.php"; ?>
    <title>Login</title>
</head>

<body>
    <?php include "../inc/nav.inc.php"; ?>
    <!-- Your provided HTML structure for the login page goes here -->
    <main class="container" style="padding-top: 250px;">
        <h1>Account</h1>
        <div class="row">
            <article class="col-sm">
                <section id="new-member">
                    <h2> New here?</h2>
                    <p>
                        Registration is free and easy!<br>
                    </p>
                    <div class="mb-3">
                        <a href="register.php" class="btn btn-primary">Register Now!</a>
                    </div>
                </section>
            </article>
            <article class="col-sm">
                <section id="sign-in">
                    <h2>Sign in</h2>
                    <!-- Update the form action to dynamically include redirect parameters if applicable -->
                    <form action="process_login.php" method="post">
                        <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : ''; ?>">
                        <input type="hidden" name="product_id" value="<?php echo isset($_GET['product_id']) ? htmlspecialchars($_GET['product_id']) : ''; ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input required maxlength="45" type="email" id="email" name="email" class="form-control" placeholder="Enter email">
                        </div>
                        <div class="mb-3">
                            <label for="pwd" class="form-label">Password:</label>
                            <input required maxlength="45" type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password">
                        </div>
                        <div class="mb-3">
                            <a href='forgot_pwd.php' style="color: darkblue; text-decoration: underline;">Forgot Password?</a>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Sign In</button>
                        </div>
                    </form>
                </section>
            </article>
        </div>
    </main>

</body>

</html>