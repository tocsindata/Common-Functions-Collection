<?php 


// added already after phpv8
if (!function_exists('str_starts_with')) {
		function str_starts_with( $haystack, $needle ) {
		     $length = strlen( $needle );
		     return substr( $haystack, 0, $length ) === $needle;
		}
}


// added already after phpv8
if (!function_exists('str_ends_with')) {
		function str_ends_with( $haystack, $needle ) {
		    $length = strlen( $needle );
		    if( !$length ) {
		        return true;
		    }
		    return substr( $haystack, -$length ) === $needle;
		}
}

// REPLACE global $_REQUEST so that we can use it here (does not include $cookies but that can be added)
if (!function_exists('get_request')) {
	function get_request($get = NULL, $post = NULL) {
		$datarequest = array();

		// get data
		if(!is_null($get)) {
		// get is not null, check if it is an array..
			if(is_array($get)) {
			// it is an array let's add it the datarequest
			$datarequest['get'] = $get ;
			} else {
			// it's not an array,  get uri
			$get = urldecode($get);
			parse_str($get, $get);
			$datarequest['get'] = $get ;
			}
		} elseif(!empty($_GET)) {
		$datarequest['get'] = $_GET ;
		}

		// post data
		if(!is_null($post)) {
		// get is not null, check if it is an array..
			if(is_array($post)) {
			// it is an array let's add it the datarequest
			$datarequest['post'] = $post ;
			} else {
			// it's not an array, check if it is a url get uri
			$post = urldecode($post);
			parse_str($post, $post);
			$datarequest['post'] = $post ;
			}
		} elseif(!empty($_POST)) {
		$datarequest['post'] = $_POST ;
		}
	return $datarequest ;
	}

}



// create a url cache dynamicly with get and post options
if (!function_exists('get_url')) {
	function get_url($url = '', $cachefolderpath = "../cache/", $cachetime = 86400) {
		// wish we could still do this.... $datarequest = $_REQUEST;
		$datarequest = get_request();

		$cachefile = $cachefolderpath.md5($url.print_r($datarequest, true)).".cache" ;
	

		if(file_exists($cachefile) && (filemtime($cachefile) > (time() - $cachetime ))) {
		$data = get_data($cachefile, $datarequest) ;
		} else {
		$data = get_data($url, $datarequest) ;
		$fp = fopen($cachefile, 'w');
		fwrite($fp, $data);
		fclose($fp);
		}
	return $data ;
	}

}

// a function to replace file_get_contents and the curl version to avoid errors
if (!function_exists('get_data')) {
	function get_data($fileurl, $datarequest = NULL) {
	$out = '' ;
	$is_local = true ;
		// first figure out if the the fileurl is local...
		if (strpos($fileurl, 'http') === 0) {
		$is_local = false ;
		}
		
		// if the file is local, get the contents
		if($is_local == true) {
		  // Open the file for reading again
		  $file = fopen($fileurl, "r");
		
		  // Get the size of the file in bytes
		  $file_size = filesize($fileurl);
		
		  // Read the contents of the file
		  $out = fread($file, $file_size);
		}
		
		// if remote...
		if($is_local == false) {
		if(is_null($datarequest)) {
		 $ch = curl_init();
	
	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $fileurl);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
	
	    $out = curl_exec($ch);
	    curl_close($ch);
		} else {
		// $datarequest is not empty
			// check if get is set
			if(isset($datarequest['get']) && !empty($datarequest['get'])) {
				// check if the url already has it
				$urlget = http_build_query($datarequest['get']);
				$pos = strpos($fileurl, $urlget);
				if($pos == 0) {
					// get is not in the url
					if(str_starts_with($fileurl, "?")) {
					// url already has a ?
						if(str_ends_with($fileurl, "&")) {
						$fileurl .= $urlget ;
						} else {
						$fileurl .= "&".$urlget ;
						}
					} else {
					$fileurl .= "?".$urlget ;
					}
				}
			}
			
			// now check if post is empty... if it is get_data
			if(!isset($datarequest['post']) || empty($datarequest['post'])) {
			$out = get_data($fileurl);
			} else {
			// we have post data to add...
			$post = $datarequest['post'] ;
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
			            http_build_query($post));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $fileurl);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
			
			$out = curl_exec($ch);
			curl_close($ch);
			}
		}
		}
	
		return $out ;
	}
}
