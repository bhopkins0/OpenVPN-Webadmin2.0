<?php
include 'resources/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    die();
}
if (isPost() && !empty($_POST["password"]) && !empty($_POST["username"])) {
    if (!preliminaryLoginCheck($_POST["username"], $_POST["password"]) || !isUserUsed($_POST["username"])) {
        $errorMsg = "Error: Incorrect Username or Password";
    } elseif (accountLogin($_POST["username"], $_POST["password"])) {
        header('Location: index.php');
        die();
    } else {
        $errorMsg = "Error: Incorrect Username or Password";
    }
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>OpenVPN Webadmin - Login</title>
    <link href="/resources/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .form-signin {
            max-width: 330px;
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
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
</head>
<body>
<main class="form-signin w-100 m-auto">
    <form method="POST">
        <h1 class="h3 mb-3 fw-normal">Login</h1>
        <?php
        if (isset($errorMsg)) {
            echo <<<EOL
            <div class="alert alert-danger" role="alert">$errorMsg</div>
            EOL;
        }
        ?>
        <div class="form-floating">
            <input type="username" class="form-control" name="username" id="username" placeholder="Username">
            <label for="username">Username</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" name="password" id="password" placeholder="Password">
            <label for="password">Password</label>
        </div>
        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
    </form>
</main>
<script src="resources/bootstrap.bundle.min.js"></script>
</body>
</html>
