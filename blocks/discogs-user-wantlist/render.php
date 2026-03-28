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

// SORT
if (isset($_REQUEST['sort'])) :
	$sort = sanitize_text_field(wp_unslash($_REQUEST['sort']));
elseif (isset($attributes['sorting_sort'])) :
	$sort = $attributes['sorting_sort'];
else :
	$sort = 'listed';
endif;

// SORT ORDER
if (isset($_REQUEST['sort_order']) && in_array( sanitize_text_field(wp_unslash($_REQUEST['sort_order'])), ['asc','desc']) ) :
	$sort_order = sanitize_text_field(wp_unslash($_REQUEST['sort_order']));
elseif (isset($attributes['sorting_sort_order'])) :
	$sort_order = $attributes['sorting_sort_order'];
else :
	$sort_order = 'asc';
endif;

// PER PAGE
if (isset($_REQUEST['per_page']) && (int)sanitize_text_field(wp_unslash($_REQUEST['per_page'])) <= 100) :
	$per_page = sanitize_text_field(wp_unslash($_REQUEST['per_page']));
elseif (isset($attributes['sorting_per_page'])) :
	$per_page = (int)$attributes['sorting_per_page'];
else :
	$per_page = 50;
endif;

// PAGE
if (isset($_GET['discogs_page'])) :
	$current_page = (int)sanitize_text_field(wp_unslash($_GET['discogs_page']));
else :
	$current_page = 1;
endif;

// CLEAN URL FOR PAGINATION
$currentURL = home_url( add_query_arg( null, null ) );
$paginationURL = remove_query_arg( 'discogs_page', $currentURL );

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// GET DISCOGS DATA
$args = [
	'endpoint' => 'wants',
	'id' => null,
	'page' => $current_page,
	'per_page' => $per_page,
	'sort' => $sort,
	'sort_order' => $sort_order,
	'username' => $username,
	'nocache' => $attributes['sorting_allow_user_sort'] ?? false,
];

$discogs = new BlocksForDiscogsUsers_API();
$data = $discogs->fetch($args);

if (!empty($data['error']) ) {
	echo wp_kses($discogs->render_error_html($data), 'post');
	return;
}

if (!empty($data['wants'])) :
	$releases = $data['wants'];
elseif (!empty($data['releases'])) :
	$releases = $data['releases'];
elseif (!empty($data['listings'])) :
	$releases = $data['listings'];
endif;

if (empty($releases)) :
	echo wp_kses($discogs->render_error_html([
		'error'=>true,
		'message'=>'NO ITEMS IN THIS WANTLIST'
	]), 'post');
	return;
endif;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<div <?php echo wp_kses_post(!$is_editor ? $wrapper_attributes : ''); ?>>

	<section id="<?php echo esc_attr($blockID); ?>" class="discogs-user-wantlist">

		<?php if ( ! empty($attributes['display_section_title']) ) : ?>
		<h2><?php echo esc_html($username.'\'s Wantlist'); ?></h2>
		<?php endif; ?>
		
		<?php if ( ! empty($attributes['sorting_allow_user_sort']) ) : ?>
		<form action="" method="get" class="discogs-sorting">

			<div>
				<label>Sort</label>
				<select name="sort">
					<option <?php if ($sort == 'added') : echo 'selected'; endif; ?> value="added">Added</option>
					<option <?php if ($sort == 'price') : echo 'selected'; endif; ?> value="price">Price</option>
					<option <?php if ($sort == 'title') : echo 'selected'; endif; ?> value="title">Title</option>
					<option <?php if ($sort == 'artist') : echo 'selected'; endif; ?> value="artist">Artist</option>
					<option <?php if ($sort == 'label') : echo 'selected'; endif; ?> value="label">Label</option>
					<option <?php if ($sort == 'format') : echo 'selected'; endif; ?> value="format">Format</option>
					<option <?php if ($sort == 'catno') : echo 'selected'; endif; ?> value="catno">Catalog Number</option>
					<option <?php if ($sort == 'rating') : echo 'selected'; endif; ?> value="rating">Rating</option>
				</select>
			</div>

			<div>
				<label>Sort Order</label>
				<select name="sort_order">
					<option value="asc" <?php if ($sort_order == 'asc') : echo 'selected'; endif; ?>>Ascending (A to Z)</option>
					<option value="desc" <?php if ($sort_order == 'desc') : echo 'selected'; endif; ?>>Descending (Z to A)</option>
				</select>
			</div>

			<div>
				<label>Per Page</label>
				<select name="per_page">
					<option value="10" <?php if ($per_page == 10) : echo 'selected'; endif; ?>>10</option>
					<option value="20" <?php if ($per_page == 20) : echo 'selected'; endif; ?>>20</option>
					<option value="50" <?php if ($per_page == 50) : echo 'selected'; endif; ?>>50</option>
					<option value="100" <?php if ($per_page == 100) : echo 'selected'; endif; ?>>100</option>
				</select>
			</div>

			<button class="wp-element-button">Sort</button>

		</form>
		<?php endif; ?>


		<?php if ($is_editor && !empty($data['cache'])) { echo '<span style="position:absolute;z-index:100;top:10px;right:10px;background:#ff0000;color:#fff;padding:5px 15px;border-radius:10px;font-size:10px;display:inline-block">CACHED DATA</span>'; } ?>


		<?php if (!empty($releases)) : ?>
		<ul class="discogs-layout-<?php echo esc_attr($attributes['layout_style'] ?? 'cards'); ?> discogs-columns-<?php echo esc_attr($attributes['layout_columns'] ?? 3); ?>">

			<?php foreach ($releases as $release) : ?>
			<li id="discogs-release-<?php echo esc_attr($release['id']); ?>">

				<?php if ($attributes['display_release_image']) : ?>
				<div class="discogs-release-image">
					<a target="_blank" href="<?php echo esc_url('https://discogs.com/release/'.$release['id']); ?>" >
						<img loading="lazy" alt="<?php echo esc_attr($release['basic_information']['title'].' Cover'); ?>"  src="<?php echo esc_url(!empty($release['basic_information']['cover_image']) ? $release['basic_information']['cover_image'] : GF_BLOCKS_FOR_DISCOGS_USERS_PLUGIN_URL.'/assets/img/PLACEHOLDER.jpg'); ?>">
					</a>
				</div>
				<?php endif; ?>

				<div class="discogs-release-information">

					<div class="discogs-release-titles">

						<?php if ($attributes['display_release_title']) : ?>
						<h5><?php echo esc_html($release['basic_information']['title']); ?></h5>
						<?php endif; ?>

						<?php if ($attributes['display_release_artist']) : ?>
						<h6>
							<a href="<?php echo esc_url('https://discogs.com/artist/'.$release['basic_information']['artists'][0]['id']); ?>" >
								<?php echo esc_html($release['basic_information']['artists'][0]['name']); ?>
							</a>
						</h6>
						<?php endif; ?>

					</div>

					<div class="discogs-release-meta">

						<?php if ($attributes['display_release_format']) : ?>
						<span class="discogs-release-meta-format">
							Format: <?php
							echo esc_html($release['basic_information']['formats'][0]['name']);
							
							if (!empty($release['basic_information']['formats'][0]['descriptions'])) : 
								echo ', <i>'. esc_html($release['basic_information']['formats'][0]['descriptions'][0] ?? '') .'</i>';
							endif;
							if (!empty($release['basic_information']['formats'][0]['text'])) : 
								echo ', <i>'. esc_html($release['basic_information']['formats'][0]['text'] ?? '') .'</i>';
							endif;
							?>
						</span>
						<?php endif; ?>

						<?php if ($attributes['display_release_year']) : ?>
						<span class="discogs-release-meta-year">
							Year: <?php echo esc_html($release['basic_information']['year']); ?>
						</span>
						<?php endif; ?>

						<?php if ($attributes['display_release_labels']) : ?>
						<span class="discogs-release-meta-labels">
							Label: <?php echo esc_html($release['basic_information']['labels'][0]['name']); ?>
						</span>
						<?php endif; ?>

						<?php if ($attributes['display_release_genres']) : ?>
						<span class="discogs-release-meta-genres">
							Genres: <?php echo esc_html(implode(', ',$release['basic_information']['genres'])); ?>
						</span>
						<?php endif; ?>

						<?php if ($attributes['display_release_styles'] == true && !empty($release['basic_information']['styles'])) : ?>
						<span class="discogs-release-meta-styles">
							Styles: <?php echo esc_html(implode(', ',$release['basic_information']['styles'])); ?>
						</span>
						<?php endif; ?>

					</div>

				</div>
			</li>
			<?php endforeach; ?>

		</ul>
		<?php endif; ?>

		<?php 
		if (!empty($data['pagination']) && !empty($attributes['display_pagination'] && (int)$data['pagination']['pages'] > 1)) : 

			$current       = isset($_GET['discogs_page']) ? max(1, (int)$_GET['discogs_page']) : 1;
			$total         = (int)$data['pagination']['pages'];
			$paginationURL = remove_query_arg('discogs_page');

			echo '<nav class="discogs-pagination">';

			// Prev button
			if ($current > 1) {
				$pageURL = add_query_arg('discogs_page', $current - 1, $paginationURL);
				echo '<a class="prev-btn" href="'.esc_url($pageURL).'">Prev</a> ';
			}

			// Dropdown
			echo '<form method="get" style="display:inline;">';

				// Keep other query parameters intact
				if (isset($_GET['sort'])) :
    				$discogsSort = sanitize_text_field( wp_unslash( $_GET['sort'] ) );
					echo '<input type="hidden" name="sort" value="'.esc_attr($discogsSort).'">';
				endif;

				if (isset($_GET['sort_order'])) :
    				$discogsSortOrder = sanitize_text_field( wp_unslash( $_GET['sort_order'] ) );
					echo '<input type="hidden" name="sort_order" value="'.esc_attr($discogsSortOrder).'">';
				endif;

				if (isset($_GET['per_page'])) :
    				$discogsPerPage = sanitize_text_field( wp_unslash( $_GET['per_page'] ) );
					echo '<input type="hidden" name="per_page" value="'.esc_attr($discogsPerPage).'">';
				endif;

				// Output pages dropdown
				echo '<select name="discogs_page" onchange="this.form.submit()">';

				for ($p = 1; $p <= $total; $p++) {
					$selected = ($p == $current ? 'selected' : '');
					echo '<option value="'.esc_attr($p).'" '.esc_attr($selected).'>Page '.esc_attr($p).'</option>';
				}

				echo '</select>';

			echo '</form>';

			// Next button
			if ($current < $total) {
				$pageURL = add_query_arg('discogs_page', $current + 1, $paginationURL);
				echo ' <a class="next-btn" href="'.esc_url($pageURL).'">Next</a>';
			}

			echo '</nav>';

		endif;
		?>

	</section>

</div>