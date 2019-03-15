<?php
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

/**
 * Install pre-built websites remote API
 * 
 * @author Muffin Group
 * @version 1.0
 */
class Mfn_Importer_API extends Mfn_API {
	
	protected $code 		= '';
	protected $demo 		= '';
	
	protected $path_tmp	= '';
	protected $path_demo 	= '';
	
	/**
	 * The constructor
	 */
	public function __construct( $demo = false ){
	
		if( ! $demo ){
			return false;
		}
		
		$this->code = mfn_get_purchase_code();
		$this->demo = $demo;
		
		$upload_dir = wp_upload_dir();
		$this->path_tmp = wp_normalize_path( $upload_dir['basedir'] . '/be-demo-tmp' );
	
		$this->mkdir();
	}
	
	/**
	 * Directories creation
	 */
	protected function mkdir(){
		
		if( ! file_exists( $this->path_tmp ) ){
			wp_mkdir_p( $this->path_tmp );
		}
		
		$this->path_demo = wp_normalize_path( $this->path_tmp .'/'. $this->demo );
		if( ! file_exists( $this->path_demo ) ){
			wp_mkdir_p( $this->path_demo );
		}
	}
	
	/**
	 * Delete temporary directory
	 */
	public function delete_temp_dir(){

		// filesystem
		$wp_filesystem = Mfn_Helper::filesystem();
		
		// director is located outside wp uploads dir
		$upload_dir = wp_upload_dir();
		if( false === strpos( $this->path_demo, $upload_dir['basedir'] ) ){
			return false;
		}
		
		$wp_filesystem->delete( $this->path_demo, true );
	}

	/**
	 * Remote get demo
	 */
	public function remote_get_demo(){
		
		$args = array(
			'code' => $this->code,
			'demo' => $this->demo,
		);
			
		if( mfn_is_hosted() ){
			$args[ 'ish' ] = mfn_get_ish();
		}
		
		$url = add_query_arg( $args, $this->get_url( 'websites_download' ) );
		
		$args = array(
			'user-agent' 	=> 'WordPress/'. get_bloginfo( 'version' ) .'; '. network_site_url(),
			'timeout' 		=> 30,
		);
	
		$response = wp_remote_get( $url, $args );

		if( is_wp_error( $response ) ){
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		
		// remote get fallback
		if( empty( $body ) ){
			if( function_exists( 'ini_get' ) && ini_get( 'allow_url_fopen' ) ){
				$body = @file_get_contents( $url );
			}
		}

		if( empty( $body ) ){
			return new WP_Error( 'error_download', __( 'The package could not be downloaded.', 'mfn-opts' ) );
		}	
		
		if( $json = json_decode( $body, true ) ){
			if( isset( $json['error'] ) ){
				return new WP_Error( 'invalid_response', $json['error'] );
			}
		}

		// filesystem
		$wp_filesystem = Mfn_Helper::filesystem();
		
		$path_zip = wp_normalize_path( $this->path_demo .'/'. $this->demo .'.zip' );
		$path_unzip = wp_normalize_path( $this->path_demo .'/'. $this->demo );

		if( ! $wp_filesystem->put_contents( $path_zip, $body, FS_CHMOD_FILE ) ){
			
			// put_contents fallback
			@unlink( $path_zip );
			$fp = @fopen( $path_zip, 'w' );
			$fwrite = @fwrite( $fp, $body );
			@fclose( $fp );
			if( false === $fwrite ){
				return new WP_Error( 'error_fs', __( 'WordPress filesystem error.', 'mfn-opts' ) );
			}
			
		}

		$unzip = unzip_file( $path_zip, $this->path_demo );
		if( is_wp_error( $unzip ) ){
			return new WP_Error( 'error_unzip', __( 'The package could not be unziped.', 'mfn-opts' ) );
		}

		if( ! is_dir( $path_unzip ) ) {
			return new WP_Error( 'error_folder', sprintf( __( 'Demo data folder does not exist (%s).', 'mfn-opts' ), $path_unzip ) );
		}

		return $path_unzip;
	}
	
}
