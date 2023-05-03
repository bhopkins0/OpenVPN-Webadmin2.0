<?php
include 'resources/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    die();
}

?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OpenVPN Webadmin - My Account</title>
    <link href="resources/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 4.5rem;
        }
        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">OpenVPN Webadmin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vpn_settings.php">VPN Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vpn_status.php">VPN Status</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="client_manager.php">Client Manager</a>
                </li>
            </ul>
            <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                <li class="nav-item dropdown me-5">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Webadmin Manager
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="myaccount.php">My Account</a></li>
                        <li><a class="dropdown-item" href="manage_users.php">Add User</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>


<main class="container mb-2">
    <div class="bg-body-tertiary p-5 rounded">
        <h1>My Account</h1>
        <hr>
        <fieldset disabled>
            <legend>Account Information</legend>
            <div class="mb-3">
                <label for="user" class="form-label">Username</label>
                <input type="text" id="user" class="form-control" placeholder="<?php echo htmlspecialchars($_SESSION["username"]); ?>">
            </div>
        </fieldset>
        <hr>
        <form method="POST">
            <div class="mb-3">
                <label for="action" class="form-label">Account Actions</label>
                <select class="form-select" id="action" name="action" aria-label="Account Actions">
                    <option selected>Select Action</option>
                    <option value="1">View Login Attempts</option>
                    <option value="2">Change Password</option>
                </select>
            </div>
            <button class="w-100 btn btn-lg btn-outline-primary" type="submit">Submit</button>
        </form>

        <?php
        if (!empty($_POST["action"]))
            echo accountAction($_POST["action"]);
        ?>

    </div>
</main>

<script src="resources/bootstrap.bundle.min.js"></script>
</body>
</html>
