<?php
namespace Fuel\Tasks;
class Crawler
{

	public function run()
	{
		\Cli::write(\Cli::color('this is a test. get yahoo.co.jp', 'light_blue'));
		$html = \Crawler\Crawl::curl('http://www.yahoo.co.jp');
		\Cli::write($html->plaintext);
	}
}
