<?php
if (isset($_GET["url"]) && filter_var($_GET["url"], FILTER_VALIDATE_URL)) {
    
$path = urldecode($_GET["url"]);
     
/**$queryString = http_build_query([ 
    'access_key' => '05a2190b974c4b6c984ba2b7a81bd9d3', 
    'url' => $path , 
]); 
 
// API URL with query string 
$apiURL = sprintf('%s?%s', 'http://api.scrapestack.com/scrape', $queryString); **/
//$headers = @get_headers($path);
  
// Use condition to check the existence of URL
//if($headers && strpos( $headers[0], '200')) {
  //  $status = "URL Exist";
if (urlExists($path)) {
 require_once 'mimini.php';
$browser=Mimini::open();
$browser->get($path);
//echo $browser->getContent();
// Create a new cURL resource 
//$ch = curl_init(); 
 
// Set URL and other appropriate options 
//curl_setopt($ch, CURLOPT_URL, $browser->getContent()); //$apiURL); 
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
 
// Execute and get response from API 
$website_content = $browser->getContent();//curl_exec($ch); 
 
// Close cURL resource 
//curl_close($ch); 
    
    // Load HTML to DOM Object
    $dom = new DOMDocument();
    @$dom->loadHTML($website_content);
    
    // Parse DOM to get Title
    $nodes = $dom->getElementsByTagName('title');
    $title = $nodes->item(0)->nodeValue;

    // Parse DOM to get Meta Description
    $metas = $dom->getElementsByTagName('meta');
   // $body = "";
    /**for ($i = 0; $i < $metas->length; $i ++) {
        $meta = $metas->item($i);
        if ($meta->getAttribute('name') == 'description') {
            $body = $meta->getAttribute('content');
        }
    }**/
	
    $tacgia = "";
    for ($i = 0; $i < $metas->length; $i ++) {
        $author = $metas->item($i);
        if ($author->getAttribute('name') == 'author') {
            $tacgia = $author->getAttribute('content');
        } else {
			for ($i = 0; $i < $metas->length; $i ++) {
				$author = $metas->item($i);
					if ($author->getAttribute('property') == 'article:author') {
						$tacgia = $author->getAttribute('content');
					} else {
						for ($i = 0; $i < $metas->length; $i ++) {
								$author = $metas->item($i);
									if ($author->getAttribute('name') == 'sailthru.author') {
										$tacgia = $author->getAttribute('content');
									}
							}
					}
			}
		}
    }

	$site_name = "";
    for ($i = 0; $i < $metas->length; $i ++) {
        $sname = $metas->item($i);
        if ($sname->getAttribute('property') == 'og:site_name') {
            $site_name = $sname->getAttribute('content');
        }
    }
	
	$published_time = "";
	for ($i = 0; $i < $metas->length; $i ++) {
        $sname = $metas->item($i);
        if ($sname->getAttribute('property') == 'article:published_time') {
            $published_time = $sname->getAttribute('content');
			$published_time = date( "Y", strtotime( $published_time ) );
			if ($published_time == "") {
				$published_time = "n.d";
			}
        } else {
			 for ($i = 0; $i < $metas->length; $i ++) {
					$sname = $metas->item($i);
					if ($sname->getAttribute('name') == 'sailthru.date') {
						$published_time = $sname->getAttribute('content');
						$published_time = date( "Y", strtotime( $published_time ) );
						if ($published_time == "") {
							$published_time = "n.d";
						}
					} else {
						//Cach lay date time public kieu khac
						/**$xpath = new DOMXpath($dom);
						$jsonScripts = $xpath->query( '//script[@type="application/ld+json"]' );
						$json = trim( $jsonScripts->item(1)->nodeValue );

						$data = json_decode( $json, true );
						//print_r($data);
						// you can now use this array to query the data you want
						//$published_time = substr( $data['datePublished'], 0, 4);
						//break;
						$u = $data['datePublished'];
		
						//$published_time = $datePublished;//date('Y', strtotime($datePublished));
						//Ket thuc lay**/
					}
				}
		}
    }
    
    // Parse DOM to get Images
   /** $image_urls = array();
    $images = $dom->getElementsByTagName('img');
     
     for ($i = 0; $i < $images->length; $i ++) {
         $image = $images->item($i);
         $src = $image->getAttribute('src');
         
         if(filter_var($src, FILTER_VALIDATE_URL)) {
             $image_src[] = $src;
         }
     }**/
    $parse = parse_url($path);

    $output = array(
        'title' => $title,
		'auhtor' => $tacgia,
		'sitename' => $site_name,
		'published_time' => $published_time,
		'url' => $path,
		'domain' =>  $parse['host']
    );

	}
else {
      $output = array(
        'title' => null,
		'auhtor' => null,
		'sitename' => null,
		'published_time' => null,
		'url' => $path,
		'domain' =>  $parse['host']
    );
}

	//print_r($output);
    echo json_encode($output); 
	//echo $data;
}


function get_http_response_code($theURL) {
    $headers = @get_headers($theURL);
    return substr($headers[0], 9, 3);
}
/**
 * Check that given URL is valid and exists.
 * @param string $url URL to check
 * @return bool TRUE when valid | FALSE anyway
 */
function urlExists ( $url ) {
    // Remove all illegal characters from a url
    $url = filter_var($url, FILTER_SANITIZE_URL);

    // Validate URI
    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE
        // check only for http/https schemes.
        || !in_array(strtolower(parse_url($url, PHP_URL_SCHEME)), ['http','https'], true )
    ) {
        return false;
    }

    // Check that URL exists
    $file_headers = @get_headers($url);
    return !(!$file_headers || $file_headers[0] === 'HTTP/1.1 404 Not Found');
}
?>
