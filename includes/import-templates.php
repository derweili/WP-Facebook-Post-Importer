<?php 

class WPFPI_IMPORT_TEMPLATES {
	
	private $new_post_id;
	private $fb_post;
	private $fb_post_attachement;
	private $subattachements;

	private 

	public function __construct( $new_post_id, $fb_post, $fb_post_attachements ) {
		$this->new_post_id =  $new_post_id;
		$this->fb_post =  $fb_post;
		$this->fb_post_attachement =  $fb_post_attachements[0];

		if ( isset( $this->fb_post_attachement['subattachments'] ) ) {
			$this->subattachements =  $this->fb_post_attachement['subattachments'];
		}

		if ( $fb_post_attachements > 0 ) {
			$this->switch_attachement_types();
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

