<?php
include_once("config.php");
include_once("includes/functions.php");

//print_r($_GET);die;
//the "code" in the REQUEST is being stored here. After using. the REQUEST-IF only exists for when one needs to redirect it back to the index. When redirected back to the index, it sees if the "code" part is found in the REQUEST url
// if(isset($_REQUEST['code'])){
	
// 	$gClient->authenticate();
// 	$_SESSION['token'] = $gClient->getAccessToken();
// 	header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
// }

// if (isset($_SESSION['token'])) {
// 	$gClient->setAccessToken($_SESSION['token']);
// }
if ($gClient->getAccessToken()) {					// get access token here. for the first time.
	$userProfile = $google_oauthV2->userinfo->get();
	//DB Insert
	//$gUser = new Users();
	//$gUser->checkUser('google',$userProfile['id'],$userProfile['given_name'],$userProfile['family_name'],$userProfile['email'],$userProfile['gender'],$userProfile['locale'],$userProfile['link'],$userProfile['picture']);
	$_SESSION['google_data'] = $userProfile; // Storing Google User Data in Session
	header("location: ../backend/backend_home.php"); // changed from account.php
	$_SESSION['token'] = $gClient->getAccessToken();
} else { 								//first and foremost goes in this else to create and authurl from gclient which is created in config
	$authUrl = $gClient->createAuthUrl();
}

if(isset($authUrl)) {
	echo '<a href="'.$authUrl.'"><img src="images/glogin.png" alt=""/></a>';
} else {
	echo '<a href="logout.php?logout">Logout</a>';
}

?>