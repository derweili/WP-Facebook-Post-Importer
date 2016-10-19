<?php 

class WPFPI_IMPORT_TEMPLATES {
	
	private $new_post_id;
	private $fb_post;
	private $fb_post_attachement;
	private $subattachments;
	private $img_attachment_ids = array();
	private $message = ' ';


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

		if ( isset( $this->fb_post[ "message" ] ) ) {
			$this->message =  $this->fb_post[ "message" ];
		}

	}

	/*
	* Check if given post has post attachments
	*/
	private function has_post_attachements( $attachements ){
		if ( count( $attachements ) > 0 ) {
			return true;
		}else{
			return false;
		}
	}

	/*
	* Check which type of attachment is served
	*/
	private function switch_attachement_types() {

		switch ($this->fb_post_attachement['type']) {
			case 'album':

				$this->album_template();
				break;
			
			case 'video_inline':

				$this->video_template();
				break;
			
			case 'photo':

				$this->photo_template();
				break;
			
			default:
				# code...
				break;
		}


	}

	/*
	* handle album attachment
	* store all image within WordPress Media Library and add al images as WordPress Gallery inside post content
	* use first image from album as post thumbnail
	*/

	private function album_template() {
		add_post_meta( $this->new_post_id, 'attachement_type', $this->fb_post_attachement['type'], true );
		foreach ($this->subattachments as $this->subattachment) {
			$this->img_attachment_ids[] = $this->import_image_from_url( $this->subattachment['media']['image']['src'], $this->subattachment['target']['id'], $this->subattachment['media']['image']['width'], $this->subattachment['media']['image']['height'] );
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

	/*
	* handle video template
	* Generate embed code from video url and send embed code to post content
	*/

	private function video_template() {
		$embedcode = '<iframe src="https://www.facebook.com/plugins/video.php?href=' . urlencode( $this->fb_post_attachement['target']['url'] ) . '&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>';
		
		add_post_meta( $this->new_post_id, 'attachement_type', $this->fb_post_attachement['type'], true );

		$post_attr = array(
			'ID'           => $this->new_post_id,
			'post_content' => $embedcode . $this->message,
		);
		wp_update_post( $post_attr );


	}

	/*
	* handle (single) photo post
	* store image within WordPress Media Gallery
	* set new photo as post thumbnail
	*/
	private function photo_template() {
		
		add_post_meta( $this->new_post_id, 'attachement_type', $this->fb_post_attachement['type'], true );

		$img_id = $this->import_image_from_url( $this->fb_post_attachement['media']['image']['src'], $this->fb_post_attachement['target']['id'], $this->fb_post_attachement['media']['image']['width'], $this->fb_post_attachement['media']['image']['height'] );
		set_post_thumbnail( $this->new_post_id, $img_id );
		/*$post_attr = array(
			'ID'           => $this->new_post_id,
			'post_content' => $embedcode . $this->fb_post[ "message" ],
		);
		wp_update_post( $post_attr );*/


	}


	/**
	*
	* Handle import image process
	* Download image from Facebook Server
	* Add image to wordpress Media Library
	* update attachment meta from FB meta values
	* return new image id
	*
	*/
	private function import_image_from_url($url = null, $filename = null, $width, $height ) {
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

		$attachment_meta = array(
				'width' => $width,
				'height' => $height,
			);

		wp_update_attachment_metadata( $attach_id , $attachment_meta );

  		return $attach_id;

	}



};

