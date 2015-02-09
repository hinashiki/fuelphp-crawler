<?php
namespace Crawler;
class Crawl_Ajax extends \Crawl
{

	/**
	 *
	 * @param string $uri
	 * @param array $data formData
	 */
	public static function get($uri, $data, $use_proxy = false)
	{
		static::$_request_headers = array(
			'Content-Type' => 'application/json',
			'X-Requested-With' => 'XMLHttpRequest',
		);
		static::$_request_type = 'json';
		$query = '';
		if( ! empty($data))
		{
			$query = '?'.http_build_query($data);
		}
		return self::curl($uri.$query, $use_proxy);
	}

	/**
	 *
	 * @param string $uri
	 * @param array $data formData
	 */
	public static function post($uri, $data, $use_proxy = false)
	{
		static::$_request_headers = array(
			'Content-Type' => 'application/json',
			'X-Requested-With' => 'XMLHttpRequest',
		);
		static::$_request_type = 'json';
		static::$_request_method = 'post';
		static::$_request_body = (array) $data;
		return self::curl($uri, $use_proxy);
	}
}
