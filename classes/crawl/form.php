<?php
namespace Crawler;
class Crawl_Form extends \Crawl
{

	/**
	 *
	 * @param string $uri
	 * @param array $data formData
	 */
	public static function post($uri, $data)
	{
		static::$_request_headers = array(
			'Content-Type' => 'application/x-www-form-urlencoded',
			'X-Requested-With' => 'XMLHttpRequest',
		);
		static::$_request_method = 'post';
		static::$_request_body = (array) $data;
		return self::curl($uri);
	}
}
