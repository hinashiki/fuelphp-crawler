<?php
/**
 * for crawling
 *
 */
namespace Crawler;
class Crawl
{
	const USLEEP_TIME = 800000; // 0.8sec
	const USLEEP_TIME_WITH_PROXY = 400000; // 0.4sec
	const DUMMY_AGENT = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)';
	private static $__proxy_list = array();
	private static $__current_proxy_num = 0;
	private static $__last_proxy_get_date = 0;

	// return headers
	protected static $_return_headers = array();

	// request_type page or image
	protected static $_request_type = 'page';

	// request_method get or post
	protected static $_request_method = 'get';

	// request headers
	protected static $_request_headers = array();

	// request body
	protected static $_request_body = array();

	// リトライ機構
	const RETRY_MAX_CNT = 7;
	private static $__retry_cnt = 0;

	// Refreshダミー機構
	const REFRESH_MAX_CNT = 10;
	private static $__refresh_cnt = 0;

	/**
	 * Init, config loading.
	 */
	public static function _init()
	{
		\Config::load('crawler', true);
	}

	/**
	 * access and return parsed html object
	 *
	 * @param string $uri
	 *        bool   $use_proxy
	 *        bool   $refresh
	 * @return mixed simple_html_dom
	 *               string
	 */
	public static function curl($uri, $use_proxy = false, $refresh = false)
	{
		$curl = \Request::forge($uri, 'curl')
		        ->set_method(static::$_request_method)
		        ->set_option(CURLOPT_TIMEOUT, 10)
		        ->set_option(CURLOPT_USERAGENT, self::DUMMY_AGENT);
		foreach(static::$_request_headers as $header => $content)
		{
			$curl->set_header($header, $content);
		}
		if(static::$_request_type === 'image')
		{
			$curl->set_option(CURLOPT_RETURNTRANSFER, 1);
		}
		if( ! empty(static::$_request_body))
		{
			$curl->set_params(static::$_request_body);
		}
		if($use_proxy)
		{
			$proxy_list = static::_get_proxy();
			self::$__current_proxy_num = rand(0, (count($proxy_list) - 1));
			$proxy = $proxy_list[self::$__current_proxy_num];
			$curl->set_option(CURLOPT_PROXY, $proxy);

			// for loop use if not refresh
			if( ! $refresh)
			{
				usleep(self::USLEEP_TIME_WITH_PROXY);
			}
		}
		elseif( ! $refresh)
		{
			// for loop use
			usleep(self::USLEEP_TIME);
		}

		try
		{
			$curl->execute();
			static::$_return_headers = $curl->response_info();
			$result = $curl->response()->body;
			if(static::$_request_type === 'page')
			{
				$result = \str_get_html($result);
				if( ! empty($result->find('title', 0)) and preg_match('/アクセスが?制限/', $result->find('title', 0)->plaintext))
				{
					// access restrict occured. retry use other proxy
					throw new \RequestException($curl->response()->body, 401);
				}
				if( is_object($result->find('meta[http-equiv=Refresh]', 0)))
				{
					if(self::$__refresh_cnt > self::REFRESH_MAX_CNT)
					{
						throw new \Exception('Refresh loop...');
					}
					self::$__refresh_cnt++;
					return self::curl($uri, $use_proxy, true);
				}
			}
			// retry, refresh reset
			self::$__retry_cnt = 0;
			self::$__refresh_cnt = 0;
		}
		catch(\RequestException $e)
		{
			// 404, または画像収集の場合はRequestStatusException側へ
			if($e->getCode() == 404 or static::$_request_type === 'image')
			{
				throw new \RequestStatusException($e->getMessage(), $e->getCode());
			}
			self::$__retry_cnt++;
			if(self::$__retry_cnt <= self::RETRY_MAX_CNT)
			{
				\Log::warning(__METHOD__.': retry execute. now cnt='.self::$__retry_cnt);
				// おそらくタイムアウトになったであろうproxyは時間短縮のため再利用しない
				if($use_proxy)
				{
					unset(self::$__proxy_list[self::$__current_proxy_num]);
					// array key reset
					self::$__proxy_list = array_values(self::$__proxy_list);
				}
				return self::curl($uri, $use_proxy);
			}
			// retry回数が一定以上に達したら終了
			throw new \RequestException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 * 適切なクロールデータトリム
	 *
	 */
	protected static function _trim($str)
	{
		return trim(html_entity_decode(preg_replace('/[\s(\r?\n)]+/', ' ', str_replace('　', ' ', $str))));
	}

	/**
	 * proxyリストの取得
	 *
	 * @return array
	 */
	protected static function _get_proxy()
	{
		// 一定時間が過ぎるかもしくは候補プロキシがN値以下の場合にリストを再取得する
		if(
			strtotime('-10 hours') < self::$__last_proxy_get_date and
			count(self::$__proxy_list) >= 3 // 3つ以下しか使いまわせなくなったらリストを更新
		)
		{
			return self::$__proxy_list;
		}
		$list = array();
		// 野良プロキシの取得
		if( ! empty(\Config::get('crawler.get_proxy.app_key')))
		{
			$url = 'http://www.getproxy.jp/proxyapi?ApiKey='.\Config::get('crawler.get_proxy.app_key').'&area=JP&sort=requesttime&orderby=asc';
			$curl = \Request::forge($url, 'curl')
			        ->set_method('get')
			        ->set_option(CURLOPT_TIMEOUT, 60)
			        ->execute();
			$xml = \Format::forge($curl->response()->body, 'xml')->to_array();
			if( ! isset($xml['errorinfo']) and count($xml) > 1)
			{
				foreach($xml['item'] as $proxy)
				{
					$list[] = $proxy['ip'];
				}
			}
		}
		// merge custom proxies
		$list = array_merge($list, \Config::get('crawler.custom_proxies'));
		self::$__proxy_list = $list;
		self::$__last_proxy_get_date = strtotime('now');
		return $list;
	}

	/**
	 * 最後に利用したプロキシ取得
	 *
	 */
	protected static function _get_last_use_proxy()
	{
		return self::$__proxy_list[self::$__current_proxy_num];
	}
}
