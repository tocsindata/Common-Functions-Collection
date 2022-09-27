<?php
/*

**************************************************************************
**************************************************************************
**************************************************************************

Filename:     cloner_script_master.php
System Name:  CLONER SCRIPT
Site URL:     https://example.com/
Version:      2.4.9 (https://tocsindata.com/version.php?v=2.4.9)
Description:  This script clones tables from the source db to the target db
WARNING:      ANY CHANGES ON THE SOURCE DB THAT HAVE BEEN ALREADY UPLOADED WILL BE IGNORED
Author:       Daniel Foscarini (Tocsin Data)
Author URI:   https://tocsindata.com/
Text Domain:  CRON
License:      MIT 
Requires PHP: 7.0

MIT License

Copyright (c) 2022 Tocsin Data / DAniel Foscarini

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
**************************************************************************
**************************************************************************
**************************************************************************

*/


// config starts

// db config...
$config = array();

// ... FROM 
$config['source']['hostname'] = "127.0.0.1";
$config['source']['username'] = "sourceuser";
$config['source']['password'] = "sourcepassword";
$config['source']['database'] = "sourcedatabase";

// ... TO
$config['target']['hostname'] = "example.com"; 
$config['target']['username'] = "targetuser"; 
$config['target']['password'] = "targetpassword"; 
$config['target']['database'] = "targetdatabase";


// misc 
$config['cache'] = "/home/cronlocal/cache/" ; // full path of the cache folder ending in a back slash "/home/foobar/cache/";
$config['lockfile'] = $config['cache']."db_cloner.".$config['source']['database'].".lock" ; // filename or full path of the lock file for this script
$config['limit'] = 10 ; // The LIMIT of rows to deal with PER table
$config['refresh'] = 61 ; // how long in seconds before we refresh (only works if opened by browser not cron)

// config ends

// functions start

function Lock() {
global $config ;
$lockfile = $config['lockfile'] ;
touch($lockfile);
	$status = file_get_contents($lockfile);
	if($status == "ON") {
			if(RunFrom() == 3) {
				echo "<hr>Script is already running... (".$lockfile.")";
			}
		exit();
		die();
	} else {
			if(RunFrom() == 3) {
				echo "<hr>SCRIPT IS NOT LOCKED";
			}
}

	$fp = fopen($lockfile, 'w');
	fwrite($fp, 'ON');
	fclose($fp);
return ;
}

function UnLock() {
global $config ;
$lockfile = $config['lockfile'] ;
touch($lockfile);

	$fp = fopen($lockfile, 'w');
	fwrite($fp, 'OFF');
	fclose($fp);
return ;
}

function RunFrom() {
$out = 0 ;
if (php_sapi_name() == 'cli') {   
   if (isset($_SERVER['TERM'])) {   
      // The script was run from a manual invocation on a shell
		$out = 1 ;   
   } else {   
      // The script was run from the crontab entry 
		$out = 2 ;    
   }   
} else { 
   // The script was run from a webserver, or something else 
	   $out = 3 ;
}
return $out ;
}

function Refresh() {
global $config ;
	if(RunFrom() == 3) {
	$seconds = $config['refresh'] ;
	header("Refresh:".$seconds);

			if ( extension_loaded('pdo') ) {
			echo "<hr>PDO FOUND, OK TO RUN";
			} else {
			echo "<hr>ERROR: PDO FUNCTION NOT FOUND IN YOUR VERSION OF PHP";
			UnLock();
			exit();
			die();
			}

	} 
return ;
}

function GetTables() {
global $db1 ;
global $config ;
$tables = array();

	$sql = "SHOW TABLES FROM `".$config['source']['database']."` ; ";
	 $result = $db1->query($sql);
	 //$stmt = $db1->prepare($sql);
    //$result = $stmt->execute($stmt);
	while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$tables[] = $row['Tables_in_'.$config['source']['database']];
		}
	

return $tables ;
}

function CreateTable($table) {
global $db1 ;
global $db2 ;
global $config ;

	// if table does exists... return and do nothing
	$donetablefile = $config['cache']."-".$config['source']['database'].$table.".created" ;
	if(file_exists($donetablefile)) {
		return false ;
	}

	// if table does not exists ... create it, store flatfile, unlock script and exit
	$sql = "SHOW CREATE TABLE `".$table."` ; ";
	$result = $db1->query($sql);
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$targetsql = $row['Create Table'];
			$db2->query($targetsql);
			$fp = fopen($donetablefile, 'w');
			fwrite($fp, $targetsql);
			fclose($fp);

			if(RunFrom() == 3) {
				echo "<hr>Created Table: <b>".$table."</b>, refreshing for next loop";
			}

				return true ;
		
	}

return false ; // should never be called, so you could put an error check here
}

function GetFields($table) {
global $db2 ; // we use db2 because it is not yet live so less resources are used at the same time
$fields = array();

	$sql = "SHOW FIELDS FROM `".$table."` ; ";
	$result = $db2->query($sql);
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$fields[] = $row['Field'];
		}
	

return $fields ;
}


function GetStart($table) {
global $db2 ;
global $config ;
$start = 0 ;
	$sql = "SELECT COUNT(*) AS 'start' FROM `".$table."` ;";
	//$sql = "SELECT `TABLE_ROWS` AS 'start' FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '".$config['target']['database']."' AND `TABLE_NAME` LIKE '".$table."';";
	$result = $db2->query($sql);
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$start = $row['start'];
		}
	

return $start ;
}

function GetTotal($table) {
global $db1 ;
global $config ;
$start = 0 ;
	$sql = "SELECT COUNT(*) AS 'start' FROM `".$table."` ;";
	//$sql = "SELECT `TABLE_ROWS` AS 'start' FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '".$config['source']['database']."' AND `TABLE_NAME` LIKE '".$table."';";
	$result = $db1->query($sql);
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$start = $row['start'];
		}
	

return $start ;
}




function CreateInsertSQL($table) {
global $db1 ;
global $db2 ;
global $config ;
 
$limit = $config['limit'] ;
$start = GetStart($table) ;
$total = GetTotal($table) ;
$fields = GetFields($table) ;


			$insertsql = "INSERT IGNORE INTO `".$table."` (";
			foreach($fields as $field) {
				$insertsql .= $field.", ";
			}
			$insertsql = rtrim($insertsql, ", ");

			$insertsql .= ") VALUES (" ;
			foreach($fields as $field) {
				$insertsql .= ":".$field.", ";
			}
			$insertsql = rtrim($insertsql, ", ");
			$insertsql .= ")  ON DUPLICATE KEY IGNORE;" ;


return $insertsql ;
}

function MyClone($table) {
global $db1 ;
global $db2 ;
global $config ;
 
$limit = $config['limit'] ;
$start = GetStart($table) ;
$total = GetTotal($table) ;
$fields = GetFields($table) ;


	$sql = "SELECT * FROM `".$table."` LIMIT ".$start.", ".$limit." ; ";
			if(RunFrom() == 3) {
				//echo "<hr> Debug FROM SOURCE SQL: <pre>".print_r($sql, true)."</pre> <br>";
			}
		$select_results = $db1->query($sql);
		while ($row = $select_results->fetch(PDO::FETCH_ASSOC)) {
			$insertsql = "INSERT IGNORE INTO `".$table."` (";
			foreach($fields as $field) {
				$insertsql .= "`".$field."`, ";
			}
			$insertsql = rtrim($insertsql, ", ");

			$insertsql .= ") VALUES (" ;
			foreach($fields as $field) {
				if(is_null($row[$field]) || empty($row[$field])) {
					if(is_null($row[$field])) {
					$insertsql .= "NULL, ";
					} else {
					$insertsql .= "'', ";
					}
				} else {
					if(is_numeric($row[$field])) {
						$insertsql .= addslashes($row[$field]).", " ;
					} else {
						$insertsql .= "'".addslashes($row[$field])."', " ;
					}
				}
			}
			$insertsql = rtrim($insertsql, ", ");
			$insertsql .= ") ;" ;
			$db2->query($insertsql);
			if(RunFrom() == 3) {
				//echo "<br> Debug Exec (InsertSQL): <pre>".print_r($insertsql, true)."</pre> <hr>";
			}
		}
	
return ;
}
// functions end

// refresh this webpage... if it is a webpage....
Refresh();

// make sure this script isn't already running...
Lock();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// connect to db1 (FROM)
$dsn1 = 'mysql:host='.$config['source']['hostname'].';dbname='.$config['source']['database'];
$db1 = new PDO($dsn1, $config['source']['username'], $config['source']['password']);
if ($db1->connect_errno) {
if(RunFrom() == 3) {
	echo "<hr>".$db1->connect_errno;
}
	UnLock();
	exit();
	die();
}
//$db1->set_charset("utf8mb4");		


// connect to db2 (TO)
$dsn2 = 'mysql:host='.$config['target']['hostname'].';dbname='.$config['target']['database'];
$db2 = new PDO($dsn2, $config['target']['username'], $config['target']['password']);
if ($db2->connect_errno) {
if(RunFrom() == 3) {
	echo "<hr>".$db2->connect_errno;
}
	UnLock();
	exit();
	die();
}
//$db2->set_charset("utf8mb4");		 			


// MISC VARIABLES
// tables to be cloned....
$tables = GetTables();

// run loops...
foreach($tables as $table) {
	if(CreateTable($table)) {

			if(RunFrom() == 3) {
				echo "<hr>CREATE TABLE <b>".$table."</b> <br> RETURNED TRUE";
			}
			// shut down mysql...
			if(is_resource($db1) && get_resource_type($db1)==='mysql link'){
			 mysqli_close($db1); //Procedural style 
			}
			
			if(is_resource($db2) && get_resource_type($db2)==='mysql link'){
			 mysqli_close($db2); //Procedural style 
			}
			
			// remove the lock
			UnLock();	
			exit();
			die(); 
	}
}


foreach($tables as $table) {

$start = GetStart($table) ;
$total = GetTotal($table) ;	
  	
		if($start < round($total)) {
			MyClone($table) ;
			if(RunFrom() == 3) {
				echo "<hr>Cloned <b>".$table."</b> (".$start."/".$total.")";
			}
		} else {
			if(RunFrom() == 3) {
				echo "<hr>Skipped <b>".$table."</b> (".$start."/".$total.")";
			}
		}

}

// shut down mysql...
if(is_resource($db1) && get_resource_type($db1)==='mysql link'){
 mysqli_close($db1); //Procedural style 
}

if(is_resource($db2) && get_resource_type($db2)==='mysql link'){
 mysqli_close($db2); //Procedural style 
}

// remove the lock
UnLock();








