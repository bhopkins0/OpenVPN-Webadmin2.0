<?php

session_start();
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
include 'api-functions.php';

function startMySQL(): bool|mysqli
{
    $mysqlHostname = "";
    $mysqlUser = "";
    $mysqlPass = '';
    $mysqlDB = "";

    return mysqli_connect($mysqlHostname, $mysqlUser, $mysqlPass, $mysqlDB);
}



/*
 *
 * My Account
 *
 */

function accountAction($action)
{
    switch ($action) {
        case 1:
            return displayLoginAttempts();
        case 2:
            return displayPasswordReset();
    }
}

function getLoginAttemptRows($user_ID)
{
    $loginAttempts = getLoginAttempts($user_ID);
    $loginAttemptRows = "";
    foreach ($loginAttempts as $row):
        $login_time = date(DATE_RFC2822, $row["login_time"]);
        $login_ip = long2ip($row["ip_address"]);
        $login_success = $row["login_successful"] ? 'Yes' : 'No';
        if ($row["login_successful"]) {
            $tableColor = '';
        } else {
            $tableColor = 'class="bg-danger text-dark"';
        }
        $loginAttemptRows .= <<<EOL
                <tr $tableColor>
                    <td>$login_time</td>
                    <td>$login_ip</td>
                    <td>$login_success</td>
                </tr>
                EOL;
    endforeach;
    return $loginAttemptRows;
}

function displayLoginAttempts()
{
    $loginAttemptRows = getLoginAttemptRows($_SESSION["user_ID"]);
    echo <<<EOL
        <hr>
        <legend>Login attempts</legend>
        <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">Time</th>
                        <th scope="col">IP Address</th>
                        <th scope="col">Successful?</th>
                    </tr>
                    </thead>
                    <tbody>
                    {$loginAttemptRows}
                    </tbody>
                </table>
            </div>
EOL;
}

function displayPasswordReset()
{
    $resultMsg = "";
    if (!empty($_POST["oldpassword"]) && !empty($_POST["newpassword"]) && !empty($_POST["repeat"]))
        $resultMsg = resetPassword($_POST["oldpassword"], $_POST["newpassword"], $_POST["repeat"]);
    echo <<<EOL
        <hr>
        <legend>Change password</legend>
        <form method="POST">
        <input type="hidden" name="action" value="3">
        {$resultMsg}
            <div class="mb-3">
                <label for="oldpassword" class="form-label">Current password</label>
                <input type="password" class="form-control" id="oldpassword" name="oldpassword" placeholder="Enter current password">
            </div>
            <div class="mb-3">
                <label for="newpassword" class="form-label">New password</label>
                <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="Enter new password">
            </div>
            <div class="mb-3">
                <label for="repeat" class="form-label">Repeat new password</label>
                <input type="password" class="form-control" id="repeat" name="repeat" placeholder="Repeat new password">
            </div>
            <input type="hidden" name="action" value="2">
            <button class="w-100 btn btn-lg btn-outline-primary" type="submit">Change password</button>
        </form>
EOL;
}

function resetPassword($current, $new, $repeatNew)
{
    if (!verifyPassword($_SESSION["username"],$current)) {
        return "<div class='alert alert-danger' role='alert'>The current password you entered was incorrect.</div>";
    }
    if (!($new == $repeatNew))
        return "<div class='alert alert-danger' role='alert'>The passwords do not match</div>";

    if (strlen($new) < 8 || strlen($new) > 64)
        return "<div class='alert alert-danger' role='alert'>Passwords must be in between 8 and 64 characters</div>";

    $newPassword = password_hash($new, PASSWORD_BCRYPT);
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "UPDATE User SET password=? WHERE username=?";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bind_param("ss", $newPassword, $_SESSION["username"]);
    $stmt->execute();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return "<div class='alert alert-success' role='alert'>Password successfully reset</div>";

}

function getLoginAttempts($user_ID) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT ip_address, login_time, login_successful FROM Login_Attempts WHERE user_ID = ? ORDER BY login_time DESC";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bind_param("i", $user_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/*
 *
 * Server Settings Page
 *
 */


function displayAddServer($errorMsg = "", $notZeroServers = 0): string
{
    if ($notZeroServers)
        $hiddenInput = '<input type="hidden" name="chooseServer" value="Add Server">';
    return <<<EOL
    <main class="container mb-2">
    <div class="bg-body-tertiary p-5 rounded">
        <h1>Add VPN Server</h1>
        <hr>
        {$errorMsg}
        <form method="POST">
            <div class="mb-3">
                <label for="ip_address" class="form-label">IP Address</label>
                <input type="text" class="form-control" id="ip_address" name="ip_address" placeholder="xxx.xxx.xxx.xxx">
            </div>
            <div class="mb-3">
                <label for="domain" class="form-label">Domain Name</label>
                <input type="text" class="form-control" id="domain" name="domain" placeholder="example.com">
                <small>A domain name is needed to simplify setting up TLS on the VPN's webserver.</small>
                {$hiddenInput}
            </div>
            <button class="btn btn-lg btn-primary w-100" type="submit">Add VPN Server</button>
        </form>
    </div>
</main>
EOL;
}

function displayServerSettingsBackButton(): string
{
    return <<<EOL
<div class="container mb-2">
            <a class="btn btn-lg btn-outline-primary w-100" href="vpn_settings.php">Back</a>
</div>
EOL;

}

function displayServerSettingsUnselected(): string
{
    $serverList = getServerFormList();
    if ($_GET["deleted"])
        $deleteMsg = '<div class="alert alert-success">VPN deleted successfully</div>';
    return <<<EOL
    <main class="container mb-2">
    <div class="bg-body-tertiary p-5 rounded">
        <h1>VPN Server Settings</h1>
        <hr>
        {$deleteMsg}
        <form method="POST">
             <div class="mb-3">
                <label for="chooseServer" class="form-label">Server list</label>
                <select class="form-select" id="chooseServer" name="chooseServer" aria-label="Select server">
                    <option selected>Select server</option>
                    {$serverList}
                    <option value="Add Server">Add new server</option>
                </select>
            </div>
            <button class="w-100 btn btn-lg btn-outline-primary" type="submit">Submit</button>
         </form>
    </div>
</main>
EOL;
}



function createVPNClient($serverID, $clientName, $apiKey): void
{
    $user_ID = $_SESSION["user_ID"];
    $serverInfo = getServerInfo($serverID);
    if (!ctype_alnum($clientName)) {
        $_SESSION["returnMsg"]='<div class="alert alert-danger">Error: Client names must be alphanumeric</div>';
    } elseif (isClientUsed($clientName, $serverID)) {
        $_SESSION["returnMsg"]='<div class="alert alert-danger">Error: Client names already used for this server</div>';
    } else {
        $createdClient = generateClient($serverInfo["domain_name"], $clientName, $apiKey);
        $client_ID = insertIntoVPNClientsTable($serverID, $user_ID, $clientName);
        $vpn_ca = get_string_between($createdClient,"<ca>","</ca>");
        $vpn_cert = get_string_between($createdClient,"<cert>","</cert>");
        $vpn_priv_key = get_string_between($createdClient,"<key>","</key>");
        $tls_crypt_key = get_string_between($createdClient,"<tls-crypt>","</tls-crypt>");

        if (insertIntoVPNKeysTable($client_ID, $vpn_ca, $vpn_cert, $vpn_priv_key, $tls_crypt_key))
            $_SESSION["returnMsg"]='<div class="alert alert-success">Client successfully added</div>';
        else
            $_SESSION["returnMsg"]='<div class="alert alert-danger">Error: Could not add client</div>';

    }
}


function downloadVPNClient($serverID, $clientID, $apiKey)
{

    if (!getClientInfo($clientID))
        return "An error occurred.";
    $clientInfo = getClientInfo($clientID);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-disposition: attachment; filename=\"".$clientInfo["client_name"].".ovpn\"");
    $serverInfo = getServerInfo($serverID);
    $vpnKeys = getVPNKeys($clientID);
    $vpnClient = getClientTemplate($serverInfo["domain_name"], $apiKey);
    $vpnClient .= "<ca>".$vpnKeys["vpn_ca"]."</ca>\n";
    $vpnClient .= "<cert>".$vpnKeys["vpn_cert"]."</cert>\n";
    $vpnClient .= "<key>".$vpnKeys["vpn_priv_key"]."</key>\n";
    $vpnClient .= "<tls-crypt>".$vpnKeys["vpn_tls_crypt"]."</tls-crypt>\n";
    die($vpnClient);
}

function getClientInfo($client_ID)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM VPN_Clients WHERE client_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $client_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    if ($result->num_rows != 1) {
        return false;
    }
    return $result->fetch_assoc();
}




function getWebPort($server_ID) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT webserver_port FROM VPN_Server_Settings WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $client_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->fetch_assoc()["webserver_port"];
}


function getVPNKeys($client_ID) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM VPN_Keys WHERE client_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $client_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->fetch_assoc();
}


function getClientsFormList($server_ID) {
    $serverList = "";
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM VPN_Clients WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $server_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    while ($serverRow = $result->fetch_assoc()) {
        $serverList .= '<option value="'.$serverRow["client_ID"].'">'.$serverRow["client_name"].'</option>';
    }
    return $serverList;
}

function deleteVPNClient($server_ID,$client_ID,$apiKey)
{
    if (!getClientInfo($client_ID))
        return "An error occurred.";
    $clientInfo = getClientInfo($client_ID);
    $serverInfo = getServerInfo($server_ID);
    if (deleteClient($serverInfo["domain_name"],$clientInfo["client_name"], $apiKey) == "true") {
        deleteVPNKeys($client_ID);
        deleteClientFromDatabase($client_ID);
        $_SESSION["returnMsg"]='<div class="alert alert-success">Client successfully deleted ('.$clientInfo["client_name"].')</div>';
    }
    else
        $_SESSION["returnMsg"]='<div class="alert alert-danger">Error: Could not delete client</div>';
}

function deleteVPNKeys($client_ID) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "DELETE FROM VPN_Keys WHERE client_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $client_ID);
    $stmt->execute();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}

function deleteClientFromDatabase($client_ID) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "DELETE FROM VPN_Clients WHERE client_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $client_ID);
    $stmt->execute();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}

function displayClientManager(): string
{
    if (isZeroServers()) {
        header('Location: vpn_settings.php');
        die();
    }
    $selectedServer = $_POST["chooseServer"] ?? $_SESSION["refreshPage"];
    $displayServerSelected = "";
    $returnMsg = "";

    if (is_numeric($_POST["chooseServer"])) {
        $serverInfo = getServerInfo($_POST["chooseServer"]);
        $ip_address = long2ip($serverInfo["ip_address"]);
        if (is_numeric($_SESSION["refreshPage"])) {
            $_SESSION["refreshPage"] = "unused";
            $refreshWithPOSTInfo = "<input type='hidden' name='chooseServer' value='$selectedServer'>";
        }
        if (!empty($_POST["addClient"]))
            createVPNClient($serverInfo["server_ID"],$_POST["addClient"],getAPIKey($serverInfo["server_ID"]));
        if (!empty($_POST["deleteClient"]))
            deleteVPNClient($serverInfo["server_ID"],$_POST["deleteClient"],getAPIKey($serverInfo["server_ID"]));

        if (!empty($_SESSION["returnMsg"])) {
            $returnMsg = $_SESSION["returnMsg"];
            $_SESSION["returnMsg"] = "";
        }
        $clientsList = getClientsFormList($serverInfo["server_ID"]);
        $displayServerSelected = <<<EOL
    <hr>
            <legend>Server Information</legend>
            <div class="mb-3">
                <label for="ip_address" class="form-label">IP address</label>
                <input type="text" id="ip_address" class="form-control" placeholder="{$ip_address}" disabled>
            </div>
            <div class="mb-3">
                <label for="domain_name" class="form-label">Domain name</label>
                <input type="text" id="domain_name" class="form-control" placeholder="{$serverInfo["domain_name"]}" disabled>
            </div>
            <hr>
            <form method="POST">
            <label for="addClient" class="form-label">Add VPN client</label>
            {$returnMsg}
            <div class="input-group mb-3">
                 <input type="text" class="form-control" name="addClient" id="addClient" placeholder="exampleClient" aria-label="exampleClient">
                 <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                 {$refreshWithPOSTInfo}
                 <button class="btn btn-primary" role="submit">Submit</button>
            </div>
            </form>
            <form method="POST" action="download.php">
            <label for="downloadClient" class="form-label">Download client</label>
            <div class="input-group mb-3">
            <select class="form-select" name="downloadClient" id="downloadClient">
                 <option selected>VPN Clients - {$serverInfo["domain_name"]}</option>
                 {$clientsList}
            </select>
              <div class="input-group-append">
                <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                {$refreshWithPOSTInfo}
                <button class="btn btn-primary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" role="submit">Download</button>
              </div>
            </div>
            </form>
            <form method="POST">
            <label for="deleteClient" class="form-label">Delete client</label>
            <div class="input-group mb-3">
            <select class="form-select" name="deleteClient" id="deleteClient">
                 <option selected>VPN Clients - {$serverInfo["domain_name"]}</option>
                 {$clientsList}
            </select>
              <div class="input-group-append">
                <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                {$refreshWithPOSTInfo}
                <button class="btn btn-danger" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" role="submit">Delete</button>
              </div>
            </div>
            </form>
           
EOL;

    }


    $serverList = getServerFormList();
    return <<<EOL
    <main class="container mb-4">
    <div class="bg-body-tertiary p-5 rounded">
        <h1>VPN Client Manager</h1>
        <hr>
        <form method="POST">
             <div class="mb-3">
                <label for="chooseServer" class="form-label">Server list</label>
                <select class="form-select" id="chooseServer" name="chooseServer" aria-label="Select server">
                    <option selected>Select server</option>
                    {$serverList}
                </select>
            </div>
            <button class="w-100 btn btn-lg btn-outline-primary" type="submit">Submit</button>
         </form>
         {$displayServerSelected}
    </div>
</main>
EOL;
}



function displayServerStatus(): string
{
    if (isZeroServers()) {
        header('Location: vpn_settings.php');
        die();
    }
    $displayServerSelected = "";

    if (is_numeric($_POST["chooseServer"])) {
        $serverInfo = getServerInfo($_POST["chooseServer"]);
        $ip_address = long2ip($serverInfo["ip_address"]);

        if (isVPNDaemonRunning($serverInfo["domain_name"], getAPIKey($_POST["chooseServer"])))
            $serverStatusButton = '<button type="button" class="btn btn-success w-100" disabled>OpenVPN daemon is running</button>';
        else
            $serverStatusButton = '<button type="button" class="btn btn-danger w-100" disabled>OpenVPN daemon is not running</button>';

        $connectedClientsTable = getConnectedClientsTable($serverInfo["domain_name"], getAPIKey($_POST["chooseServer"]));
        $bandwidthGraph = getBandwidthGraph($serverInfo["domain_name"], getAPIKey($_POST["chooseServer"]), $serverInfo["server_ID"]);
        $displayServerSelected = <<<EOL
    <hr>
            <legend>Server Information</legend>
            <div class="mb-3">
                <label for="ip_address" class="form-label">IP address</label>
                <input type="text" id="ip_address" class="form-control" placeholder="{$ip_address}" disabled>
            </div>
            <div class="mb-3">
                <label for="domain_name" class="form-label">Domain name</label>
                <input type="text" id="domain_name" class="form-control" placeholder="{$serverInfo["domain_name"]}" disabled>
            </div>
            <div class="mb-3">
                {$serverStatusButton}
            </div>
            <hr>
            <div class="row">
            <div class="col-9"><legend>Connected clients and bandwidth graph</legend></div>
            <div class="col-lg-6 mb-3 justify-content-center d-flex">
            <div class="table-responsive">
<table class="table table-dark">
  <thead>
    <tr>
      <th scope="col">Common Name</th>
      <th scope="col">IP:PORT</th>
      <th scope="col">Data Recieved</th>
      <th scope="col">Data Sent</th>
      <th scope="col">Connected Since</th>
    </tr>
  </thead>
  <tbody>
  {$connectedClientsTable}
  </tbody>
</table>
</div></div>
            <div class="col-lg-6 mb-3 justify-content-center d-flex">
                <img class="img-fluid" src="data:image/png;base64,{$bandwidthGraph}" alt="Network stats">
            </div></div>
EOL;

    }


    $serverList = getServerFormList();
    return <<<EOL
    <main class="container mb-4">
    <div class="bg-body-tertiary p-5 rounded">
        <h1>VPN Server Status</h1>
        <hr>
        <form method="POST">
             <div class="mb-3">
                <label for="chooseServer" class="form-label">Server list</label>
                <select class="form-select" id="chooseServer" name="chooseServer" aria-label="Select server">
                    <option selected>Select server</option>
                    {$serverList}
                </select>
            </div>
            <button class="w-100 btn btn-lg btn-outline-primary" type="submit">Submit</button>
         </form>
         {$displayServerSelected}
    </div>
</main>
EOL;
}


function deleteVPN($serverID): void
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }

    $sql = "SELECT client_ID FROM VPN_Clients WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $serverID);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    $serverInfo = getServerInfo($serverID);
    while ($clientRow = $result->fetch_assoc()) {
        $clientInfo = getClientInfo($clientRow["client_ID"]);
        if (deleteClient($serverInfo["domain_name"],$clientInfo["client_name"], getAPIKey($serverID)) == "true") {
            deleteVPNKeys($clientRow["client_ID"]);
            deleteClientFromDatabase($clientRow["client_ID"]);
        }
    }


    $sql = "DELETE FROM API_Keys WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $serverID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $sql = "DELETE FROM VPN_Server_Settings WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $serverID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $sql = "DELETE FROM VPN_Servers WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $serverID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    header('Location: vpn_settings.php?deleted=1');
    die();
}

function refreshPageWithPOST($serverID)
{
    $_SESSION["refreshPage"] = $serverID;
    header('Location: vpn_settings.php');
    die();
}


function displaySelectedServer(): string
{
    $selectedServer = $_POST["chooseServer"] ?? $_SESSION["refreshPage"];
    $serverInfo = getServerInfo($selectedServer);
    $ip_address = long2ip($serverInfo["ip_address"]);
    $serverSettings = getServerSettings($selectedServer);
    $vpnDNS = long2ip($serverSettings["vpn_dns"]);
    $apiKey = getAPIKey($selectedServer);


    /*
    if ($_POST["togglePortForwarding"])
        togglePortForwarding($selectedServer, !$serverSettings["portforwarding_on"]);
    */

    if ($_POST["toggleVPNStatus"])
        toggleVPNStatus($selectedServer, getAPIKey($selectedServer));


    if (isVPNDaemonRunning($serverInfo["domain_name"], getAPIKey($selectedServer)))
        $toggleStatusButton = '<button class="btn btn-outline-danger w-100" role="submit">Stop OpenVPN Daemon</button>';
    else
        $toggleStatusButton = '<button class="btn btn-success w-100" role="submit">Start OpenVPN Daemon</button>';

    if ($_POST["deleteVPN"])
        deleteVPN($selectedServer);

    if (!empty($_POST["changeDomain"])) {
        if (strlen($_POST["changeDomain"]) > 253 || !preg_match('/^(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/m', $_POST["changeDomain"]))
            $errorMsg=displayErrorMessage("Error: Invalid domain name");
        elseif (!isset($errorMsg))
            setDomainName($selectedServer, $_POST["changeDomain"]);
    }
    if (!empty($_POST["changePort"])) {
        if ($_POST["changePort"] > 65535 || $_POST["changePort"] < 0 || !is_numeric($_POST["changePort"]))
            $errorMsg=displayErrorMessage("Error: Invalid port number");
        elseif (!isset($errorMsg))
            setVPNPort($selectedServer, $_POST["changePort"]);
    }
    /*if (!empty($_POST["changeWebPort"])) {
        if ($_POST["changeWebPort"] > 65535 || $_POST["changeWebPort"] < 0 || !is_numeric($_POST["changeWebPort"]))
            $errorMsg=displayErrorMessage("Error: Invalid port number");
        elseif (!isset($errorMsg))
            setWebPort($selectedServer, $_POST["changeWebPort"]);
    }*/
    if (!empty($_POST["changeDNS"])) {
        if (!ip2long($_POST["changeDNS"]))
            $errorMsg=displayErrorMessage("Error: Invalid IP address");
        elseif (!isset($errorMsg))
            setDNS($selectedServer, ip2long($_POST["changeDNS"]));
    }
    if (!empty($_POST["changeProtocol"])) {
        if ($_POST["changeProtocol"] != "UDP" && $_POST["changeProtocol"] != "TCP")
            $errorMsg=displayErrorMessage("Error: Invalid protocol");
        elseif (!isset($errorMsg))
            setProtocol($selectedServer, $_POST["changeProtocol"]);
    }

    /*
    if ($serverSettings["portforwarding_on"])
        $portForwardingButton = '<button class="btn w-100 btn-outline-danger" role="submit">Disable port forwarding</button>';
    else
        $portForwardingButton = '<button class="btn w-100 btn-success" role="submit">Enable port forwarding</button>';
    */

    if (is_numeric($_SESSION["refreshPage"])) {
        $_SESSION["refreshPage"] = "unused";
        $refreshWithPOSTInfo = "<input type='hidden' name='chooseServer' value='$selectedServer'>";
    }



    return <<<EOL
    <main class="container mb-2">
    <div class="bg-body-tertiary p-5 rounded">
        <h1>VPN Server Settings</h1>
        <hr>
            <fieldset>
            <legend>Server Information</legend>
            <div class="mb-3">
                <label for="ip_address" class="form-label">IP address</label>
                <input type="text" id="ip_address" class="form-control" value="{$ip_address}" readonly>
            </div>
            <div class="mb-3">
                <label for="domain_name" class="form-label">Domain name</label>
                <input type="text" id="domain_name" class="form-control" value="{$serverInfo["domain_name"]}" readonly>
            </div>
            <div class="mb-3">
                <label for="apiKey" class="form-label">API Key</label>
                <input type="text" id="apiKey" class="form-control" value="{$apiKey}" readonly>
            </div>
            <div class="mb-3">
                <label for="vpnPort" class="form-label">VPN port</label>
                <input type="text" id="vpnPort" class="form-control" value="{$serverSettings["vpn_port"]}" disabled>
            </div>
            <div class="mb-3">
                <label for="vpnDNS" class="form-label">VPN DNS</label>
                <input type="text" id="vpnDNS" class="form-control" value="{$vpnDNS}" disabled>
            </div>
            <div class="mb-3">
                <label for="vpnProtocol" class="form-label">VPN protocol</label>
                <input type="text" id="vpnProtocol" class="form-control" value="{$serverSettings["vpn_protocol"]}" disabled>
            </div>
            <!--<div class="mb-3">
                <label for="webPort" class="form-label">VPN web server port</label>
                <input type="text" id="webPort" class="form-control" value="{$serverSettings["webserver_port"]}" disabled>
            </div>-->
            </fieldset>
        <hr>
        <legend>Configure Server Settings</legend>
        <form method="POST" >
        {$errorMsg}
            <label for="changeDomain" class="form-label">Set domain</label>
            <div class="input-group mb-3">
                 <input type="text" class="form-control" name="changeDomain" id="changeDomain" placeholder="www.example.com" aria-label="Change domain">
                 <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                 {$refreshWithPOSTInfo}
                 <button class="btn btn-primary" role="submit">Submit</button>
            </div>
            </form>
            <form method="POST">
            <label for="changePort" class="form-label">Set VPN port</label>
            <div class="input-group mb-3">
                 <input type="text" class="form-control" name="changePort" id="changePort" placeholder="1194" aria-label="Change port">
                 <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                 {$refreshWithPOSTInfo}
                 <button class="btn btn-primary" role="submit">Submit</button>
            </div>
            </form>
            <!--<form method="POST">
            <label for="changeWebPort" class="form-label">Set web server port</label>
            <div class="input-group mb-3">
                 <input type="text" class="form-control" name="changeWebPort" id="changeWebPort" placeholder="443" aria-label="Change web server port">
                 <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                 {$refreshWithPOSTInfo}
                 <button class="btn btn-primary" role="submit">Submit</button>
            </div>
            </form>-->
            <form method="POST">
            <label for="changeDNS" class="form-label">Set DNS</label>
            <div class="input-group mb-3">
                 <input type="text" class="form-control" name="changeDNS" id="changeDNS" placeholder="1.1.1.1" aria-label="Change DNS">
                 <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                 {$refreshWithPOSTInfo}
                 <button class="btn btn-primary" role="submit">Submit</button>
            </div>
            </form>
            <form method="POST">
            <label for="changeProtocol" class="form-label">Set protocol</label>
            <div class="input-group mb-3">
            <select class="form-select" name="changeProtocol" id="changeProtocol">
                 <option selected>Choose protocol</option>
                 <option value="TCP">TCP</option>
                 <option value="UDP">UDP</option>
            </select>
              <div class="input-group-append">
                <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                {$refreshWithPOSTInfo}
                <button class="btn btn-primary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" role="submit">Submit</button>
              </div>
            </div>
            </form>
            <form method="POST">
            <div class="input-group mb-3">
                 <input type="hidden" name="toggleVPNStatus" value="1">
                 <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                 {$refreshWithPOSTInfo}
            </div>
                 {$toggleStatusButton}
            </form>
            <hr>
            <form method="POST">
            <div class="input-group mb-3">
                 <input type="hidden" name="deleteVPN" value="1">
                 <input type="hidden" name="chooseServer" value="{$_POST["chooseServer"]}">
                 {$refreshWithPOSTInfo}
            </div>
                 <button class="btn w-100 btn-danger" role="submit">Delete VPN</button>
        </form>
    </div>
</main>
EOL;


}



function getServerSettings($server_ID)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM VPN_Server_Settings WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $server_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows != 1) {
        header('Location: vpn_settings.php');
        die();
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->fetch_assoc();
}

function getServerInfo($server_ID)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM VPN_Servers WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $server_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows != 1) {
        header('Location: vpn_settings.php');
        die();
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->fetch_assoc();
}


function getServerFormList() {
    $serverList = "";
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM VPN_Servers";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    while ($serverRow = $result->fetch_assoc()) {
        $serverList .= '<option value="'.$serverRow["server_ID"].'">'.$serverRow["domain_name"].' - '.long2ip($serverRow["ip_address"])."</option>\n";
    }
    return $serverList;
}

function addVPNServer($notZeroServers = 0) {
    $domain = $_POST["domain"];
    $ip_address = ip2long($_POST["ip_address"]);
    if (!$ip_address)
        return displayAddServer(displayErrorMessage("Error: Invalid IP address"), $notZeroServers);
    if (strlen($domain) > 253 || !preg_match('/^(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/m', $domain))
        return displayAddServer(displayErrorMessage("Error: Invalid domain name"), $notZeroServers);
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "INSERT INTO VPN_Servers (ip_address, domain_name, is_active) VALUES (?, ?, 1)";
    if ($stmt = mysqli_prepare($mysqlConn, $sql)) {
        mysqli_stmt_bind_param($stmt, "is", $ip_address, $domain);
        mysqli_stmt_execute($stmt);
        if (insertIntoServerSettingsTable($stmt->insert_id) && insertIntoAPIKeysTable($stmt->insert_id))
            header('Location: vpn_settings.php');
        else
            return displayAddServer(displayErrorMessage("Error: Could not add to database"), $notZeroServers);
        return false;
    } else {
        return displayAddServer(displayErrorMessage("Error: Could not add to database"), $notZeroServers);
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}



function insertIntoVPNClientsTable($server_ID, $user_ID, $client_name) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "INSERT INTO VPN_Clients (server_ID, user_ID, client_name, is_active) VALUES (?, ?, ?, 1)";
    if ($stmt = mysqli_prepare($mysqlConn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iis", $server_ID, $user_ID, $client_name);
        mysqli_stmt_execute($stmt);
        $client_ID=$stmt->insert_id;
        return $client_ID;
    } else {
        return false;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}

function isClientUsed($client_name, $server_ID)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT client_ID FROM VPN_Clients WHERE client_name=? AND server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bind_param("si", $client_name, $server_ID);
    $stmt->execute();
    $result=$stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return (bool)$result->fetch_row();
}


function insertIntoVPNKeysTable($client_ID, $vpn_ca, $vpn_cert, $vpn_priv_key, $vpn_tls_crypt) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "INSERT INTO VPN_Keys (client_ID, vpn_ca, vpn_cert, vpn_priv_key, vpn_tls_crypt) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($mysqlConn, $sql)) {
        mysqli_stmt_bind_param($stmt, "issss", $client_ID, $vpn_ca, $vpn_cert, $vpn_priv_key, $vpn_tls_crypt);
        mysqli_stmt_execute($stmt);
        return true;
    } else {
        return false;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}

function insertIntoServerSettingsTable($server_ID) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "INSERT INTO VPN_Server_Settings (server_ID, vpn_port, vpn_protocol, vpn_dns, webserver_port, tls_crypt_on, portforwarding_on) VALUES (?, 0, 0, 0, 443, 1, 0)";
    if ($stmt = mysqli_prepare($mysqlConn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $server_ID);
        mysqli_stmt_execute($stmt);
        return true;
    } else {
        return false;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}

function insertIntoAPIKeysTable($server_ID) {
    $apiKey = bin2hex(random_bytes(48));
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "INSERT INTO API_Keys (server_ID, api_key, user_ID, is_active) VALUES (?, ?, ?, 1)";
    if ($stmt = mysqli_prepare($mysqlConn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isi", $server_ID, $apiKey, $_SESSION["user_ID"]);
        mysqli_stmt_execute($stmt);
        return true;
    } else {
        return false;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}

/*
 *
 * Sign up and Log in
 *
 */


function createAccount($username, $password, $userType)
{
    $mysqlConn = startMySQL();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "INSERT INTO User (username, password, usertype_ID) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($mysqlConn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $username, $hashedPassword, $userType);
        mysqli_stmt_execute($stmt);
        return true;
    } else {
        return false;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}


function preliminaryLoginCheck($user, $password): bool
{
    return !(strlen($user) > 64 || strlen($user) < 3 || !ctype_alnum($user) || strlen($password) < 8 || strlen($password) > 64);
}

function preliminarySignUpCheck($username, $password, $rpassword): string
{
    if (strlen($username) > 254 || strlen($username) < 3) {
        return "Error: Username must be 3-32 characters";
    } elseif (!ctype_alnum($username)) {
        return "Error: Username must be alphanumeric";
    } elseif (strlen($password) < 8 || strlen($password) > 64) {
        return "Error: Password must be between 8 characters and 64 characters";
    } elseif ($password != $rpassword) {
        return "Error: Passwords do not match";
    }
    return "Success";
}


function isUserUsed($username)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT user_ID FROM User WHERE username=?";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result=$stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return (bool)$result->fetch_row();
}

function verifyPassword($username, $password)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT password FROM User WHERE username=?";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $retrievedAccount = $result->fetch_assoc();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return password_verify($password, $retrievedAccount["password"]);
}

function accountLogin($username, $password)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM User WHERE username=?";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $retrievedAccount = $result->fetch_assoc();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    if (password_verify($password, $retrievedAccount["password"])) {
        $_SESSION["username"] = $username;
        $_SESSION["user_ID"] = $retrievedAccount["user_ID"];
        $_SESSION["usertype_ID"] = $retrievedAccount["usertype_ID"];
        loginAttempt($retrievedAccount["user_ID"], 1);
        return true;
    } else {
        loginAttempt($retrievedAccount["user_ID"], 0);
        return false;
    }
}

function isUsers(): bool
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM User";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    if ($result->num_rows > 0)
        return true;
    else
        return false;
}

function loginAttempt($user_ID, $loginSuccessful): void
{
    $mysqlConn = startMySQL();
    $loginTime = time();
    $loginIP = ip2long($_SERVER["REMOTE_ADDR"]);
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "INSERT INTO Login_Attempts (user_ID, ip_address, login_time, login_successful) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($mysqlConn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iiii", $user_ID, $loginIP, $loginTime, $loginSuccessful);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
}



/*
 *
 *  Misc Functions
 *
 */



function logoutButton(): void
{
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: index.php");
    die();
}

function isLoggedIn(): bool
{
    return isset($_SESSION["user_ID"]);
}

function isPost(): bool
{
    return ($_SERVER["REQUEST_METHOD"] == "POST");
}

function displayErrorMessage($errorMsg): string
{
    return '<div class="alert alert-danger" role="alert">'.$errorMsg.'</div>';
}


function getUsernameFromUserID($user_ID)
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT username FROM User WHERE user_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bind_param("i", $user_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    if ($result->num_rows == 1)
        return $result->fetch_assoc();
    else
        return false;
}

function get_string_between($string, $start, $end): string
{
    // Thank you StackOverflow for this.
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function getAPIKey($serverID) {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT api_key FROM API_Keys WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "i", $serverID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows != 1) {
        header('Location: index.php');
        die();
    }
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->fetch_assoc()["api_key"];
}

function togglePortForwarding($serverID, $toggleState): void
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }

    $sql = "UPDATE VPN_Server_Settings SET portforwarding_on = ? WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "ii", $toggleState, $serverID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    refreshPageWithPOST($serverID);
}

function setDomainName($serverID, $domain): void
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }

    $sql = "UPDATE VPN_Servers SET domain_name = ? WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "si", $domain, $serverID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    refreshPageWithPOST($serverID);
}

function setProtocol($serverID, $protocol): void
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }

    if (changeVPNProtocol($serverID, strtolower($protocol), getAPIKey($serverID)) == "true") {
        $sql = "UPDATE VPN_Server_Settings SET vpn_protocol = ? WHERE server_ID = ?";
        $stmt = $mysqlConn->prepare($sql);
        mysqli_stmt_bind_param($stmt, "si", $protocol, $serverID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($mysqlConn);
    refreshPageWithPOST($serverID);
}

function setVPNPort($serverID, $vpnPort): void
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    if (changeVPNPort($serverID, $vpnPort, getAPIKey($serverID)) == "true") {
        $sql = "UPDATE VPN_Server_Settings SET vpn_port = ? WHERE server_ID = ?";
        $stmt = $mysqlConn->prepare($sql);
        mysqli_stmt_bind_param($stmt, "ii", $vpnPort, $serverID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($mysqlConn);
    refreshPageWithPOST($serverID);
}

function setWebPort($serverID, $webPort): void
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }

    $sql = "UPDATE VPN_Server_Settings SET webserver_port = ? WHERE server_ID = ?";
    $stmt = $mysqlConn->prepare($sql);
    mysqli_stmt_bind_param($stmt, "ii", $webPort, $serverID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    refreshPageWithPOST($serverID);
}

function setDNS($serverID, $dns): void
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }


    if (changeVPNDNS($serverID, $dns, getAPIKey($serverID)) == "true") {
        $sql = "UPDATE VPN_Server_Settings SET vpn_dns = ? WHERE server_ID = ?";
        $stmt = $mysqlConn->prepare($sql);
        mysqli_stmt_bind_param($stmt, "ii", $dns, $serverID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($mysqlConn);
    refreshPageWithPOST($serverID);
}

function isZeroServers() {
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT * FROM VPN_Servers";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return ($result->num_rows == 0);
}

function getNumberOfServers()
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT server_ID FROM VPN_Servers";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->num_rows;
}

function getNumberOfClients()
{
    $mysqlConn = startMySQL();
    if ($mysqlConn === false) {
        die("ERROR");
    }
    $sql = "SELECT client_ID FROM VPN_Clients";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    mysqli_stmt_close($stmt);
    mysqli_close($mysqlConn);
    return $result->num_rows;
}
