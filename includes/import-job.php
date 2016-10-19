<?php
session_start();


class WPFPI_CRONJOBS {


	private $options;
    private $fb;
    private $pages;
    private $userToken;
    private $accounts;
    private $posts;
    private $attachements;
    private $post_data;
    public  $post_attributes;
    private $posts_request;
    private $posts_response;
    private $total_posts;
    private $message;
    private $insert_post_return;

    public function __construct() {

        $this->load_options();

        if ( $this->app_credentials_available() ) {
            $this->connect_to_facebook();
        }
   
        $this->schedule_import_event();

        //$this->add_import_job();

        if ( isset( $_GET["import"] ) ) {
        	//$this->add_import_job();
        	add_action('init', array(&$this, 'add_import_job' ) );
        }

    }

    private function load_options() {
        $this->options = get_option( 'wp-facebook-post-importer', array() );
        $this->userToken = get_option( 'wpfpi_access_token' );
    }

    private function connect_to_facebook(){
        $this->fb = new Facebook\Facebook([
          'app_id' => $this->options['app_id'], // Replace {app-id} with your app id
          'app_secret' => $this->options['app_secret'],
          'default_graph_version' => 'v2.2',
        ]);

        if ( !empty( $this->userToken ) ) {
            $this->fb->setDefaultAccessToken($this->userToken);
            $this->get_accounts();
        }
    }
    private function get_accounts () {
        $this->accounts = $this->fb->get('/me/accounts');
        $this->accounts = $this->accounts->getGraphEdge()->asArray();
        return $this->accounts;
    }

    private function app_credentials_available(){
        if ( !empty( $this->options['app_id']) && !empty( $this->options['app_secret'] ) ) {
            return true;
        }else{
            return false;
        }
    }

    public function schedule_import_event () {

    	  //wp_schedule_event( time(), 'hourly', 'wpfpi_import_posts' );

            if ( ! function_exists('wp_generate_attachment_metadata' ) {
                include( ABSPATH . 'wp-admin/includes/image.php' )
            }

    	  add_action( 'wpfpi_import_posts',  array( &$this, 'add_import_job' ) );

    }

    public function add_import_job() {
    	if ( $this->app_credentials_available() && !empty( $this->userToken ) ) {
    		//echo "before get accounts";
    		$this->get_accounts();
    		//var_dump($this->accounts);

    		foreach ( $this->accounts as $account ) {

    			if ( $this->options['account' . $account["id"]] ) {

    				$this->posts = $this->get_posts_from_page( $account["id"] );
    				//var_dump($this->posts);
    				foreach ($this->posts as $fbpost) {

    					//var_dump($fbpost);
    					$this->attachements = $this->get_attachements_from_post( $fbpost[ "id" ] );

    					$post_title = ' ';
    					if ( isset( $fbpost[ "message" ] ) ) {
    						$post_title = $this->shortText( $fbpost[ "message" ], 50 );
    					}
    					

    					$this->post_attributes = array(
    							'post_title'    => $post_title,
							    //'post_content'  => $this->message,
							    'post_status'   => 'publish',
							    //'post_author'   => 1,
							    //'post_type'   => 'post',
							    'post_category' => array(),
                                'post_date' => $fbpost[ "created_time" ]->format('Y-m-d H:i:s')
    						);
    					if ( isset( $fbpost[ "message" ] ) && $fbpost[ "message" ] != null ) {
    						$this->post_attributes['post_content'] = $fbpost[ "message" ];
    					}


    					$this->insert_post_return = wp_insert_post( $this->post_attributes );
    					//var_dump($this->insert_post_return );

    					add_post_meta( $this->insert_post_return, 'wpfpi_facebook_post_id', $fbpost[ "id" ], true );

    					/* add_post_meta( $this->insert_post_return, 'wpfpi_time', date( 'Y', $timecode ), true );
*/
    					new WPFPI_IMPORT_TEMPLATES( $this->insert_post_return, $fbpost, $this->attachements );

    				}
    				

    			}
    		}

    	}
    }

    private function get_posts_from_page( $account_id = null ) {
		try {
			$posts_request = $this->fb->get('/' . $account_id . '/posts?limit=5');
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		$total_posts = array();

		$posts_response = $posts_request->getGraphEdge();

		if($this->fb->next($posts_response)) {

			$response_array = $posts_response->asArray();
			$total_posts = array_merge($total_posts, $response_array);

			while ($posts_response = $this->fb->next($posts_response)) {  

			  $response_array = $posts_response->asArray();
			  $total_posts = array_merge($total_posts, $response_array);  

			}

			return $total_posts;

		} else {

			$posts_response = $posts_request->getGraphEdge()->asArray();
			return $posts_response;
		}
    }


    private function get_attachements_from_post( $fb_post_id = null ) {
    	try {
		    $posts_request = $this->fb->get('/' . $fb_post_id . '/attachments');
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		return $posts_request->getGraphEdge()->asArray();
    }
    private function shortText( $string, $lenght ) {
    if (strlen($string) > $lenght ) {
        $string = substr($string,0,$lenght).'…';
        $string_ende = strrchr($string, ' ');
        $string = str_replace($string_ende,'…', $string);
    }
    return $string;
}

}

new WPFPI_CRONJOBS;

