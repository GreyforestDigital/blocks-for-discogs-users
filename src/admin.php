<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class BlocksForDiscogsUsers_Admin {

	private $logger;
	private $plugin;

	///////////////////////////////////////////////////////////////////////////
    public function __construct($plugin) {

		$this->plugin = $plugin;

		//if (!is_plugin_active('blocks-for-discogs-users/blocks-for-discogs-sellers.php')) {
			add_action( 'admin_menu', [ $this, 'add_settings_menu' ] );
		//}
    }

	///////////////////////////////////////////////////////////////////////////
    /**
     * Add settings page to the admin menu
     */
    public function add_settings_menu() {

		add_submenu_page( 
			'options-general.php', 
			'Blocks for Discogs', 
			'Blocks for Discogs', 
			'manage_options', 
			$this->plugin->plugin_data['key'], 
			[ $this, 'render_settings_page' ],
			99
		);

    }

	///////////////////////////////////////////////////////////////////////////
    /**
     * Render the settings page HTML
     */
    public function render_settings_page() {

		$settings = get_option($this->plugin->plugin_data['key'].'__settings',[]);

		$apiData = $this->plugin->class_api->validate_api_rates();

		$rate_total = (int)$apiData['data']['total'] ?? false;
		$rate_used = floatval($apiData['data']['used']) ?? 0;
		$rate_remaining = (int)$apiData['data']['remaining'] ?? 0;
		$rate_percent = ($rate_used / $rate_total) * 100;

		if ($rate_percent >= 100) :
			$color = '#ff0c00';
			$message = 'You have exceeded your API rate limits';
		elseif ($rate_percent >= 80) :
			$color = '#ff6000';
			$message = 'You are close to exceeding your API rate limits';
		elseif ($rate_percent >= 65) :
			$color = '#ffa700';
			$message = 'You are using more than half of your API rate limits';
		elseif ($rate_percent >= 50) :
			$color = '#ffe300';
			$message = 'You are using more than half of your API rate limits';
		else :
			$color = '#19c500';
			$message = '';
		endif; 

        ?>
        <div class="wrap">

            <h1>Blocks for Discogs : Settings</h1>
        	<p>To use Blocks for Discogs in the Block Editor, provide your Discogs username and personal access token.</p>

            <form action="options.php" method="post">
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">Discogs Username</th>
							<td>
								<input type="text" name="<?php echo esc_attr($this->plugin->plugin_data['key']); ?>__settings[username]" value="<?php echo esc_html($settings['username'] ?? ''); ?>" min="" max="" step="">
								<p class="description">Enter your Discogs username. <a href="https://accounts.discogs.com/register" target="_blank">Create one here</a>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Discogs Personal Access Token</th>
							<td>
								<input type="text" name="<?php echo esc_attr($this->plugin->plugin_data['key']); ?>__settings[token]" value="<?php echo esc_html($settings['token'] ?? ''); ?>" min="" max="" step="">
								<p class="description">Enter your Discogs token. <a href="https://www.discogs.com/settings/developers" target="_blank">Generate a token here</a>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Discogs Transient Cache Length</th>
							<td>
								<input type="number" name="<?php echo esc_attr($this->plugin->plugin_data['key']); ?>__settings[transient]" value="<?php echo esc_html($settings['transient'] ?? 60); ?>" min="1" max="1440" step="1">
								<p class="description">Choose a length (in minutes) for the API calls to be cached as a transient. Shorter lengths update data more frequently but use more API calls; longer lengths are ideal for high-traffic sites or data that doesn't change often.
								<br><a href="https://www.discogs.com/developers#page:home,header:home-rate-limiting" target="_blank">Learn about rate limits here</a>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Discogs Rate Limits</th>
							<td>	
								<div id="discogs-rate-limits" style="margin:0;height:40px;border-radius:4px;overflow:hidden;border:1px solid #aaa;background:rgba(0,0,0,0.1);position:relative;width:100%;display:block;">
									<div id="discogs-rate-used" style="font-weight:bold;color:#fff;text-shadow:1px 1px 0px #000;padding:0 15px;position:relative;z-index:2;float:left;line-height:40px"><?php echo esc_html($rate_used); ?></div>
									<div id="discogs-rate-total" style="font-weight:bold;color:#fff;text-shadow:1px 1px 0px #000;padding:0 15px;position:relative;z-index:2;float:right;line-height:40px"><?php echo esc_html($rate_total); ?></div>
									<div id="discogs-rate-message" style="font-weight:bold;color:#fff;text-shadow:1px 1px 0px #000;padding:0 15px;position:relative;z-index:2;float:none;text-align:center;line-height:40px"><?php echo esc_html($message); ?></div>
									<div id="discogs-rate-current" style="position:absolute;top:0;left:0;height:100%;z-index:1;display:block;width:<?php echo esc_html($rate_percent); ?>%;background:<?php echo esc_html($color); ?>;"></div>
								</div>
								<p>This is your current API rate limit value from Discogs. It represents the amount of requests within the past 60 seconds. Authenticated requests using a token are granted 60 requests.</p>
								<p><strong>Last Updated:</strong> <?php echo esc_html(wp_date('F j, Y h:i:sa',$apiData['data']['last_updated'])); ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php ///// FORM : Save Options ///// ?>
				<?php wp_nonce_field('update-options'); ?>
				<input type="hidden" name="page_options" value="<?php echo esc_html($this->plugin->plugin_data['key'].'__settings'); ?>" />
				<input type="hidden" name="action" value="update" />
				<?php ///// FORM : Save Options ///// ?>

				<button class="gf-plugin-button button" type="submit">Save Settings</button>
            </form>

            <hr style="margin: 2em 0;">

            <?php $this->section_cache(); ?>

            <hr style="margin: 2em 0;">

            <?php $this->section_logging(); ?>

        </div>
        <?php
    }

	///////////////////////////////////////////////////////////////////////////
    /**
     * Display logging table
     */
    public function section_logging() {
	?>

		<div class="gf-settings-header">
			<h3>STATUS / DEBUG LOG</h3>
			<form action="" method="post">
				<?php wp_nonce_field( 'gf_clear_logs_action', 'gf_clear_logs_nonce' ); ?>
				<button name="gf_clear_logs" value="<?php echo esc_html($this->plugin->plugin_data['key']); ?>" class="gf-plugin-button button">Clear Logs</button>
			</form>
		</div>
		<div class="gf-table-wrapper">
			<table class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<th style="width:80px">Index</th>
					<th style="width:180px">Date</th>
					<th style="width:120px">Level</th>
					<th>Message</th>
					<th style="width:50%">Context</th>
				</thead>
				<tbody>
					<?php
					$logFile = wp_upload_dir()['basedir'] . '/plugin-logs/'.$this->plugin->plugin_data['key'].'.json';
					$logFileContents = file_get_contents($logFile) ?? false;

					if ($logFileContents) :
						$logs = json_decode($logFileContents, true);

						// Check if decoding was successful
						if (is_array($logs)) :
							$logs = array_reverse($logs);
							$logsCount = count($logs);
							foreach ($logs as $index => $log) :
								if ($log['level'] == 'error') : $style = 'style="color:red"'; else : $style = ''; endif;
								echo '
								<tr>
									<td>' . esc_html($logsCount - (int)$index) . '</td>
									<td>' . esc_html(wp_date('M d, Y h:i:sa',$log['timestamp'])) . '</td>
									<td ' . esc_attr($style) . '>' . esc_html(htmlspecialchars($log['level'])) . '</td>
									<td>' . esc_html(htmlspecialchars($log['message'])) . '</td>
									<td>' . esc_html(htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ))) . '</td>
								</tr>';
							endforeach;
						else :
							echo '<tr><td colspan="5">Invalid JSON structure in log file.</td></tr>';
						endif;

					else:

						echo '<tr><td colspan="5">No Log File</td></tr>';

					endif;
					?>
				</tbody>
			</table>
		</div>

		<?php
		if (file_exists($logFile)) {
			echo '<p><strong>Current Log File Size:</strong> '. esc_html(size_format( filesize($logFile) )) .'</p>';
		}

    }

	///////////////////////////////////////////////////////////////////////////
    /**
     * Display logging table
     */
    public function section_cache() {

		$cached = $this->plugin->class_cache;
		$cached_transients = $cached->get_transients_list($this->plugin->plugin_data['key']);
		
		if (!empty($cached_transients)) {
		$transients = json_decode($cached_transients, true);
		$total_bytes = array_sum( array_column( $transients, 'size_bytes' ) );
		}
		?>

		<div class="gf-settings-header">
			<h3>CACHED DATA</h3>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'gf_clear_cache_action', 'gf_clear_cache_nonce' ); ?>
				<input type="hidden" name="action" value="<?php echo esc_html($this->plugin->plugin_data['key']);?>_clear_cache">
				<button name="gf_clear_cache" value="true" class="gf-plugin-button button">Clear Cache</button>
			</form>			
		</div>
		<div class="gf-table-wrapper">
			<table class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<th style="width:80px">Size</th>
					<th style="width:180px">Expires</th>
					<th>Key</th>
					<th>Endpoint</th>
					<th>ID</th>
				</thead>
				<tbody>
					<?php
					if ($cached_transients) :

						// Check if decoding was successful
						if (is_array($transients)) :
							$transients = array_reverse($transients);
							foreach ($transients as $index => $transient) :
								echo '
								<tr>
									<td>' . esc_html(htmlspecialchars($transient['size_human'])) . '</td>
									<td>' . esc_html(gmdate('M d, Y h:i:sa',strtotime($transient['expires']))) . '</td>
									<td>' . esc_html(htmlspecialchars($transient['key'])) . '</td>
									<td>' . esc_html(htmlspecialchars($transient['endpoint'])) . '</td>
									<td>' . esc_html(htmlspecialchars($transient['id'])) . '</td>
								</tr>';
							endforeach;
						else :
							echo '<tr><td colspan="5">Invalid JSON structure in transients array.</td></tr>';
						endif;

					else:

						echo '<tr><td colspan="5">No Transients</td></tr>';

					endif;
					?>
				</tbody>
			</table>
		</div>

		<p><strong>Current Cache Size:</strong> <?php echo esc_html(size_format( $total_bytes ?? 0 )); ?></p>

	<?php
    }

}
