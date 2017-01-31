<?php

/**
 * Login actions.
 *
 * @package    based on sfGoogleLoginPlugin for ifresco Client
 * @subpackage actions
 * @author     sfGoogleLoginPlugin from Sebastian Herbermann <sebastian.herbermann@googlemail.com> modified from Dominik Danninger <ddanninger@may.co.at>
 *
 * ifresco Client v0.1alpha
 * 
 * Copyright (c) 2011 Dominik Danninger, MAY Computer GmbH
 * 
 * This file is part of "ifresco Client".
 * 
 * "ifresco Client" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco Client" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco Client".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */
class AlfrescoLoginActions extends sfActions {
    public function executeLoginAjax(sfWebRequest $request) {
        if ( $this->getUser()->isAuthenticated() ) {
            $this->redirect('homepage');
        }
        
        $processResult = $this->loginProcess($request);
        $array = array("loggedIn"=>false);
            
        
        if ($processResult==true) {
            $array["loggedIn"] = true;
        }
            
            
        $loginData = $request->getParameter('login');
        if (isset($loginData)) {
            $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
            $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
            $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
            $this->getResponse()->setHttpHeader('Pragma','no-cache');
            $return = json_encode($array);
            
            return $this->renderText($return);      
        }
        
        $this->setLayout(false);
    }

    public function executeLogin(sfWebRequest $request) {
     
        if ( $this->getUser()->isAuthenticated() ) {
            $this->redirect('homepage');
        }
        
        //$this->Languages = $this->getRequest()->getLanguages();
        $AcceptLanguages = $this->getRequest()->getLanguages();
        $this->LanguageDefault = "";
        if (count($AcceptLanguages) > 0) {
            $langKey = $AcceptLanguages[0];
            $langKey = preg_replace("/(.*?)_.*/","$1",$langKey);
            $this->LanguageDefault = $langKey;
        }

        $this->Languages = array("en"=>"English","de"=>"Deutsch","ru"=>"Russian");
        if (array_key_exists($this->LanguageDefault,$this->Languages)) {
            $this->getUser()->setCulture($this->LanguageDefault);
        }
            
        $processResult = $this->loginProcess($request);
        if ($processResult==true) {
            $this->redirect('homepage');        
        }
        
        $this->ifrescoVersion = AlfrescoClientConfiguration::get_version();
        $this->setLayout(false);
    }
    
    public function loginProcess(sfWebRequest $request) {
        $user = $this->getUser();
        $this->form = new AlfrescoLoginForm();
        
        if ($request->isMethod('post')) {  
            $lang = $request->getParameter('lang');
            $this->getUser()->setCulture($lang);
            
            $this->form->bind($request->getParameter('login'));

            if ($this->form->isValid()) {   
                $this->success = false;
            
                $user = $this->getUser();
                
                $repositoryUrl = AlfrescoConfiguration::getInstance()->RepositoryUrl;
                $loginData = $request->getParameter('login');
                $userName = $loginData["username"];
                $password = $loginData["password"];


                $alfrescoLogin = new RESTAuthentication($repositoryUrl);

                $ticket = $alfrescoLogin->login($userName,$password);
                if ($ticket != null && !empty($ticket)) {
                    if (!$alfrescoAccount = Doctrine::getTable('AlfrescoAccount')->findOneByUserToken($ticket)) {
                        $alfrescoAccount = new AlfrescoAccount();
                        $alfrescoAccount->setUserToken( $ticket );
                    }
                    $alfrescoAccount->setLastLogin( date('Y-m-d H:i:s') );
                    $alfrescoAccount->save();
                    
                    $user->setAuthenticated( true );
                    $user->setAttribute( 'AlfrescoTicket', $ticket );
                    $user->setAttribute( 'AlfrescoUsername', $userName );             

                    $this->success = true;
                    return true;
                    
                } 
                else {
                    $this->success = false;
                   
                }    
            }
            else {
                
            }      
        }
        return false;
    }
    
    public function executeIsLoggedin(sfWebRequest $request) {
        $array = array("loggedIn"=>false);
        if ( !$this->getUser()->isAuthenticated() ) {
            $array["loggedIn"] = false;
            $this->getResponse()->setStatusCode(401);
        }
        else {
            $array["loggedIn"] = true;
            $this->getResponse()->setStatusCode(200);
        }
        return $this->renderText(json_encode($array));
    }
	
	public function executeVerify(sfWebRequest $request) {
		$this->success = false;
        
        $user = $this->getUser();
        
        $repositoryUrl = AlfrescoConfiguration::getInstance()->RepositoryUrl;
        $userName = $request->getParameter('username');
        $password = $request->getParameter('password');
        $alfrescoLogin = new RESTAuthentication($repositoryUrl);      
        
        $ticket = $alfrescoLogin->login($userName,$password);
        if ($ticket != null && !empty($ticket)) {
            if (!$alfrescoAccount = Doctrine::getTable('AlfrescoAccount')->findOneByUserToken($ticket)) {
                $alfrescoAccount = new AlfrescoAccount();
                $alfrescoAccount->setUserToken( $ticket );
            }
            $alfrescoAccount->setLastLogin( date('Y-m-d H:i:s') );
            $alfrescoAccount->save();
            
            $user->setAuthenticated( true );

            $user->setAttribute( 'AlfrescoTicket', $ticket );   
            $user->setAttribute( 'AlfrescoUsername', $userName );   
            $this->success = true;
        } 
        else {
        	$this->success = false;
        }

        if ($this->success != false) {
            $this->redirect('homepage');        
        }
        
        $this->setLayout(false);
	}
	
    public function executeLogout(sfWebRequest $request) {
        $this->getUser()->setAuthenticated( false );
        $this->getUser()->setAttribute( 'AlfrescoLogin_account', null );
        $this->getUser()->setAttribute( 'AlfrescoAuthentication', null );
        $this->getUser()->setAttribute( 'AlfrescoTicket', null );
        $this->getUser()->setAttribute( 'AlfrescoUsername', null );
        $this->redirect( 'homepage' );
    }
}
