<?php

class LoginController extends BaseController{    
    
    public function initialize() {
        
    }
    
    public function actionLogin(){
        
        //lets break ALL the rules and use no view layer
        //because a view stops following execution
        //not clever after login
        if(!$this->model->canLoginWithToken()){
            $this->model->loginWithTwitterAndSetToken();
        }
        
    }
    
    public function actionLogout(){
        
        $this->model->removeToken();
        
        $this->view(
            array(
                "externalLogoutUrl" => $this->model->getExternalLogoutUrl()
            )
        );
    }
}
?>
