<?php
session_start();

class WPFPI_Options {

    private $options;
    private $sections;

    private $fb;

    private $pages;

    private $redirectURL;
    private $helper;
    private $userToken;

    private $permissions = ['email', 'publish_pages', 'manage_pages', 'user_posts', 'publish_actions'];

    private $loginURL;

    public function __construct() {

        $this->redirectURL = home_url( '/' );

        $this->load_options();

        if ( $this->app_credentials_available() ) {
            $this->connect_to_facebook();
        }
   
        $this->options_page_settings();



    }

    private function connect_to_facebook(){
        $this->fb = new Facebook\Facebook([
          'app_id' => $this->options['app_id'], // Replace {app-id} with your app id
          'app_secret' => $this->options['app_secret'],
          'default_graph_version' => 'v2.2',
        ]);
    }

    private function load_options() {
        $this->options = get_option( 'wp-facebook-post-importer', array() );
        $this->userToken = get_option( 'wpfpi_access_token' );
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

        $this->sections = array(
            'app_credentials'   => array(
                'title'         => __( 'Stepp 1: App Credentials', 'sample-domain' ),
                'custom'        => true,
                'text'          => '<p>' . __( 'Please enter you app credentials', 'sample-domain' ) . '</p>',
               // 'include'       => plugin_dir_path( __FILE__ ) . '/your-include.php',
                'callback'      => $this->login_with_facebook_link(),
                'fields'        => array(
                    'app_id'       => array(
                        'title'         => __( 'App ID', 'sample-domain' ),
                    ),
                    'app_secret'       => array(
                        'title'         => __( 'App Secret', 'sample-domain' ),
                    ),
                ),
            )
        );
        
        if ( $this->app_credentials_available() && !empty( $this->userToken ) ) {
           $this->sections['page_settings'] = array(
                'title'         => __( 'Stepp 2: Facebook Page Settings', 'sample-domain' ),
                'custom'        => true,
                'text'          => '<p>' . __( 'Select the page from which the posts should be importet', 'sample-domain' ) . '</p>',
                //'callback'      => 'hello world <a href="#">Mit Facebook verbinden</a>',
                'fields'        => array(
                    'importpages'         => array(
                        'title'         => __( 'Pages', 'sample-domain' ),
                        'type'          => 'checkbox',
                        'text'          => __( 'Text attributes are used as labels for checkboxes' ),
                        'choices'       => array(
                            '123456'    => __( 'Facebook Page 1', 'sample-domain' ),
                            '654321'    => __( 'Facebook Page 2', 'sample-domain' ),
                        ),
                    ),
                ),
            );
        }

        return $this->sections;

    }

    private function app_credentials_available(){
        if ( !empty( $this->options['app_id']) && !empty( $this->options['app_secret'] ) ) {
            return true;
        }else{
            return false;
        }
    }

    private function login_with_facebook_link (){

        if ( !empty( $this->options['app_id']) && !empty( $this->options['app_secret'] ) ) {

            $this->helper = $this->fb->getRedirectLoginHelper();
            $this->loginUrl = $this->helper->getLoginUrl($this->redirectURL, $this->permissions);

            return 'Please connect your Facebook Account <a href="' . htmlspecialchars($this->loginUrl) . '" class="button">
        Connect now
    </a>';
        } else {
           // return $this->options['app_id'];
        }

    }

}

new WPFPI_Options;