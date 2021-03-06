<?php
if(!isset($_SESSION)) 
{ 
    session_start(); 
}

class WPFPI_Callback {

	private $options;
	private $fb;
	private $helper;
	private $accessToken;
	private $oAuth2Client;



	public function __construct() {


        $this->load_options();

        if ( $this->app_credentials_available() ) {
            $this->connect_to_facebook();
        }
   
        if ( isset( $_GET["code"] ) && isset( $_GET["state"] ) ) {
        	$this->get_access_token();
        }else{
        }


    }

    /*
    * get options (app id and secret) from database and store as variable
    */

    private function load_options() {
        $this->options = get_option( 'wp-facebook-post-importer', array() );
    }

    /*
	* init facebook connection via php sdk
	*/

    private function connect_to_facebook(){
        $this->fb = new Facebook\Facebook([
          'app_id' => $this->options['app_id'],
          'app_secret' => $this->options['app_secret'],
          'default_graph_version' => 'v2.2',
        ]);
        $this->helper = $this->fb->getRedirectLoginHelper();

    }

    /*
    * check if app credentials are available and return false or true
    */

    private function app_credentials_available(){
        if ( !empty( $this->options['app_id']) && !empty( $this->options['app_secret'] ) ) {

            return true;

        }else{

            return false;

        }
    }


    /*
    * get access token from facebook and store as option in options table
    * handle errors
    */

    private function get_access_token() {

    	try {
		  $this->accessToken = $this->helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}

		if ( ! isset($this->accessToken) ) {
		  if ( $this->helper->getError() ) {

		    header('HTTP/1.0 401 Unauthorized');
		    echo "Error: " . $this->helper->getError() . "\n";
		    echo "Error Code: " . $this->helper->getErrorCode() . "\n";
		    echo "Error Reason: " . $this->helper->getErrorReason() . "\n";
		    echo "Error Description: " . $this->helper->getErrorDescription() . "\n";
		  } else {
		    header('HTTP/1.0 400 Bad Request');
		    echo 'Bad request';
		  }
		  exit;
		}

		$this->oAuth2Client = $this->fb->getOAuth2Client();

		update_option( 'wpfpi_access_token', $this->accessToken->getValue() );



    }


};

new WPFPI_Callback;