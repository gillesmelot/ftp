<?php namespace Gime\Ftp;

use Config;
use Response;

class Ftp {
	
	/**
	 * Transfer file to a directory path by FTP
	 * 
	 * @param string $data
	 * @param string $filename
	 * @param string $directory
	 * 
	 * @return boolean
	 */
	public static function transfer( $data, $filename, $directory = null, $cnx = null )
	{
		$return = FALSE;
		
		// Set protocol
		$cnx = Ftp::getProtocol( $cnx );
		
		// Set complete path on FTP server
		$path = $cnx. ( !is_null( $directory ) ? '/'. $directory : '' ) . '/'. $filename;
		
		// Write file on FTP
		$stream_options = array('ftp' => array('overwrite' => true));
		$stream_context = stream_context_create($stream_options);
		$response = @file_put_contents($path, $data, 0, $stream_context);
		if( $response !== FALSE ){
			$return = TRUE;
		}
		
		return $return;
	}
	
	/**
	 * Transfer file to a directory path by FTP
	 * 
	 * @param string $content
	 * @param string $path
	 * 
	 * @return mixed
	 */
	public static function download( $path, $cnx = null )
	{
		$return = FALSE;
		
		// Set protocol
		$cnx = Ftp::getProtocol( $cnx );
		
		// Path to file
		$path = $cnx.'/'.$path;
		
		$content = @file_get_contents($path);
		
		if( $content !== FALSE ){
			
			$extract = explode('/',$path);
			$filename = array_pop( $extract );
			
			$return = Response::make($content,200);
			$return->header('Content-Type','octet/stream');
			$return->header('Content-disposition','attachment; filename="'.$filename.'"');
		}
		
		return $return;
	}
	
	
	private static function getProtocol( $cnx=null )
	{
		$return = '';
		
		// Get default connection if not specified
		if( is_null( $cnx ) ){
			$cnx = Config::get('ftp::default');
		}
		
		// Retrieve params of connection
		$params = Config::get('ftp::connections.'.$cnx);
		if( !is_null( $params ) ){
			$protocole = ( $params['secure'] ? 'sftp' : 'ftp' );
			$url = $params['username'];
			if( !empty( $params['password'] ) ){
				$url.= ':'.$params['password'];
			}
			$url.= '@'.$params['host'];
			$return = $protocole.'://'.$url;
		}
		
		return $return;
	}
}
