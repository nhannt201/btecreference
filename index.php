<?php
header('Access-Control-Allow-Origin: *');

if (isset($_GET["url"]) ) { 
    $urlck = ngoaile($_GET["url"]);
$path = urldecode($urlck);
     $jsons;

if (urlExists($path)) {
 require_once 'mimini.php';
$browser=Mimini::open();
$browser->get($path);
 
// Execute and get response from API 
$website_content = $browser->getContent();//curl_exec($ch); 
 
    
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

    echo json_encode($output); 
	
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
