<?php
if (isset($_GET["url"]) ) { //&& filter_var($_GET["url"], FILTER_VALIDATE_URL)) {
    $urlck = ngoaile($_GET["url"]);
$path = urldecode($urlck);
     $jsons;
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
    @$dom->loadHTML("<!DOCTYPE html><meta charset='UTF-8'>".$website_content);
    
    // Parse DOM to get Title
    $nodes = $dom->getElementsByTagName('title');
    $title = $nodes->item(0)->nodeValue;

    // Parse DOM to get Meta Description
    $metas = $dom->getElementsByTagName('meta');
	
	$scripts = $dom->getElementsByTagName('script');
	//found application/ld+json
	for ($i = 0; $i < $scripts->length; $i ++) {
					$scripts_name = $scripts->item($i);
					
					if ($scripts_name->getAttribute('type') == 'application/ld+json') {
						 $jsons = json_decode($scripts_name->nodeValue, true);
					}
				}
   // $body = "";
   /** for ($i = 0; $i < $metas->length; $i ++) {
        $meta = $metas->item($i);
		//print_r($meta->getAttribute('name'));
        if ($meta->getAttribute('name') == 'author') {
           echo $meta->getAttribute('content');
        }
    }**/
		//var_dump($metas)."<hr>";
    $tacgia = "";
    for ($i = 0; $i < $metas->length; $i ++) {
        $author = $metas->item($i);
	
        if (($author->getAttribute('name') == 'author') || ($author->getAttribute('name') == 'citation_author')) {
            $tacgia = $author->getAttribute('content');
        }
    }
	//Xu ly truong hop khac neu khong lay duoc ten tg
			if (strlen($tacgia) > 0) {} else {
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
			
			if (strlen($tacgia) > 0) {} else {
				if (!empty($jsons)) {
					if (!empty(($jsons['author']['name']))){
						$tacgia = ($jsons['author']['name']);
					}
					if (!empty(($jsons['@graph'][3]['name']))){
						$tacgia = ($jsons['@graph'][3]['name']);
					}
				}	
			}
	//Ket thuc xu ly



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
		} else if ($sname->getAttribute('property') == 'published_time') {
            $published_time = $sname->getAttribute('content');
			$published_time = date( "Y", strtotime( $published_time ) );
		} else  if ($sname->getAttribute('itemprop') == 'datePublished') {
            $published_time = $sname->getAttribute('content');
			$published_time = date( "Y", strtotime( $published_time ) );
		}
	}
	if (strlen($published_time) > 0) {} else {
			if (!empty($jsons)) {
					if (!empty(($jsons['datePublished']))){
						$published_time = ($jsons['datePublished']);
						$published_time = date( "Y", strtotime( $published_time ) );
					} else if (!empty(($jsons['@graph'][2]['datePublished']))){
						$published_time = ($jsons['@graph'][2]['datePublished']);
						$published_time = date( "Y", strtotime( $published_time ) );
					} else {
						$published_time = "n.d";
					}
			}	else {
				$published_time = "n.d";
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

function ngoaile($url) {
	if (strpos($url, 'www.academia.edu')) {
		$urln = explode('/', $url);
		return 'https://www.academia.edu/'.$urln[3].'/';
	} else {
			return $url;
	}
}
?>
