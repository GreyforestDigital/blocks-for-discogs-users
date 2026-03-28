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

if (empty($attributes['list_id'])) :
	$data = $discogs->fetch(['endpoint' => 'lists', 'username' => $username]);
else :
	$data = $discogs->fetch(['endpoint' => 'list', 'id'=>$attributes['list_id'] ?? '']);
endif;

if (!empty($data['error']) ) {
	echo wp_kses($discogs->render_error_html($data), 'post');
	return;
}

if (empty($attributes['list_id']) && empty($data['lists'])) :
	echo wp_kses($discogs->render_error_html([
		'error'=>true,
		'message'=>'NO LISTS FROM THIS USER'
	]), 'post');
	return;
endif;

if (!empty($attributes['list_id']) && empty($data['items'])) :
	echo wp_kses($discogs->render_error_html([
		'error'=>true,
		'message'=>'NO ITEMS IN THIS LIST'
	]), 'post');
	return;
endif;
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!empty($data['lists']) && $is_editor) : 
	$data_list = '';
	echo '
	<section class="discogs-list-picker">
		<ul>';
		foreach ($data['lists'] as $list) :
			if ($list['public'] == true) :
			echo '
			<li class="discogs-list-item">
				<div class="discogs-list-image">
					<img alt="'.esc_html($list['name']).' List Art" src="'.esc_url($list['image_url']).'">
				</div>
				<div class="discogs-list-content">
					<p>
						<strong>
							<a href="'.esc_url($list['uri']).'" target="_blank">'.esc_html($list['name']).'</a>
						</strong>
					</p>
				</div>
				<div class="discogs-list-copy">	
					<small>
						<button class="button discogs-list-id-copy" data-id="'. esc_attr($list['id']).'">Copy List ID</button>
						<br>ID: '. esc_html($list['id']).'
					</small>
				</div>
			</li>';
			endif;
		endforeach;
	echo '
		</ul>
	</section>';

	return;

elseif (!empty($data['lists']) && !$is_editor) :

	return;

endif;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<div <?php echo wp_kses_post(!$is_editor ? $wrapper_attributes : ''); ?>>

	<section id="<?php echo esc_attr($blockID); ?>" class="discogs-user-list">

		<?php if ($attributes['display_list_name']) : ?>
		<h2><?php echo esc_html($data['name']); ?></h2>
		<?php endif; ?>

		<?php if ($attributes['display_list_description']) : ?>
		<p><?php echo esc_html($data['description']); ?></p>
		<?php endif; ?>

		<?php if (!empty($data['items'])) : ?>
		<ul class="discogs-user-list discogs-layout-<?php echo esc_attr($attributes['layout_style'] ?? 'cards'); ?> discogs-columns-<?php echo esc_attr($attributes['layout_columns'] ?? 3); ?>">

			<?php foreach ($data['items'] as $release) : ?>
			<li id="discogs-<?php echo esc_attr($release['id']); ?>">

				<?php if ($attributes['display_release_image']) : ?>
				<div class="discogs-release-image">
					<a title="<?php echo esc_attr($release['display_title']); ?>" target="_blank" href="<?php echo esc_url($release['uri']); ?>" >
						<img loading="lazy" style="object-fit:<?php echo esc_attr($release['type'] == 'release' ? 'cover' : 'contain'); ?>" alt="<?php echo esc_attr($release['display_title'].' Cover'); ?>" src="<?php echo esc_url(!empty($release['image_url']) ? $release['image_url'] : GF_BLOCKS_FOR_DISCOGS_USERS_PLUGIN_URL.'/assets/img/PLACEHOLDER.jpg'); ?>">
					</a>
				</div>
				<?php endif; ?>

				<div class="discogs-release-information">

					<div class="discogs-release-titles">

						<?php if ($attributes['display_release_title']) : ?>
						<h5>
							<a target="_blank" href="<?php echo esc_url($release['uri']); ?>" >
								<?php echo esc_html($release['display_title']); ?>
							</a>
						</h5>
						<?php endif; ?>

						<?php if ($attributes['display_release_comment'] && !empty($release['comment'])) : ?>
						<p><?php echo esc_html($release['comment']); ?></p>
						<?php endif; ?>

					</div>

				</div>
			</li>
			<?php endforeach; ?>

		</ul>
		<?php endif; ?>

	</section>

</div>