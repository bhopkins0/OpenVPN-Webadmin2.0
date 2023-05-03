# OpenVPN-Webadmin2.0
Easily manage your OpenVPN servers from a browser.

Developed on a LEMP stack running Ubuntu 22.04, PHP-FPM 8.1, and MySQL 8.

![](https://brenthopkins.me/img/github/openvpn-webadmin/status_page.jpg)

# What can you do with OpenVPN-Webadmin2.0?

* Easily add, revoke, and download clients
* Manage an unlimited amount of servers
* Manage an unlimited amount of clients
* View currently connected clients, along with: their bandwidth usage, connection duration, and IP address
* View VPN server bandwidth graph
* Check OpenVPN daemon status
* Start and stop OpenVPN daemon
* Change OpenVPN server port, protocol (TCP/UDP), and DNS server

NOTE: After changing the VPN port or protocol, you must redownload the OpenVPN client configurations. 
This is because changing the VPN port/protocol requires the client configuration to updated. Downloading the client configuration again will give you an updated version


# How to install:

0. Install `php-fpm` (8.0 or higher), `php-curl`, `php-mysql`, and `mysql-server`.
1. Add the MySQL database by importing `db_structure.sql`. You can do this with `mysql -u[user_name] -p [db_name] < db_structure.sql`
2. Add the repository into your web directory
3. Enter your MySQL credentials in `resources/functions.php`
4. Go to `your_url/signup.php` in a browser to create an account. This page will automatically become restricted after you sign up.

To add a VPN server:

0. Follow the instructions to add the API endpoint: https://github.com/bhopkins0/OpenVPN-Webadmin2.0-API
1. Set the VPN configuration settings in the VPN Settings page

Adding clients, revoking clients, and downloading clients can be done via Client Manager.

# Bugs to be fixed:

* Deleting VPN servers that are no longer online (such as deleted VPS instance running OpenVPN) from the Webadmin has issues. I believe it is caused from cURL timeouts.

# Screenshots:

## Home 

![](https://brenthopkins.me/img/github/openvpn-webadmin/home_page.jpg)

## VPN Settings

![](https://brenthopkins.me/img/github/openvpn-webadmin/vpn-settings-page.jpg)

## VPN Status

![](https://brenthopkins.me/img/github/openvpn-webadmin/status_page.jpg)

## Client Manager

![](https://brenthopkins.me/img/github/openvpn-webadmin/client_manager_page.jpg)



# Todo:

* Look into the possibility of installaton via Docker
* Pentest webadmin and API endpoint
* Make security enchancements for storing VPN client private keys and privileges for API scripts

