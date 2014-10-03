<?php
namespace Crawler;
class Crawl_Image extends \Crawl
{
	// request_type page or image
	protected static $_request_type = 'image';

	public static function get($uri)
	{
		$binary = self::curl($uri, true);
		$return = static::$_return_headers;
		$return['body'] = $binary;
		return $return;
	}
}
