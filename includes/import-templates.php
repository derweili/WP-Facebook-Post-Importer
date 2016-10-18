<?php 

class WPFPI_IMPORT_TEMPLATES {
	
	private $new_post_id;
	private $fb_post;
	private $fb_post_attachement;
	private $subattachments;
	private $img_attachment_ids = array();


	public function __construct( $new_post_id, $fb_post, $fb_post_attachements ) {
		$this->new_post_id =  $new_post_id;
		$this->fb_post =  $fb_post;

		if ( $this->has_post_attachements( $fb_post_attachements ) ) { //Check if Post hast Attachents

			$this->fb_post_attachement =  $fb_post_attachements[0]; // store attachement

			if ( isset( $this->fb_post_attachement['subattachments'] ) ) { // check if post has subattachements
				$this->subattachments =  $this->fb_post_attachement['subattachments']; // store subattachements
			}

			$this->switch_attachement_types(); //execute attachement process

		}


	}

	private function has_post_attachements( $attachements ){
		if ( count( $attachements ) > 0 ) {
			return true;
		}else{
			return false;
		}
	}

	private function switch_attachement_types() {

		switch ($this->fb_post_attachement['type']) {
			case 'album':

				$this->album_template();
				break;
			
			case 'video_inline':

				$this->video_template();
				break;
			
			default:
				# code...
				break;
		}


	}

	private function album_template() {
		add_post_meta( $this->new_post_id, 'attachement_type', $this->fb_post_attachement['type'], true );
		foreach ($this->subattachments as $this->subattachment) {
			$this->img_attachment_ids[] = $this->import_image_from_url( $this->subattachment['media']['image']['src'], $this->subattachment['target']['id'] );
		}
		add_post_meta( $this->new_post_id, 'img_attachment_ids', $this->img_attachment_ids );

		$gallery_shortcode = '[gallery ids="' . implode(",",$this->img_attachment_ids) . '"]';
		$post_attr = array(
			'ID'           => $this->new_post_id,
			'post_content' => $this->fb_post[ "message" ] . ' ' . $gallery_shortcode, 
		);
		wp_update_post( $post_attr );

		set_post_thumbnail( $this->new_post_id, $this->img_attachment_ids[0] );

	}


	private function video_template() {
		/*$embedcode = '<iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fallesteuershop%2Fvideos%2F828966240571019%2F&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>'*/
		
		add_post_meta( $this->new_post_id, 'attachement_type', $this->fb_post_attachement['type'], true );

		$post_attr = array(
			'ID'           => $this->new_post_id,
			'post_content' => esc_url( $this->fb_post_attachement['target']['url'] ), 
		);
		wp_update_post( $post_attr );


	}



	private function import_image_from_url($url = null, $filename = null ) {
		if( !class_exists( 'WP_Http' ) )
			include_once( ABSPATH . WPINC. '/class-http.php' );

		$photo = new WP_Http();
		$photo = $photo->request( $url );
		$attachment = wp_upload_bits( $filename . '.jpg', null, $photo['body'], date("Y-m", strtotime( $photo['headers']['last-modified'] ) ) );

		$filetype = wp_check_filetype( basename( $attachment['file'] ), null );

		$postinfo = array(
			'post_mime_type'	=> $filetype['type'],
			'post_title'		=> $filename . '',
			'post_content'	=> '',
			'post_status'	=> 'inherit',
		);
		$new_filename = $attachment['file'];
		
		$attach_id = wp_insert_attachment( $postinfo, $new_filename, $this->new_post_id );
		//$attach_data = wp_generate_attachment_metadata( $attach_id, $new_filename );
  		//wp_update_attachment_metadata( $attach_id,  $attach_data );

  		return $attach_id;

	}



};

