<?php

  // Handle the parsing of the _ga cookie or setting it to a unique identifier
  // Generate UUID v4 function - needed to generate a CID when one isn't available
  function gaGenUUID() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
      mt_rand( 0, 0xffff ),
      mt_rand( 0, 0x0fff ) | 0x4000,
      mt_rand( 0, 0x3fff ) | 0x8000,
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }
  function gaParseCookie() {
    if (isset($_COOKIE['_ga'])) {
      list($version,$domainDepth, $cid1, $cid2) = split('[\.]', $_COOKIE["_ga"],4);
      $contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1.'.'.$cid2);
      $cid = $contents['cid'];
    }
    else $cid = gaGenUUID();
    return $cid;
  }

  $url = "https://newsio-app-bucket.s3.amazonaws.com/production/updates.json";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL,$url);
  $result=curl_exec($ch);
  curl_close($ch);
  $dlLinks = json_decode($result, true);
  $dlLinux = $dlLinks['linux-x64-production']['install'];
  $dlLinuxVersion = $dlLinks['linux-x64-production']['version'];
  $dlMacOs = $dlLinks['darwin-x64-production']['install'];
  $dlMacOsVersion = $dlLinks['darwin-x64-production']['version'];
  $dlWindows = $dlLinks['win32-x64-production']['install'];
  $dlWindowsVersion = $dlLinks['win32-x64-production']['version'];
  $dlAndroid = "https://play.google.com/store/apps/details?id=com.newsio.newsio&utm_source=website&utm_campaign=android-shortlink";
  $dlIos = "https://itunes.apple.com/app/newsio/id1085026307";

  $user_agent     =   $_SERVER['HTTP_USER_AGENT'];
  function getOS() {
      global $user_agent;
      $os_platform    =   "Unknown OS Platform";
      $os_array       =   array(
                              '/windows nt 10/i'     =>  'windows',
                              '/windows nt 6.3/i'     =>  'windows',
                              '/windows nt 6.2/i'     =>  'windows',
                              '/windows nt 6.1/i'     =>  'windows',
                              '/windows nt 6.0/i'     =>  'windows',
                              '/macintosh|mac os x/i' =>  'macos',
                              '/linux/i'              =>  'linux',
                              '/ubuntu/i'             =>  'linux',
                              '/iphone/i'             =>  'ios',
                              '/ipod/i'               =>  'ios',
                              '/ipad/i'               =>  'ios',
                              '/android/i'            =>  'android'
                          );
      foreach ($os_array as $regex => $value) {
          if (preg_match($regex, $user_agent)) {
              $os_platform    =   $value;
          }
      }
      return $os_platform;
  }

  if (isset($_GET['platform'])) {
    $user_os = $_GET['platform'];
  }
  else $user_os = getOS();


  if ($user_os == "linux") {
    $downloaded = "Linux";
    $version = $dlLinuxVersion;
  } else if ($user_os == "macos") {
    $downloaded = "MacOS";
    $version = $dlMacOsVersion;
  } else if ($user_os == "ios"){
  } else if ($user_os == "android"){
  } else {
    $downloaded = "Windows";
    $version = $dlWindowsVersion;
  }

  if (isset($downloaded)) {
    $data = array(
      'v' => 1,
      'tid' => 'UA-72952347-1',
      'cid' => gaParseCookie(),
      't' => 'event'
    );
    $data['ec'] = 'Download App';
    $data['ea'] = $downloaded;
    $data['el'] = $version;
    $url = 'http://www.google-analytics.com/collect'; // This is the URL to which we'll be sending the post request.
    $content = http_build_query($data); // The body of the post must include exactly 1 URI encoded payload and must be no longer than 8192 bytes. See http_build_query.
    $content = utf8_encode($content); // The payload must be UTF-8 encoded.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded'));
    curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
    curl_setopt($ch,CURLOPT_POST, TRUE);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $content);
    curl_exec($ch);
    curl_close($ch);
  }

  if ($user_os == "linux") {
    header('Location: ' . $dlLinux);
  } else if ($user_os == "macos") {
    header('Location: ' . $dlMacOs);
  } else if ($user_os == "ios"){
    header('Location: ' . $dlIos);
  } else if ($user_os == "android"){
    header('Location: ' . $dlAndroid);
  } else {
    header('Location: ' . $dlWindows);
  }




?>
