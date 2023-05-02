<?php

function deleteClient($domainName, $clientName, $apiKey)
{
    $url = "https://$domainName/api.php";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=deleteclient&clientName=$clientName";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
}

function getClientTemplate($domainName, $apiKey)
{
    $url = "https://$domainName/api.php";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $data = "apikey=$apiKey&action=getclienttemplate";
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
}

function generateClient($domainName, $clientName, $apiKey)
{
    $url = "https://$domainName/api.php";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=createclient&clientName=$clientName";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;

}

function getConnectedClientsTable($domainName, $apiKey) {
    $clientsTable = "";
    $url = "https://$domainName/api.php";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=connectedclients";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = array_filter(explode("\n", curl_exec($curl)));
    curl_close($curl);
    foreach ($resp as $client) {
        $client = explode(",", $client);
        $bytesrec = (int)$client[2] . ' Bytes';
        $bytessent = (int)$client[3] . ' Bytes';
        if ((int)$client[2] > 1000 && (int)$client[2] < 1000000) {
            $bytesrec = round(((int)$client[2] / 1000), 2) . ' KB';
        }
        if ((int)$client[2] > 1000000 && (int)$client[2] < 1000000000) {
            $bytesrec = round(((int)$client[2] / 1000000), 2) . ' MB';
        }
        if ((int)$client[2] > 1000000000 && (int)$client[2] < 1000000000000) {
            $bytesrec = round(((int)$client[2] / 1000000000), 2) . ' GB';
        }
        if ((int)$client[2] > 1000000000000 && (int)$client[2] < 1000000000000000) {
            $bytesrec = round(((int)$client[2] / 1000000000000), 2) . ' TB';
        }
        if ((int)$client[3] > 1000 && (int)$client[3] < 1000000) {
            $bytessent = round(((int)$client[3] / 1000), 2) . ' KB';
        }
        if ((int)$client[3] > 1000000 && (int)$client[3] < 1000000000) {
            $bytessent = round(((int)$client[3] / 1000000), 2) . ' MB';
        }
        if ((int)$client[3] > 1000000000 && (int)$client[3] < 1000000000000) {
            $bytessent = round(((int)$client[3] / 1000000000), 2) . ' GB';
        }
        if ((int)$client[3] > 1000000000000 && (int)$client[3] < 1000000000000000) {
            $bytessent = round(((int)$client[3] / 1000000000000), 2) . ' TB';
        }

        $clientsTable .= '<tr><td>' . $client[0] . '</td>';
        $clientsTable .= '<td>' . $client[1] . '</td>';
        $clientsTable .= '<td>' . $bytessent . '</td>';
        $clientsTable .= '<td>' . $bytesrec . '</td>';
        $clientsTable .= '<td>' . $client[4] . '</td></tr>';
    }
    return $clientsTable;
}

function isVPNDaemonRunning($domainName, $apiKey) {
    $url = "https://$domainName/api.php";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=status";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);

    return str_contains($resp, 'Active: active');
}



function changeVPNDNS($serverID, $vpnDNS, $apiKey) {

    $domainName = getServerInfo($serverID)["domain_name"];
    $convertedDNS = long2ip($vpnDNS);
    $url = "https://$domainName/api.php";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=setvpndns&vpnDNS=$convertedDNS";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);

    var_dump($resp);
    return $resp;
}

function changeVPNPort($serverID, $vpnPort, $apiKey) {

    $domainName = getServerInfo($serverID)["domain_name"];
    $ipAddress = long2ip(getServerInfo($serverID)["ip_address"]);
    $url = "https://$domainName/api.php";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=setvpnport&vpnPort=$vpnPort&setIP=$ipAddress";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);

    var_dump($resp);
    return $resp;
}

function changeVPNProtocol($serverID, $vpnProtocol, $apiKey) {

    $domainName = getServerInfo($serverID)["domain_name"];
    $url = "https://$domainName/api.php";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=setvpnprotocol&vpnProtocol=$vpnProtocol";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);

    var_dump($resp);
    return $resp;
}

function getBandwidthGraph($domainName, $apiKey) {


    $url = "https://$domainName/api.php";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=bwgraph";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $resp = curl_exec($curl);
    curl_close($curl);

    return $resp;
}

function toggleVPNStatus($serverID, $apiKey) {

    $domainName = getServerInfo($serverID)["domain_name"];
    $url = "https://$domainName/api.php";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "apikey=$apiKey&action=toggleserverstatus";

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_exec($curl);
    curl_close($curl);

    refreshPageWithPOST($serverID);
}
