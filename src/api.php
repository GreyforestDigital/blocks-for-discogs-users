<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class BlocksforDiscogsUsers_API {

	private $logger;
	private $settings;
	private $token;
	private $cache_ttl;
	private $plugin;
	private $plugin_key = 'gf_blocks_for_discogs';

	///////////////////////////////////////////////////////////////////////////
	public function __construct() {

		// start logging class
		$this->logger = new BlocksForDiscogsUsers_Logging();

		// set shared variables for use
		$this->settings = get_option($this->plugin_key.'__settings', []);
		$this->token = $this->settings['token'] ?? '';
		$this->cache_ttl = isset($this->settings['transient']) ? intval($this->settings['transient']) * MINUTE_IN_SECONDS : 60 * MINUTE_IN_SECONDS;
	
	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Perform cache check, build API fetch url, perform API call, cache and return data
	 * Example: $discogs->fetch(['endpoint'=>'profile','username'=>'foo']);
	 *
	 * @since 1.0.0
	 * @return array	 
	 */
	public function fetch(array $args) {

		$key = $this->cache_key($args);
		$is_editor = defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit';

		// 1. Try cache first
		$cached = get_transient($key);
		if (empty($args['nocache']) && $cached !== false && !in_array($args['endpoint'], ['ratelimit'])) {
			return $cached;
		}

		// 2. Build API endpoint
		$fetch_url = $this->build_fetch_url($args);

		if (!$fetch_url) {
			$error = $this->error('Invalid or incomplete Discogs parameters.');
			return $error;
		} elseif (!empty($fetch_url['error'])) {
			$error = $this->error($fetch_url['message']);
			return $error;
		}

		// 3. Make API request
		$data = $this->api_get($fetch_url);
		if (!empty($data['error'])) {
			return $data;
		}

		// 4. Cache and return
		if ($args['endpoint'] != 'ratelimit' && empty($args['nocache'])) {
			$data['cache'] = true;
			set_transient($key, $data, $this->cache_ttl);
		}
		return $data;
	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Perform API GET call
	 *
	 * @since 1.0.0
	 * @return array		 
	 */
	private function api_get($fetch_url) {
		if (empty($this->token)) {
			return $this->error('Discogs API token missing.');
		}

		$apiRates = $this->validate_api_rates();

		if (!empty($apiRates['error'])) {
			return $this->error('No Discogs API calls remaining. Please wait at least 60 seconds.');
		}		

		$url = 'https://api.discogs.com/' . ltrim($fetch_url, '/');
		$response = wp_remote_get($url, [
			'headers' => [
				'User-Agent'    => home_url(),
				'Authorization' => 'Discogs token=' . $this->token,
			]
		]);

		if (is_wp_error($response)) {
			$this->logger->log('error','API call to Discogs failed',ltrim($fetch_url, '/').' | '.json_encode($response));
			return $this->error('Failed to reach Discogs API.');
		}

		// GET RATE LIMIT DATA
		$headers = wp_remote_retrieve_headers( $response );
		$rate_limit = [
			'total'     => $headers['x-discogs-ratelimit'] ?? null,
			'used'      => $headers['x-discogs-ratelimit-used'] ?? null,
			'remaining' => $headers['x-discogs-ratelimit-remaining'] ?? null,
			'last_updated' => time(),
		];

		update_option($this->plugin_key.'__api_rates',$rate_limit);

		// GET API DATA
		$data = json_decode(wp_remote_retrieve_body($response), true);
		$data['rate'] = $rate_limit;
		
		if (isset($data['message']) || isset($data['error']) || isset($data['detail']) ) {
			$this->logger->log('error','API call to Discogs returned error',ltrim($fetch_url, '/').' | '.(isset($data['detail']) ? json_encode($data['detail']) : $data['message']));
			return $this->error(isset($data['detail']) ? json_encode($data['detail']) : $data['message'], $data);
		}

		$this->logger->log('info','API call to Discogs successful',($fetch_url == '/' ? 'Rate limit check' : ltrim($fetch_url, '/') ));

		return $data;
	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Build the Discogs API endpoint URL based on provided args
	 *
	 * @since 1.0.0
	 * @return string	 
	 */
	private function build_fetch_url($args) {

		if ($args['endpoint'] == 'collection_value' && $this->settings['username'] != $args['username']) :
			return $this->error('Collection values can only be retrieved for usernames matching access token.');
		endif;

		$type = $args['endpoint'] ?? '';
		$username = $args['username'] ?? ($this->settings['username'] ?? '');
		$id = $args['id'] ?? '';
		$page = $args['page'] ?? 1;
		$per_page = $args['per_page'] ?? 20;
		$sort_order = $args['sort_order'] ?? 'asc';
		$sort = $args['sort'] ?? ($type == 'inventory' ? 'listed' : 'added');

		switch ($type) {
			case 'profile':
				if ($username == '') return false;
				return "users/$username";
			case 'wants':
				if ($username == '') return false;
				return "users/$username/wants?page=$page&per_page=$per_page&sort=$sort&sort_order=$sort_order";
			case 'collection':
				if ($username == '') return false;
				return "users/$username/collection/folders/0/releases?page=$page&per_page=$per_page&sort=$sort&sort_order=$sort_order";
			case 'collectionvalue':
				if ($username == '') return false;
				return "users/$username/collection/value";
			case 'lists':
				if ($username == '') return false;
				return "users/$username/lists";
			case 'list':
				if ($id == '') return false;
				return "lists/$id";
			case 'inventory':
				if ($username == '') return false;
				return "users/$username/inventory?status=for%20sale&page=$page&per_page=$per_page&sort=$sort&sort_order=$sort_order";
			case 'listing':
				if ($id == '') return false;
				return "marketplace/listings/$id";
			case 'release':
				if ($id == '') return false;
				return "releases/$id";
			case 'ratelimit':
				return "/";
			default:
				return false;
		}
	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Check current API rates before allowing call to Discogs
	 *
	 * @since 1.0.0
	 * @return array	 
	 */
	public function validate_api_rates() {

		$apiRates = get_option($this->plugin_key.'__api_rates',[]);

		if (empty($apiRates['last_updated']) || ($apiRates['last_updated'] + 60) < time()) {

			$apiRates = [
				'total'=>60,
				'used'=>0,
				'remaining'=>60,
				'last_updated'=>time()
			];
			update_option($this->plugin_key.'__api_rates',$apiRates);

			$response = ['data'=>$apiRates];

		} else {

			if ($apiRates['remaining'] <= 3) {

				$response = ['error'=>true, 'data'=>$apiRates];

			} else {

				$response = ['data'=>$apiRates];

			}

		}

		return $response;

	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Generate a safe transient key
	 *
	 * @since 1.0.0
	 * @return string	 
	 */
	private function cache_key($args) {

		if (isset($args['page']) && $args['page'] > 1) {
			$args['username'] = $args['username'].'_page'.$args['page'];
		}

		$cache_key_raw = sanitize_title($this->plugin_key.'__' . $args['endpoint'].'_'.($args['id'] ?? $args['username']));
		$cache_key = substr( $cache_key_raw, 0, 120 );
		return $cache_key;
		// transient keys max ~172 chars
		//$raw = 'gf_blocks_for_discogs_users__' . md5(json_encode($args));
		//return substr($raw, 0, 172); // transient keys max ~172 chars
	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Standardized error response
	 *
	 * @since 1.0.0
	 * @return array	 
	 */
	private function error($message, $data = null) {
		return [ 
			'error' => true, 
			'message' => esc_html($message), 
			'rate' => $data ? $data['rate'] : []
		];
	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Render a nice HTML block for errors
	 *
	 * @since 1.0.0
	 * @return string	 
	 */
	public function render_error_html($data) {

		$msg = esc_html($data['message'] ?? 'Unknown error');
		return '
			<div class="discogs-block-message discogs-block-error">
				<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 100 100" xml:space="preserve"> <path d="M52.2,49.9c0,1.2-1,2.1-2.1,2.1c-1.2,0-2.1-1-2.1-2.1s1-2.1,2.1-2.1C51.2,47.8,52.2,48.8,52.2,49.9z M25.4,49.9 	c0,7,2.9,13.2,7.5,17.7l4.6-5.1l0.1,0.1c-3.4-3.2-5.4-7.7-5.4-12.8c0-9.8,8-17.8,17.8-17.8c3.3,0,6.3,0.9,8.9,2.4l3.3-6 	c-3.6-2-7.7-3.2-12.2-3.2C36.5,25.3,25.4,36.3,25.4,49.9L25.4,49.9z M67.8,49.9c0,9.8-8,17.8-17.7,17.8c-3,0-5.8-0.7-8.2-2l-3.4,5.9 	c3.5,1.9,7.5,2.9,11.6,2.9c13.6,0,24.7-11.1,24.7-24.7c0-6.7-2.7-12.9-7.1-17.3l-4.7,5.1C66,40.9,67.8,45.1,67.8,49.9L67.8,49.9z 	 M17.6,49.9c0,9.2,3.9,17.6,10.1,23.5l4.7-5.1c-4.9-4.6-7.9-11.2-7.9-18.4c0-14.1,11.4-25.5,25.5-25.5c4.6,0,8.9,1.2,12.6,3.4l3.4-6 	c-4.7-2.7-10.1-4.2-16-4.2c-17.8-0.1-32.3,14.2-32.5,32C17.6,49.7,17.6,49.8,17.6,49.9L17.6,49.9z M75.5,49.9 	C75.5,64,64.1,75.5,50,75.5c-4.2,0-8.4-1-12.1-3l-3.4,6c15.8,8.5,35.5,2.6,44-13.2c2.5-4.7,3.9-10,3.9-15.4c0-9-3.6-17.1-9.6-23 	L68.2,32C72.8,36.6,75.5,42.9,75.5,49.9L75.5,49.9z M83.3,49.9c0,18.5-15.1,33.4-33.5,33.3c-5.5,0-10.9-1.4-15.7-4l-3.5,6.1 	c5.8,3.1,12.3,5,19.3,5c22.3,0,40.3-18.1,40.3-40.3c0-11.3-4.6-21.5-12.1-28.7l-4.7,5.1C79.6,32.3,83.3,40.7,83.3,49.9z M9.7,49.9 	c0,11.5,4.9,21.9,12.6,29.3L27,74c-6.6-6.3-10.4-15-10.4-24.1c0-18.4,15-33.3,33.3-33.3c5.9,0,11.5,1.6,16.3,4.3l3.4-6.1 	C63.8,11.5,57.1,9.6,50,9.6C27.8,9.6,9.7,27.6,9.7,49.9L9.7,49.9z M83.5,15.5l-4.6,5c7.6,7.5,12.4,17.9,12.4,29.4 	C91.3,72.5,72.9,91,50.2,91c-7.2,0-13.8-1.9-19.7-5.1L27,92c6.8,3.7,14.7,5.9,23.1,5.9c26.5,0,48.1-21.6,48.1-48.1 	C98.1,36.4,92.5,24.3,83.5,15.5L83.5,15.5z M8.8,49.9C8.8,27.2,27.3,8.8,50,8.8c7.3,0,14.2,2,20.1,5.3l3.3-5.9 	C66.7,4.3,59,2.1,50.7,2h-0.8C23.5,2,2.1,23.3,1.8,49.6v0.4C1.8,63.8,7.7,76.3,17,85l4.8-5.1C13.8,72.3,8.8,61.7,8.8,49.9L8.8,49.9z 	"/> </svg>
				<span>'.$msg.'</span>
			</div>';
	}

}