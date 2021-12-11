<?php

if(!function_exists('isSecure')) {
	function isSecure() {

			if((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')  || intval($_SERVER['SERVER_PORT']) == 443) {
				return true ;
			}
	return false ;
	}
}

// credit: https://stackoverflow.com/questions/1175096/how-to-find-out-if-youre-using-https-without-serverhttps
?>