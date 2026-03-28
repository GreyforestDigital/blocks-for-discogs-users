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
$data = $discogs->fetch(['endpoint' => 'profile', 'username' => $username]);
$data_cv = $discogs->fetch(['endpoint' => 'collectionvalue', 'username' => $username]);

if (!empty($data['error']) || !empty($data_cv['error']) ) {
	$outputData = !empty($data['error']) ? $data : $data_cv;
	echo wp_kses($discogs->render_error_html($outputData), 'post');
	return;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div <?php echo wp_kses_post(!$is_editor ? $wrapper_attributes : ''); ?>>

	<section id="<?php echo esc_attr($blockID); ?>" class="discogs-user-collection-value">

		<?php if ( ! empty($attributes['display_section_title']) ) : ?>
		<h2><?php echo esc_html(!empty($attribute['name']) ? $attribute['name'] : $username.'\'s Collection'); ?></h2>
		<?php endif; ?>
		
		<?php if (!empty($data) && !empty($data_cv)) : ?>

			<ul class="discogs-collection-values">
				<?php if (!empty($attributes['display_collection_count']) && !empty($data['num_collection'])) : ?>
				<li class="discogs-collection-values-count">
					<p><?php echo esc_html(number_format((int)$data['num_collection'] ?? 0,0,'.',',')); ?></p>
					<small>Releases</small>
				</li>
				<?php endif; ?>
				<?php if (!empty($attributes['display_collection_minimum']) && !empty($data_cv['minimum'])) : ?>
				<li class="discogs-collection-values-minimum">
					<p><?php echo esc_html($data_cv['minimum'] ?? 0); ?></p>
					<small>Minimum</small>
				</li>
				<?php endif; ?>
				<?php if (!empty($attributes['display_collection_median']) && !empty($data_cv['median'])) : ?>
				<li class="discogs-collection-values-median">
					<p><?php echo esc_html($data_cv['median'] ?? 0); ?></p>
					<small>Median</small>
				</li>
				<?php endif; ?>
				<?php if (!empty($attributes['display_collection_maximum']) && !empty($data_cv['maximum'])) : ?>
				<li class="discogs-collection-values-maximum">
					<p><?php echo esc_html($data_cv['maximum'] ?? 0); ?></p>
					<small>Maximum</small>
				</li>
				<?php endif; ?>
			</ul>

		<?php endif; ?>

	</section>

</div>