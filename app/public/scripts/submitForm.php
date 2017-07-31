<?php
  // Set destinations
  $nextPage = "https://front.belloforwork.com/create-team";
  $errorPage = "https://front.belloforwork.com/create-team?error";
  $thanksPage = "https://belloforwork.com/thanks";


  $email = $_POST['leadEmail'];
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

  // Check if email valid according to PHP Filter
  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Save valid email address
    setcookie("leadEmail", $email, time() + (86400), "/", ".belloforwork.com");


    // Check Email Info
    // Set API Access Key
    $access_key = 'b3d183c0e9393b26af42da359d0ff2c4';
    // Initialize CURL:
    $ch = curl_init('http://apilayer.net/api/check?access_key='.$access_key.'&email='.$email.'');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Store the data:
    $json = curl_exec($ch);
    curl_close($ch);
    // Decode JSON response and set:
    $validationResult = json_decode($json, true);
    $emailFreeBoolean = $validationResult['free'];
    $emailFree = ($emailFreeBoolean) ? 'true' : 'false';
    $emailValidBoolean = $validationResult['format_valid'];
    $emailValid = ($emailValidBoolean) ? 'true' : 'false';
    $emailDomain = $validationResult['domain'];
    $emailUser = $validationResult['user'];
    $emailDYM = $validationResult['did_you_mean'];


    // Lookup Domain on FullContact Company API
    if (!$emailFreeBoolean) {
      // Initialize CURL:
      $ch = curl_init('https://api.fullcontact.com/v2/company/lookup.json?domain='.$emailDomain);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-FullContact-APIKey: 2b7ad517c4537bb0"
      ));
      // Store the data:
      $jsonFC2 = curl_exec($ch);
      curl_close($ch);
      // Decode JSON response:
      $fcInfo2 = json_decode($jsonFC2, true);
      $fcCode = $fcInfo2['category'][0]['code'];
      if ($fcCode != "EMAIL_PROVIDER") {
        $fcWebsite = $fcInfo2['website'];
        $fcOrg = $fcInfo2['organization']['name'];
        $fcEmployees = $fcInfo2['organization']['approxEmployees'];
        setcookie("leadOrgName", $fcOrg, time() + (86400), "/", ".belloforwork.com");
      }
    }


    // Lookup IP Address
    // Get IP Address
    function getUserIP() {
      $client  = @$_SERVER['HTTP_CLIENT_IP'];
      $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
      $remote  = $_SERVER['REMOTE_ADDR'];
      if(filter_var($client, FILTER_VALIDATE_IP)) {
          $ip = $client;
      } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
          $ip = $forward;
      } else {
          $ip = $remote;
      }
      return $ip;
    }
    // Set IP address
    $ipaddress = getUserIP();
    // Initialize CURL to IPInfo API:
    $ch = curl_init('http://ipinfo.io/'.$ipaddress.'/json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Store the data:
    $jsonIP = curl_exec($ch);
    curl_close($ch);
    // Decode JSON response and set:
    $ipinfo = json_decode($jsonIP, true);
    $ipCity = $ipinfo['city'];
    $ipRegion = $ipinfo['region'];
    $ipCountry = $ipinfo['country'];
    $ipNetwork = $ipinfo['org'];


    // Set Browsing Data info
    $autopilotid = $_POST['_autopilot_session_id'];
    $hubspotutk = $_COOKIE['hubspotutk'];
    // $curl = $_POST['currentPage'];
    $curl = $_SERVER['HTTP_REFERER'];
    $form_id = $_POST['formID'];
    $lurl = $_COOKIE[lurl];
    $totalpages = $_COOKIE[totalpages];
    $utm_source = $_COOKIE[utm_source];
    $utm_medium = $_COOKIE[utm_medium];
    $utm_campaign = $_COOKIE[utm_campaign];
    $utm_content = $_COOKIE[utm_content];
    $utm_term = $_COOKIE[utm_term];


    // Lookup Person on Fullcontact
    // Initialize CURL:
    $ch = curl_init('https://api.fullcontact.com/v2/person.json?email='.$email);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "X-FullContact-APIKey: 2b7ad517c4537bb0"
    ));
    // Store the data:
    $jsonFC = curl_exec($ch);
    curl_close($ch);
    // Decode JSON response and set:
    $fcInfo = json_decode($jsonFC, true);
    $fcLikelihood = $fcInfo['likelihood'];
    if ($fcLikelihood > 0.65) {
      $fcFamilyName = $fcInfo['contactInfo']['familyName'];
      $fcGivenName = $fcInfo['contactInfo']['givenName'];
      $fcFullName = $fcInfo['contactInfo']['fullName'];
      $fcOrg = $fcInfo['organizations'][0]['name'];
      $fcTitle = $fcInfo['organizations'][0]['title'];
      // Save Name and Company
      setcookie("leadFirstName", $fcGivenName, time() + (86400), "/", ".belloforwork.com");
      setcookie("leadSurname", $fcFamilyName, time() + (86400), "/", ".belloforwork.com");
      setcookie("leadOrgName", $fcOrg, time() + (86400), "/", ".belloforwork.com");
    } else {

      // Deduce name from FullContact
      // Initialize CURL:
      $ch = curl_init('https://api.fullcontact.com/v2/name/deducer.json?email='.$email);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-FullContact-APIKey: 2b7ad517c4537bb0"
      ));
      // Store the data:
      $jsonFC = curl_exec($ch);
      curl_close($ch);
      // Decode JSON response and set:
      $fcInfo = json_decode($jsonFC, true);
      $fcLikelihood = $fcInfo['likelihood'];
      if ($fcLikelihood > 0.75) {
        $fcFamilyName = $fcInfo['nameDetails']['familyName'];
        $fcGivenName = $fcInfo['nameDetails']['givenName'];
        $fcFullName = $fcInfo['nameDetails']['fullName'];
        // Save Deduced Name
        setcookie("leadFirstName", $fcGivenName, time() + (86400), "/", ".belloforwork.com");
        setcookie("leadSurname", $fcFamilyName, time() + (86400), "/", ".belloforwork.com");
      }
    }

    if (!$fcOrg) {
      // Save Email Username
      setcookie("leadOrg", $emailUser, time() + (86400), "/", ".belloforwork.com");
    }


    header('Location: '.$nextPage);
    // // Lookup Colleagues on Hunter.io API
    // if ($fcEmployees < 101) {
    //   // Initialize CURL:
    //   $ch = curl_init('https://api.hunter.io/v2/domain-search?domain='.$emailDomain.'&type=personal&api_key=5b97c50d1bb3c6c01f5b856b9ac836bc7d1a606f');
    //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //   // Store the data:
    //   $jsonTeammates = curl_exec($ch);
    //   curl_close($ch);
    // }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api2.autopilothq.com/v1/contact");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_POST, TRUE);

      curl_setopt($ch, CURLOPT_POSTFIELDS, "{
        \"contact\": {
          \"Email\": \"{$email}\",
          \"FirstName\": \"{$fcGivenName}\",
          \"LastName\": \"{$fcFamilyName}\",
          \"Company\": \"{$fcOrg}\",
          \"Title\": \"{$fcTitle}\",
          \"Website\": \"{$fcWebsite}\",
          \"NumberOfEmployees\": \"{$fcEmployees}\",
          \"_autopilot_session_id\": \"{$autopilotid}\",
          \"MailingCity\": \"{$ipCity}\",
          \"MailingState\": \"{$ipRegion}\",
          \"MailingCountry\": \"{$ipCountry}\",
          \"LeadSource\": \"{$utm_source} / {$utm_medium}\",
          \"Status\": \"{$leadstatus}\",
          \"custom\": {
            \"string--Submission--Page\": \"{$curl}\",
            \"string--UTM_Source\": \"{$utm_source}\",
            \"string--UTM_Medium\": \"{$utm_medium}\",
            \"string--UTM_Campaign\": \"{$utm_campaign}\",
            \"string--UTM_Content\": \"{$utm_content}\",
            \"string--UTM_Term\": \"{$utm_term}\",
            \"string--Form--ID\": \"{$form_id}\",
            \"string--Landing--Page--Url\": \"{$lurl}\",
            \"integer--Hits--before--Submission\": \"{$totalpages}\",
            \"string--IP--Address\": \"{$ipaddress}\",
            \"string--IP--Network\": \"{$ipNetwork}\",
            \"string--Email--Free--Provider\": \"{$emailFree}\",
            \"string--Email--DYM\": \"{$emailDYM}\"
          }
        }
      }");

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "autopilotapikey: 5af8508bcb444add9fb2eda3e8a80c52",
      "Content-Type: application/json"
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    // Send data to Hubspot
    $hs_context = array(
        'hutk' => $hubspotutk,
        'ipAddress' => $ipaddress,
        'pageUrl' => $curl
    );
    $hs_context_json = json_encode($hs_context);
    //Need to populate these variable with values from the form.
    $str_post = "firstname=" . urlencode($fcGivenName)
        . "&lastname=" . urlencode($fcFamilyName)
        . "&email=" . urlencode($email)
        . "&company=" . urlencode($fcOrg)
        . "&hs_context=" . urlencode($hs_context_json); //Leave this one be

    //replace the values in this URL with your portal ID and your form GUID
    $endpoint = 'https://forms.hubspot.com/uploads/form/v2/2083960/386372';

    $ch = @curl_init();
    @curl_setopt($ch, CURLOPT_POST, true);
    @curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
    @curl_setopt($ch, CURLOPT_URL, $endpoint);
    @curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded'
    ));
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    @curl_close($ch);



    // Send event to GA
    $data = array(
      'v' => 1,
      'tid' => 'UA-72952347-1',
      'cid' => gaParseCookie(),
      't' => 'event'
    );

    $data['ec'] = 'Sign Up';
    $data['ea'] = 'Email';
    $data['el'] = $curl;
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




  } else {
    header('Location: '.$errorPage);
  }

  ?>
