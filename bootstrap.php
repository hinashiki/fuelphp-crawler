<?php
/**
 * @package    Crawler
 * @version    0.1
 * @author     Hinashiki
 * @license    MIT License
 * @copyright  2014 - Hinashiki
 * @link       https://github.com/hinashiki/fuelphp-crawler
 */

Autoloader::add_namespace('Crawler', __DIR__.'/classes/');
Autoloader::add_core_namespace('Crawler');
require __DIR__.'/vendor/simple_html_dom.php';

Autoloader::add_classes(array(
	/**
	 * Map classes.
	 */
	'Crawler\\Crawl'       => __DIR__.'/classes/crawl.php',
	'Crawler\\Crawl_Image' => __DIR__.'/classes/crawl/image.php',
	'Crawler\\Crawler'     => __DIR__.'/tasks/crawler.php',
));
