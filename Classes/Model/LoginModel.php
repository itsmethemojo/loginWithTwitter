<?php


class LoginModel extends BaseModel{
    //TODO overwrite with define or global?
    private $expiresInterval = 2592000; //30 Days 
    
    public function __construct($dbConfig) {
        parent::__construct($dbConfig);
        //maybe here?
    }
    
    private function getCookieKey(){
        if(defined("COOKIE_LOGIN_TOKEN_PREFIX")){
            return LOGIN_TOKEN_PREFIX."_token";
        }
        else{
            return "token";
        }
    }
    
    private function getGlobalsKey(){
        if(defined("GLOBALS_USERID_KEY")){
            return GLOBALS_USERID_KEY;
        }
        else{
            return "user_id";
        }
    }
    
    private function getUserIdWithToken($token){
        return $this->queryDatabase(
                "SELECT user_id FROM ".$this->tablePrefix."tokens where token = ? and expires > ?", 
                array($token,time()));
    }
    
    public function canLoginWithToken(){
        $key = $this->getCookieKey();
        if(!isset($_COOKIE[$key])){
            return false;
        }
        
        $userIds = $this->getUserIdWithToken($_COOKIE[$key]);
        if(count($userIds)==1){
            $GLOBALS[$this->getGlobalsKey()] = $userIds[0]["user_id"];
            return true;
        }
        return false;
    }
    
    private function setToken($userId){
        $token = md5(time().$userId.rand(1000, 9999));
        $expires = time()+$this->expiresInterval;
        $this->queryDatabase("INSERT INTO ".$this->tablePrefix."tokens (token,user_id,expires) VALUES (?,?,?)", 
                array($token,$userId,$expires));
        setcookie($this->getCookieKey(), $token, $expires, "/", $_SERVER['HTTP_HOST']);
        if(isset($_SESSION["SRC_URL"])){
            $url = $_SESSION["SRC_URL"];
        }
        else{
            $url = (($_SERVER['SERVER_PORT']==80)?"http":"https")."://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        
        //TODO remove oauth_token,oauth_verifier
        $this->redirect($url);
    }
    
    private function redirect($url,$onlyJavascript=false){
        if(!$onlyJavascript){
            header("Location: ".$url);
        }
        echo "<script>location.href=location.href</script>redirecting to ".$url;
        exit;
    }
    
    
    
    private function loginWithTwitter(){
        session_start();
        if(isset($_SESSION['twitter_login_userid'])){
            return $_SESSION['twitter_login_userid'];
        }
        else{
            $_SESSION["SRC_URL"] = (($_SERVER['SERVER_PORT']==80)?"http":"https")."://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $apiPath = LOGIN_ROOT_DIR."Api".DIRECTORY_SEPARATOR."Twitter".DIRECTORY_SEPARATOR;
            
            include_once(LOGIN_ROOT_DIR."Configuration".DIRECTORY_SEPARATOR."/twitter.php");
            include_once($apiPath."twitteroauth.php");

            if(isset($_SESSION['status']) && $_SESSION['status']=='verified'){
                    $screenname = $_SESSION['request_vars']['screen_name'];
                    if(isset($allowedUsers[$screenname])){
                        $_SESSION['twitter_login_userid'] = $allowedUsers[$screenname];
                        //echo "i am logged in ".$_SESSION['twitter_login_userid'];
                    }
                    else{
                        unset( $_SESSION['twitter_login_userid']);
                        echo "your twitter login is correct but you are still not allowed here";
                        exit();
                    }
            }else{
                include_once($apiPath."twitteroauth.php");

                if (isset($_REQUEST['oauth_token']) && $_SESSION['token']  !== $_REQUEST['oauth_token']) {
                    session_destroy();echo "#1";
                    $this->redirect($_SESSION["SRC_URL"]);	
                }elseif(isset($_REQUEST['oauth_token']) && $_SESSION['token'] == $_REQUEST['oauth_token']) {
                    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['token'] , $_SESSION['token_secret']);
                    $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
                    if($connection->http_code=='200')
                    {
                        $_SESSION['status'] = 'verified';
                        $_SESSION['request_vars'] = $access_token;echo "#2";
                        $this->redirect($_SESSION["SRC_URL"]);
                        echo
                        exit;
                    }else{
                        die("error, try again later!");
                    }

                }else{
                    if(isset($_GET["denied"]))
                    {echo "#3";
                        $this->redirect($_SESSION["SRC_URL"]);
                    }

                    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
                    $request_token = $connection->getRequestToken($_SESSION["SRC_URL"]);
                    $_SESSION['token'] 			= $request_token['oauth_token'];
                    $_SESSION['token_secret'] 	= $request_token['oauth_token_secret'];

                    if($connection->http_code=='200')
                    {
                        $twitter_url = $connection->getAuthorizeURL($request_token['oauth_token']);echo "#4";
                        //don't use url from twitter given. use url that called the authorisation
                        $this->redirect($twitter_url);
                        
                    }else{
                        die("error connecting to twitter! try again later!");
                    }
                }
            }

        }
        //exit; do i need this?
    }
    
    public function loginWithTwitterAndSetToken(){
        $userId = $this->loginWithTwitter();
        $this->setToken($userId);
    }
    
    public function removeToken(){
        setcookie($this->getCookieKey(), "delete_me", -1, "/", $_SERVER['HTTP_HOST']);
    }
    
    public function getExternalLogoutUrl(){
        if(defined("EXTERNAL_LOGOUT_URL")){
            return EXTERNAL_LOGOUT_URL;
        }
        return false;
    }
}