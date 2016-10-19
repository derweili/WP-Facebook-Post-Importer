<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 


class WPFPI_Options {

    private $options;
    private $sections;

    private $fb;

    private $pages;

    private $redirectURL;
    private $helper;
    private $userToken;

    private $permissions = ['email', 'publish_pages', 'manage_pages', 'user_posts', 'publish_actions']; // facebook permissions

    private $loginURL;

    private $accounts;

    private $pages_fields;

    public function __construct() {

        /*
        * set "login with Facebook" redirect url to wp home url
        */

        $this->redirectURL = home_url( '/' );

        $this->load_options(); // load options

        if ( $this->app_credentials_available() ) { // init facebook connection if app credentials are available
            $this->connect_to_facebook();
        }
   
        $this->options_page_settings(); // load options page settings

    }

    private function connect_to_facebook(){ // init connection to facebook based on app id and secret from options
        $this->fb = new Facebook\Facebook([
          'app_id' => $this->options['app_id'], // Replace {app-id} with your app id
          'app_secret' => $this->options['app_secret'],
          'default_graph_version' => 'v2.2',
        ]);

        if ( !empty( $this->userToken ) ) { // if user token is alreade stored, set user token as default access token for all fb connections
            $this->fb->setDefaultAccessToken($this->userToken);
        }
    }

    private function load_options() {
        $this->options = get_option( 'wp-facebook-post-importer', array() ); // load options page - options
        $this->userToken = get_option( 'wpfpi_access_token' ); // load stored access token
    }

    private function options_page_settings() {
        $pages = array(
            'wp-facebook-post-importer'   => array(
                'page_title'    => __( 'Facebook Post Importer', 'sample-domain' ),
                'parent_slug'    => 'options-general.php',
                'sections'  => $this->options_page_sections(),
            ),
        );

        $option_page = new RationalOptionPages( $pages );

    }

    private function options_page_sections() {

        /**
        *
        * Create Input fields for App ID and App Secred
        * Load "login with facebook" link as callback when app credentials are available but no token is stored
        *
        */

        $this->sections = array(
            'app_credentials'   => array(
                'title'         => __( 'Stepp 1: App Credentials', 'sample-domain' ),
                'custom'        => true,
                'text'          => '<p>' . __( 'Please enter you app credentials', 'sample-domain' ) . '</p>',
                'callback'      => $this->login_with_facebook_link(),
                'fields'        => array(
                    'app_id'       => array(
                        'title'         => __( 'App ID', 'sample-domain' ),
                    ),
                    'app_secret'       => array(
                        'title'         => __( 'App Secret', 'sample-domain' ),
                        'type'          => 'password',
                    ),
                ),
            )
        );
        

        if ( $this->app_credentials_available() && !empty( $this->userToken ) ) { // Check if app credentials and user token are available
            if ( $this->get_accounts() != null ) { // load user accounts ( facebook pages managed by authorized user ) and check if accounts are available
                $this->pages_fields = array();
                foreach ( $this->accounts as $account ) { // loop through accounts and store required information inside array -> build one settings field for each account
                    $this->pages_fields[ $account["id"] ] = array(
                        'title'         => $account["name"],
                        'type'          => 'checkbox',
                        'id'          => 'account' . $account["id"],
                    );
                }
            }
           // var_dump($this->pages_fields);

           $this->sections['page_settings'] = array(
                'title'         => __( 'Stepp 2: Facebook Page Settings', 'sample-domain' ),
                'custom'        => true,
                'text'          => '<p>' . __( 'Select the page from which the posts should be importet', 'sample-domain' ) . '</p>',
                //'callback'      => 'hello world <a href="#">Mit Facebook verbinden</a>',
                'fields'        => $this->pages_fields,
            );
        }

        return $this->sections;

    }


    /*
    * check if app credentials are set and return true or false
    */

    private function app_credentials_available(){ 
        if ( !empty( $this->options['app_id']) && !empty( $this->options['app_secret'] ) ) {
            return true;
        }else{
            return false;
        }
    }

    /*
    * Get login with facebook link if account credentials are available but no token is stored
    */

    private function login_with_facebook_link (){

        if ( !empty( $this->options['app_id']) && !empty( $this->options['app_secret'] ) && empty( $this->userToken ) ) {

            $this->helper = $this->fb->getRedirectLoginHelper();
            $this->loginUrl = $this->helper->getLoginUrl($this->redirectURL, $this->permissions);

            return 'Please connect your Facebook Account <a href="' . htmlspecialchars($this->loginUrl) . '" class="button">
        Connect now
    </a>';
        } else {
           // return $this->options['app_id'];
        }

    }

    /*
    * handle accounts call to list all user accounts
    */
    private function get_accounts () { 
        $this->accounts = $this->fb->get('/me/accounts');
        $this->accounts = $this->accounts->getGraphEdge()->asArray();
        return $this->accounts;
    }


}

new WPFPI_Options;