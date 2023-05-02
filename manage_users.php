<?php
include 'resources/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    die();
}

if (isPost() && !empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["repeatPassword"])) {
    if (preliminarySignUpCheck($_POST["username"], $_POST["password"], $_POST["repeatPassword"]) != "Success") {
        $errorMsg = preliminarySignUpCheck($_POST["username"], $_POST["password"], $_POST["repeatPassword"]);
    } elseif (isUserUsed($_POST["username"])) {
        $errorMsg = "Error: Username is already in use";
    } elseif (createAccount($_POST["username"], $_POST["password"], 1)) {
        $successMsg = "Account created successfully";
    } else {
        $errorMsg = "Error: Could not create account";
    }
}

?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OpenVPN Webadmin - Manage Users</title>
    <link href="/resources/bootstrap.min.css" rel="stylesheet">
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


        .form-signin {
            padding: 15px;
        }
        .form-signin .form-floating:focus-within {
            z-index: 2;
        }
        .form-signin input[name="username"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
        .form-signin input[name="password"] {
            border-radius: 0;
        }
        .form-signin input[name="repeatPassword"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
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
        <div class="form-signin">
        <form method="POST">
            <h1 class="h3 mb-3 fw-normal">Add User</h1>
            <?php
            if (isset($errorMsg)) {
                echo <<<EOL
            <div class="alert alert-danger" role="alert">$errorMsg</div>
            EOL;
            } elseif (isset($successMsg)) {
                echo <<<EOL
            <div class="alert alert-success" role="alert">$successMsg</div>
            EOL;
            }
            ?>
            <div class="form-floating">
                <input type="text" class="form-control" name="username" id="username" placeholder="Username">
                <label for="username">Username</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                <label for="password">Password</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" name="repeatPassword" id="repeatPassword" placeholder="Repeat password">
                <label for="repeatPassword">Repeat password</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">Create Account</button>
        </form>
        </div>
    </div>
</main>

<script src="resources/bootstrap.bundle.min.js"></script>
</body>
</html>
