<?php

/*
 * @license	coypleft
 * @author	gloomers@gmail.com
 */
/*
 * USAGE:
 * require_once('FileSessionManager.class.php');
 * FileSessionManager::$filename = 'path/to/writable/session_data_file.txt';
 * Podio::setup(CLIENT_ID, CLIENT_SECRET, array('session_manager' => 'FileSessionManager'));
 *
 * IMPORTANT:
 * 1) Session data file has to br protected from public reading;
 * 2) Sessions data file has to be writable by the PHP;
*/
class FileSessionManager{
	public static $filename	= null;
	public static $session	= null;
	
	public function __construct(){
		if(file_exists(self::$filename)){
			$session_data	= file_get_contents(self::$filename);
			if(!$session_data){
				$unserialized = unserialize($session_data);
				if(!$unserialized){
					self::$session = $unserialized;
				}
			}
		}
    }
	
	public function get(){
		if(self::$session != null){
			return new PodioOAuth(
				self::$session['access_token'],
				self::$session['refresh_token'],
				self::$session['expires_in'],
				self::$session['ref']
			);
		}else{
			return new PodioOAuth();
		}
	}
	public function set($oauth){
		self::$session = array(
			'access_token'	=> $oauth->access_token,
			'refresh_token'	=> $oauth->refresh_token,
			'expires_in'	=> $oauth->expires_in,
			'ref'			=> $oauth->ref
		);
		try{
			file_put_contents(self::$filename, serialize(self::$session));
		}catch(Exception $e){
			throw $e->getMessage();
		}
	}
	public function destroy(){
		try{
			unlink(self::$filename);
		}catch(Exception $e){
			throw $e->getMessage();
		}
		self::$session = null;
	}
}
?>

