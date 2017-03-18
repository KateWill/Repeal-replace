<?php
defined( 'ABSPATH' ) or die( 'No direct access!' );

class CaptainForm_WP_POSTS {
	private static $public_key;
	private static $message;
	private static $signature;
	private static $api_key;
	
	public function __construct($action){
    }
	
	function init_vars(){
		self::$public_key = $_REQUEST["pk"];
		self::$message = $_REQUEST["message"];
		self::$api_key = $_REQUEST["api_key"];
		self::$signature = base64_decode(str_replace(" ", "+", $_REQUEST["signature"]));
	}
	
	function connect(){
		self::init_vars();
		
		if (!isset(self::$public_key) || self::$public_key == "") 
		{
			echo self::message("Key is not sent", 0);
			exit();
		}
		
		if (!isset(self::$api_key) || self::$api_key == "" || self::$api_key != get_option($GLOBALS['captainform_option2'])) 
		{	
			echo self::message("Invalid API Key", 0);
			exit();
		}
		
		$verify = openssl_verify(self::$message, self::$signature, base64_decode(self::$public_key), OPENSSL_ALGO_SHA1);
		
		if ($verify == 1) 
		{
			if (!get_option("123cf_post_public_key")) 
			{
				add_option("123cf_post_public_key", self::$public_key);  
			} 
			else 
			{
				update_option("123cf_post_public_key", self::$public_key);
			}
			echo self::message("WordPress connected", 1);
		} 
		elseif ($verify == 0) 
		{
			echo self::message("Signature not verified", 0);
		} 
		else 
		{
			echo self::message("error: ".openssl_error_string(), 0);
		}
		exit();
	}
	
	private function authenticate() {
		if (!get_option("123cf_post_public_key")) 
		{
			return false; 
		}
		self::$public_key = get_option( "123cf_post_public_key");
		self::$message = $_REQUEST["message"];
		self::$signature = base64_decode(str_replace(" ", "+", $_REQUEST["signature"]));
		return openssl_verify(self::$message, self::$signature, base64_decode(self::$public_key), OPENSSL_ALGO_SHA1);
	}
	
	function check_connection() {
		if (!self::authenticate()) 
		{
			echo self::message("There was an error while trying to authenticate with wordpress", 0);
			exit();
		}
		echo self::message("Connection OK", 1);
		exit();
	}

	private function message($message, $status) {
		return json_encode(
			array(
				"message" => $message, 
				"status" => $status
			)
		);
	}
	
	function new_post(){
		if (!self::authenticate()) 
		{
			echo self::message("There was an error while trying to authenticate with wordpress",0); 
			exit(); 
		}
		
		$post_title = strip_tags(rawurldecode($_POST["post_title"]));
		$post_title = preg_replace("/&nbsp;/", ' ', $post_title);
		$post_title = stripslashes($post_title);
		
		$post_content = rawurldecode($_POST["post_content"]);
		$post_content = stripslashes($post_content);
		
		$post_status = $_POST["post_status"];
		$post_category = urldecode($_POST["post_category"]);
		$post_author = $_POST["post_author"];
		$post_format = $_POST["post_format"];
		
		$comments = $_POST["comment_status"];
		$comments == "1" ? $comment_status = "open" : $comment_status = "closed";
		
		$post_excerpt = rawurldecode($_POST["post_excerpt"]);
		$post_excerpt = preg_replace("/&nbsp;/",' ',$post_excerpt);
		$post_excerpt = stripslashes($post_excerpt);
		
		$post_tags = explode(",",rawurldecode($_POST["post_tags"]));
		
		$post_image = str_replace(" ", "+",$_POST["post_image"]);
		$post_image_name = $_POST["post_image_name"];    
		
		$custom_fields_keys = self::get_custom_post_fields();
		$custom_fields_values = array();
		foreach ($custom_fields_keys as $key)
		{
			if ($_POST[$key]) 
			{
			   $custom_fields_values[rawurldecode($key)] =  $_POST[$key];
			}
		}
		
		$post_categories = explode(",",$post_category);
		$cat_id_arr = array();
		if (is_array($post_categories)) 
		{
			foreach ($post_categories as $category_name) 
			{
				$category_id = get_cat_ID( $category_name );
				if ($category_id) 
				{
				   $cat_id_arr[] = $category_id; 
				}
			}
		}
		
		$new_post = array(
			'post_author'    => $post_author,    
			'post_title'     => $post_title,
			'post_content'   => $post_content,
			'post_status'    => $post_status,
			'comment_status' => $comment_status,
			'post_excerpt'   => $post_excerpt,
			'post_category'  => $cat_id_arr
		);
		
		$post_id = wp_insert_post( $new_post );
		if ($post_id) 
		{
			foreach ($custom_fields_values as $meta_key => $meta_value) 
			{
				add_post_meta($post_id, str_replace("|***|"," ",$meta_key), $meta_value);  
			} 
			set_post_format($post_id, $post_format);
			
			wp_set_post_tags($post_id, $post_tags);
			if (isset($post_image) && RetrieveExtension($post_image)) 
			{
				self::upload_image($post_id,$post_image,$post_image_name);   
			}
			echo self::message("New post created", 1); 
			exit();
		}
		echo self::message("There was an error while trying to create new post", 0);
		exit();
	}
	
	private function get_custom_post_fields(){
		global $wpdb;
		$custom_fields = array();
		$fields = $wpdb->get_results('SELECT DISTINCT meta_key FROM wp_postmeta', OBJECT);
		foreach ($fields as $field) 
		{
			if (substr($field->meta_key, 0, 1) != "_") 
			{
				$meta_key = str_replace(" ", "|***|", $field->meta_key);
				$custom_fields[] = $meta_key;
			}
		}
		return $custom_fields;
	}

	private function insert_child_category($category, $wp_categories){
		if ($category->parent == 0) 
		{
			$wp_categories[] = $category;
			$args = array('hierarchical' => true, 'hide_empty' => 0, 'child_of' => $category->cat_ID);
			$child_categories = get_categories($args);
			foreach ($child_categories as $child_cat) 
			{
				$wp_categories[] = $child_cat;
			}
		}
		return $wp_categories;
	}
	
	function get_wp_data() {
		if (!self::authenticate()) {
			echo captainform_cfp_message("There was an error while trying to authenticate with wordpress", 0);
			exit();
		}
		global $wpdb;
		$data = array();
		$custom_fields = array();

		$fields = $wpdb->get_results( "SELECT DISTINCT meta_key FROM {$wpdb->prefix}postmeta", OBJECT );
		foreach ($fields as $field) {
			if (substr($field->meta_key, 0, 1) != "_") {
				$custom_fields[] = $field->meta_key;
			}
		}
		$data["custom_fields"] = $custom_fields;
		$args = array('orderby' => 'name','hierarchical'=>true,'hide_empty'=>0,'parent'=>0 ); 
		$categories = get_categories( $args );
		$wp_categories = array();
		foreach ($categories as $category) {
			$wp_categories = self::insert_child_category($category,$wp_categories);
		}
		
		$data["categories"] = $wp_categories; 
		$args_authors = array('who' => 'author');
		$users = get_users($args_authors);
		$authors = array();
		foreach ($users as $user) {
		   $authors[] = array("id"=>$user->data->ID,"username"=>$user->data->user_login);
		}
		$data["authors"] = $authors;
		echo json_encode($data);
		exit();
	}
	
	private function upload_image($post_id, $post_image, $post_image_name = null) {
		$upload_dir = wp_upload_dir();
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
		$decoded_img = base64_decode($post_image);
		
		if (!$post_image_name) 
		{
			$filename = 'image.png'; 
		} 
		else 
		{
			$filename = $post_image_name; 
		}
		 
		$hashed_filename = md5($filename . microtime()) . '_' . $filename;
		$image_upload = file_put_contents( $upload_path . $hashed_filename, $decoded_img );
		
		if (!function_exists('wp_handle_sideload')) 
		{
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		
		if (!function_exists('wp_get_current_user')) 
		{
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}
		
		$file             = array();
		$file['error']    = '';
		$file['tmp_name'] = $upload_path . $hashed_filename;
		$file['name']     = $hashed_filename;
		$file['type']     = 'image/jpg';
		$file['size']     = filesize( $upload_path . $hashed_filename );
		
		$file_return = wp_handle_sideload($file, array('test_form' => false)); 
		$file_url = $file_return["file"];
		$filetype = wp_check_filetype(basename($file_url), null);
		
		$attachment = array(
			'guid'           =>  $upload_dir['url'] . '/' . basename( $file_url ), 
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		
		$attach_id = wp_insert_attachment( $attachment, $file_url, $post_id );
		
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_url );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		update_post_meta( $post_id, '_thumbnail_id', $attach_id );
	}
}

function RetrieveExtension($data){
    $imageContents = base64_decode($data);

    // If its not base64 end processing and return false
    if ($imageContents === false) {
        return false;
    }

    $validExtensions = array('png', 'jpeg', 'jpg', 'gif');

    $tempFile = tmpfile();

    fwrite($tempFile, $imageContents);

    $contentType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tempFile);

    fclose($tempFile);

    if (substr($contentType, 0, 5) !== 'image') {
        return false;
    }

    $extension = ltrim($contentType, 'image/');

    if (!in_array(strtolower($extension), $validExtensions)) {
        return false;
    }

    return $extension;
}

add_action( 'wp_ajax_cfp-connect', array('CaptainForm_WP_POSTS', 'connect'));
add_action( 'wp_ajax_nopriv_cfp-connect', array('CaptainForm_WP_POSTS', 'connect'));

add_action( 'wp_ajax_cfp-new-post', array('CaptainForm_WP_POSTS', 'new_post'));
add_action( 'wp_ajax_nopriv_cfp-new-post', array('CaptainForm_WP_POSTS', 'new_post'));

add_action('wp_ajax_cfp-get-wp-data', array('CaptainForm_WP_POSTS', 'get_wp_data'));
add_action('wp_ajax_nopriv_cfp-get-wp-data', array('CaptainForm_WP_POSTS', 'get_wp_data'));

add_action( 'wp_ajax_cfp-check-connection', array('CaptainForm_WP_POSTS', 'check_connection'));
add_action( 'wp_ajax_nopriv_cfp-check-connection', array('CaptainForm_WP_POSTS', 'check_connection'));