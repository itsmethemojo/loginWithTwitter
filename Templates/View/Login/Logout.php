you are logged out
<?php
if($this->par["externalLogoutUrl"]){
    echo '<br/><br/>redirecting to <a href="'.$this->par["externalLogoutUrl"].
            '">'.$this->par["externalLogoutUrl"].'</a><script>location.href="'.$this->par["externalLogoutUrl"].'";</script>';
}