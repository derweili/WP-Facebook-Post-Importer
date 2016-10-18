<?php 

class WPFPI_IMPORT_TEMPLATES {
	
	private $new_post_id;
	private $fb_post;
	private $fb_post_attachement;
	private $subattachements;


	public function __construct( $new_post_id, $fb_post, $fb_post_attachements ) {
		$this->new_post_id =  $new_post_id;
		$this->fb_post =  $fb_post;

		if ( $this->has_post_attachements( $fb_post_attachements ) ) { //Check if Post hast Attachents

			$this->fb_post_attachement =  $fb_post_attachements[0]; // store attachement

			if ( isset( $this->fb_post_attachement['subattachments'] ) ) { // check if post has subattachements
				$this->subattachements =  $this->fb_post_attachement['subattachments']; // store subattachements
			}

			$this->switch_attachement_types(); //execute attachement process

		}


			

		

		

		if ( $fb_post_attachements > 0 ) {
			$this->switch_attachement_types();
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
			
			default:
				# code...
				break;
		}


	}

	private function album_template() {
		add_post_meta( $this->new_post_id, 'attachement_type', $this->fb_post_attachement['type'], true );
	}


	private function import_image_from_url() {

	}



};

