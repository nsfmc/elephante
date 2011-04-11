<?php 
// an elephante never forgets (and never forgives, either)
// (c) 2011, marcos ojeda
// licensed under an mit license

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");
date_default_timezone_set("America/New_York");


function get_tumblr($tumblr_url, $args= "", $lastmodtime = null){
  $tumblr_url .= "api/read/json?{$args}";
  
  $curly = curl_init();
  $curlyopts = array(
    CURLOPT_URL => $tumblr_url,
    CURLOPT_USERAGENT => "elephante/0.5 (generic.cx/elephante; elephante@generic.cx)",
    CURLOPT_RETURNTRANSFER => true, // output result in var, not browser
    CURLOPT_FILETIME => true   // get remote file access time
  );
  
  if (isset($lastmodtime)){
    $curlyopts[CURLOPT_TIMECONDITION] = CURL_TIMECOND_IFMODSINCE;
    $curlyopts[CURLOPT_TIMEVALUE] = $lastmodtime;
  }
  
  curl_setopt_array($curly, $curlyopts);
  
  $json = curl_exec($curly);
  $status = curl_getinfo($curly, CURLINFO_HTTP_CODE);
  $modtime = curl_getinfo($curly, CURLINFO_FILETIME);
  
  curl_close($curly);
  
  return array(
    "status" => $status,
    "modtime" => $modtime,
    "tumblr" => trim_tumblr($json),
  );
}


function trim_tumblr($response, $TO_OBJ = true){
  // collect a tumblr api json call and return either the text or an object
  // assume that $response is tumblr_api_read = {....};
  $response = trim($response);
  $response = substr($response, strpos($response, "{")); // remove var
  $response = substr($response, 0, strlen($response)-1); // remove semicolon
  return $TO_OBJ ? json_decode($response, TRUE) : $response;
}


function check_cache($filename = "cache/mytumblr.json"){
  if(file_exists($filename)){
    return json_decode(file_get_contents($filename), true);
  }else{
    return false;
  }
}


function crawl_tumblr($tumblr_url, $cachefile="cache/mytumblr.json"){
  $num = 50;
  $args = "num={$num}";
  $init = get_tumblr($tumblr_url, $args);
  // TODO obviously doing some error checking
  
  $tumblr = $init["tumblr"];
  $tumblr["modtime"] = $init["modtime"];
  
  if($tumblr["posts-total"] <= $num){
    $tumblr["modtime"] = $init["modtime"];
  }else{
    for($i = 1; $i < ceil($tumblr["posts-total"] / $num); $i += 1){
      $next_urlargs = "num={$num}&start=".($num * $i);
      $next_tumblr = get_tumblr($tumblr_url, $next_urlargs);
      $tumblr["posts"] = array_merge($tumblr["posts"], $next_tumblr["tumblr"]["posts"]);
      
      $naptime = mt_rand(0,1000); // try to be polite when crawling?
      usleep($naptime);
    }
  }
  // TODO abstract this away
  file_put_contents($cachefile, json_encode($tumblr));
  chmod($cachefile, 0666);
}

function elephante($tumblr_url, $cachefile="cache/mytumblr.json"){
  $cache = check_cache($cachefile);
  if($cache){
    $newtumblr = get_tumblr($tumblr_url,"", $cache["modtime"]);
    
    
    if($newtumblr["status"] == 200){
      // a new tumblr file was loaded, now update cache time
      $cache["modtime"] = $newtumblr["modtime"];
      
      $firstpostid = $cache["posts"][0]["id"];
      for(
        $i=0; $i < count($newtumblr["tumblr"]["posts"]) &&
          $newtumblr["tumblr"]["posts"][$i]["id"] !== $firstpostid;
          $i += 1){
        
        $thispost = $newtumblr["tumblr"]["posts"][$i];
        
        if($thispost["id"] > $firstpostid){ // add this post
          $cache["posts"] = array_merge(array($thispost), $cache["posts"]);
        }
        if($thispost["id"] < $firstpostid){
          // this new post is older than our first saved post
          // weird state problem, so maybe should force recrawl?
        }
      }
      
      // do low-key consistency check here and then save or recrawl
      if($newtumblr["tumblr"]["posts-total"] == count($cache["posts"])){
        file_put_contents($cachefile, json_encode($cache));
      }else{
        crawl_tumblr($tumblr_url, $cachefile);
      }
    }else if($newtumblr["status"] == 304){
      // nothing's changed
    }else{
      // uh oh (likely api/tumblr fail), return cache
    }
  }else{
    // the cache is borked, try crawling?
    // also, this assumes that cachefile is writable, but doesn't
    // give an error if it's not :(
    // TODO break nicely, maybe add a post that says "it's broken!"
    crawl_tumblr($tumblr_url, $cachefile);
    return check_cache($cachefile);
  }
  return $cache;
}

?>