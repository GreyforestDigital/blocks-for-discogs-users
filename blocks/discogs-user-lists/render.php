<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$align = !empty( $attributes['align'] ) ? esc_attr( $attributes['align'] ) : '';
$anchor = !empty( $attributes['anchor'] ) ? esc_attr( $attributes['anchor'] ) : esc_attr('wp-block-' . wp_rand());
$blockID = !empty( $attributes['blockID'] ) ? esc_attr( $attributes['blockID'] ) : esc_attr('wp-block-' . wp_rand());
$is_editor = defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$wrapper_attributes = get_block_wrapper_attributes([
	'id' => $anchor,
	'class' => 'gf-blocks-for-discogs'
]);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$settings = get_option('gf_blocks_for_discogs__settings');

// USERNAME
if (!empty($attributes['username'])) :
	$username = preg_replace('/[^A-Za-z0-9._-]/', '', $attributes['username']);
else : 
	$username = !empty($settings['username']) ? $settings['username'] : '';
endif;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$discogs = new BlocksForDiscogsUsers_API();
$data = $discogs->fetch(['endpoint' => 'lists', 'username' => $username]);

if (!empty($data['error']) ) {
	echo wp_kses($discogs->render_error_html($data), 'post');
	return;
}

if (empty($data['lists'])) :
	echo wp_kses($discogs->render_error_html([
		'error'=>true,
		'message'=>'NO LISTS FROM THIS USER'
	]), 'post');
	return;
endif;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div <?php echo wp_kses_post(!$is_editor ? $wrapper_attributes : ''); ?>>

	<section id="<?php echo esc_attr($blockID); ?>" class="discogs-user-lists">

		<?php if (!empty($data['lists'])) : ?>
		<ul>

			<?php foreach ($data['lists'] as $list) : if ($list['public'] == true) : ?>

			<li id="discogs-list-<?php echo esc_attr($list['id']); ?>" class="<?php echo esc_attr($attributes['display_list_image'] != true ? 'no-image' : ''); ?>">

				<?php if ($attributes['display_list_image'] == true) : ?>
					
				<div class="discogs-list-image">
					<a target="_blank" href="<?php echo esc_url($list['uri']); ?>" title="<?php echo esc_html($list['name']); ?>">
						<img loading="lazy" alt="<?php echo esc_attr($list['name'].' Cover'); ?>" src="<?php echo esc_url($list['image_url']); ?>">
					</a>
				</div>
					
				<?php endif; ?>

				<div class="discogs-list-information">

					<?php if ($attributes['display_list_title'] == true) : ?>
					<h3>
						<a target="_blank" href="<?php echo esc_url($list['uri']); ?>" >
							<?php echo esc_html($list['name']); ?>
						</a>
					</h3>
					<?php endif; ?>

					<?php if ($attributes['display_list_date_added'] == true || $attributes['display_list_date_changed'] == true ) : ?>
					<p>
						<?php
						if ($attributes['display_list_date_added'] == true) :
							echo '<span class="user-lists-date-added">Created: '.esc_html(wp_date('F j, Y',strtotime($list['date_added']))).'</span>';
						endif;
						if ($attributes['display_list_date_changed'] == true) :
							echo '<span class="user-lists-date-changed">Last Updated: '.esc_html(wp_date('F j, Y',strtotime($list['date_changed']))).'</span>';
						endif;
						?>
					</p>
					<?php endif; ?>

					<?php if ($attributes['display_list_description'] == true) : ?>
					<small><?php echo nl2br(esc_html($list['description'])); ?></small>
					<?php endif; ?>

				</div>
			</li>
			<?php endif; endforeach; ?>

		</ul>
		<?php endif; ?>

	</section>

</div>