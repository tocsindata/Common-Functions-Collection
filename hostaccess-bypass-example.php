<?php

/*

**************************************************************************
FILENAME:     hostaccess-bypass-example.php / index.php / whatever.php
System Name:  Bypass WHM /etc/hosts.allow Without Service Subdomains on port 80
Site URL:     https://RANDOM.local
Version:      2.7.8 (https://tocsindata.com/version.php?v=2.7.8)
Description:  See Below
Author:       Daniel Foscarini (Tocsin Data)
Author URI:   https://tocsindata.com/
Text Domain:  Bypass Host Access
License:      All Rights Reserved Copyright 2022 TocsinData.com
Requires PHP: 7.0/Linux (Redhat/Centos)/ WHM/cPanel

Usage Instructions and Important Information:

You must be familiar with WHM Host Access Firewall settings and various Linux
bash commands... if this breaks your system, you own the system, This script is 
provided for educational use only, with no guarantees. If you use this script in
production and it locks you out, it's your fault not ours.

Step #1

Create a new cPanel account on your WHM hosting server. Use a domain that is a 
random alphanumeric dot local for best security, it must start with a letter.

example: jbhurenk83qo85qm.local

You may need to "Tweek Settings" to allow domains in the dot local environment 

Step #2

On your computer change your hosts file to point to the correct IP for that local 
domain you used in the above account ... test to see if you can access.

Step #3

Login into WHM and set the firewall settings for allows, do not include any denies.
This should set up a useless firewall set of rules that allows anyone to access 
WHM and cPanel, this allows you to set this via this script if things go wrong.

Step #4

While still WHM root  open terminal and type:

cat /etc/hosts.allow

Copy the contents of the cat output to a file called openall.txt and save it in 
home of the local user ABOVE public_html not below (as that user not root)

example: 
/home/somerandomuser/openall.txt

Step #5

create a copy of openall.txt as current.txt in the local account (as that user 
not root)

example: 
/home/somerandomuser/current.txt

Step #6

As Root alter your firewall as you see fit, make sure you include allows for your
IP range that you currently use, even if your ISP provides dynamic IPs

cat the hosts.allow file again, and this time save it in the local as
secure.txt

example: 
/home/somerandomuser/secure.txt

Step #7
Configure this script below, and save it as index.php in the home public_html folder
, you can also add a htaccess secure password the public_html folder as well via
 the cpanel interface



Step #8
( HOT TIP: USE NANO INSTEAD OF VIM....
export VISUAL=nano; crontab -e
)
Write a bash script for root that copies the current.txt to /etc/hosts.allow with
 the correct user group permissions every x minutes

EXAMPLE: /root/hosts_access_bypass.sh

#!/bin/bash

cat /home/YOURLOCALUSERACCOUT/current.txt > /etc/hosts.allow

Then ... crontab -e ( note it's * SLASH 5 with the SLASH FIVE being a /5 )

*SLASH5 * * * * /root/hosts_access_bypass.sh

IMPORTANT.... Do not set it to every minute, encase you need time to change stuff
Dont forget chmod 755 ./hosts_access_bypass.sh in /root or your home

Step #9
Use a VPN to change your IP and test to see if WHM cPanel etc are secure, and 
you can access the local still

Step #10
Test it. Use this script in the dot local to change the current settings to openall, 
and then back to secure etc... if all goes well your done
*/

// #############################################################################
// #############################################################################
// ##################     CONFIG STARTS     #################################### 
// #############################################################################
// #############################################################################

// we strongly recoomend that you also use htaccess password protect on public_html as well

$username = "YOURUSERNAME"; // use a random password generator for alphanumeric for best security
$password = "YOURPASSWORD"; // make it random!
$openall = "../openall.txt";
$secure = "../secure.txt";
$current = "../current.txt";
$index = "hostaccess-bypass-example.php" ; // filename of this script... you can rename it
$session_timeout = 120 ; // seconds

// #############################################################################
// #############################################################################
// ##################     CONFIG ENDS     #################################### 
// #############################################################################
// #############################################################################

// #############################################################################
// #############################################################################
// ##################     FUNCTIONS START     ################################## 
// #############################################################################
// #############################################################################

function OpenALL() {
global $openall ;
global $current ;
	if (!copy($openall, $current)) {
	    return false;
	}
return true ;
}


function SecureFirewall() {
global $secure ;
global $current ;
	if (!copy($secure, $current)) {
	    return false;
	}
return true ;
}

function UserAccess() {

	if(IsUserAuthenticated()) {
	return true ;
	}

return false ;
}

function ExtraSec(){
global $index ;
	
	if($_SERVER["HTTPS"] != "on")
	{
	    header("Location: https://" . $_SERVER["HTTP_HOST"] ."/". $index);
	    exit();
	}

return ;
}


function IsUserAuthenticated() {
global $username ;
global $password ;
global $session_timeout ;

   if(isset($_SESSION['authenticate']) && $_SESSION['authenticate'] == 1 && isset($_SESSION['expire']) && $_SESSION['expire'] > time()) {
		return true ;
	}

	if(isset($_POST['user']) && $_POST['user'] == $username) {
		if(isset($_POST['password']) && $_POST['password'] == $password) {
			$_SESSION["authenticate"] = 1; 
			$_SESSION['start'] = time();
			$_SESSION['expire'] = $_SESSION['start'] + $session_timeout;
			return true ;
		}
	}

return false ;
}

// #############################################################################
// #############################################################################
// ##################     FUNCTIONS END       ################################## 
// #############################################################################
// #############################################################################

ExtraSec() ;
session_start();
?><html>
<head>
<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
</head>
<body>


<?php
if(UserAccess()) {

$msg = "";
$displaymsg = 0 ;

if(isset($_POST['openall']) && $_POST['openall'] == 1 && OpenALL() && IsUserAuthenticated()) {
$msg = "Firewall Config has been set to open all related ports.";
$displaymsg = 1 ;
}


if(isset($_POST['setsecure']) && $_POST['setsecure'] == 1 && SecureFirewall() && IsUserAuthenticated()) {
$msg = "Firewall Config has been set to the default security settings.";
$displaymsg = 1 ;
}

if($displaymsg == 0) {

?>

<section class="vh-100" style="background-color: #9A616D;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
			<form class="form-horizontal" action="<?php echo $index ; ?>" method="post">
			<fieldset>
			
			<!-- Form Name -->
			<legend>Bypass Firewall</legend>
			
			<!-- Button (Double) -->
			<div class="form-group">
			  <label class="col-md-4 control-label" for="button1id"></label>
			  <div class="col-md-8">
			    <button id="openall" name="openall" class="btn btn-success" value="1">Open ALL</button>
			    <button id="setsecure" name="setsecure" class="btn btn-danger" value="1">Secure Firewall</button>
			  </div>
			</div>
			
			</fieldset>
			</form>
		</div>
	</div>
</div>
</section>

<?php
} else {

?>
<section class="vh-100" style="background-color: #9A616D;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
			<?php echo $msg ; ?>
		</div>
	</div>
</div>
</section>

<?php
}
					} else {
?>

<section class="vh-100" style="background-color: #9A616D;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
        <div class="card" style="border-radius: 1rem;">
          <div class="row g-0">
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-black">

                <form action="<?php echo $index ; ?>" method="post">

                  <div class="d-flex align-items-center mb-3 pb-1">
                    <i class="fas fa-cubes fa-2x me-3" style="color: #ff6219;"></i>
                    <span class="h1 fw-bold mb-0">Login</span>
                  </div>

                  <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Sign into your account</h5>

                  <div class="form-outline mb-4">
                    <input type="text" id="user"  name="user" class="form-control form-control-lg" />
                    <label class="form-label" for="form2Example17">Username</label>
                  </div>

                  <div class="form-outline mb-4">
                    <input type="password" id="password"  name="password" class="form-control form-control-lg" />
                    <label class="form-label" for="form2Example27">Password</label>
                  </div>

                  <div class="pt-1 mb-4">
                    <input class="btn btn-dark btn-lg btn-block" type="submit" value="Login">
                  </div>

                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
}
?>
</body>
</html>
