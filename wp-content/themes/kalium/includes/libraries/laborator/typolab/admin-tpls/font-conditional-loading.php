<?php
/**
 * Kalium WordPress Theme
 *
 * Conditional Font Loading.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$conditional_statements = [
	'data'       => [],
	'statements' => kalium_get_array_key( $font['options'], 'conditional_loading', [] ),
];

$conditional_statements['data']['post_types'] = [];
$conditional_statements['data']['taxonomies'] = [];

// Page Types
$conditional_statements['data']['page_types'] = [
	'frontpage' => [ 'name' => 'Front Page' ],
	'blog'      => [ 'name' => 'Blog Page' ],
	'search'    => [ 'name' => 'Search Page' ],
	'not_found' => [ 'name' => '404 Page' ],
];

// Page Templates
$conditional_statements['data']['page_template'] = [];

foreach ( get_page_templates() as $template_name => $template_filename ) {
	$conditional_statements['data']['page_template'][ $template_filename ] = [
		'name' => $template_name
	];
}

// Public Post Types
$all_post_types   = get_post_types( '', 'objects' );
$added_post_types = get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' );

$added_post_types = array_merge( [
	'post' => $all_post_types['post'],
	'page' => $all_post_types['page'],
], $added_post_types );

foreach ( $added_post_types as $post_type_id => $post_type ) {
	// Post Type
	$conditional_statements['data']['post_types'][ $post_type_id ] = [
		'name'     => $post_type->labels->name,
		'singular' => $post_type->labels->singular_name
	];

	// Post Type Taxonomies
	$post_type_taxonomies = get_object_taxonomies( $post_type_id );

	foreach ( $post_type_taxonomies as $taxonomy_id ) {
		$taxonomy = get_taxonomy( $taxonomy_id );

		if ( $taxonomy->show_ui ) {
			$conditional_statements['data']['taxonomies'][ $taxonomy_id ] = [
				'name'      => $taxonomy->labels->name,
				'singular'  => $taxonomy->labels->singular_name,
				'post_type' => reset( $taxonomy->object_type )
			];
		}
	}
}

// Preload Data Objects
foreach ( $conditional_statements['statements'] as $conditional ) {
	$statement = $conditional['statement'];
	$criteria  = $conditional['criteria'];

	// Post Items
	if ( isset( $conditional_statements['data']['post_types'][ $statement ] ) ) {
		$conditional_statements['data'][ $statement ] = [ TypoLab::any_item() ];

		$posts_query = new WP_Query( [
			'post_type'      => $statement,
			'posts_per_page' => - 1
		] );

		while ( $posts_query->have_posts() ) {
			$posts_query->the_post();

			$conditional_statements['data'][ $statement ][] = [
				'value' => get_the_ID(),
				'text'  => get_the_title()
			];
		}

		wp_reset_postdata();
	} // Taxonomy Items
	else {
		if ( isset( $conditional_statements['data']['taxonomies'][ $statement ] ) ) {
			$conditional_statements['data'][ $statement ] = [ TypoLab::any_item() ];

			$terms = get_terms( [
				'taxonomy'   => $statement,
				'hide_empty' => false
			] );

			foreach ( $terms as $term ) {
				$conditional_statements['data'][ $statement ][] = [
					'value' => $term->term_id,
					'text'  => $term->name
				];
			}
		}
	}
}
?>
<script id="conditional-statements" type="text/template"><?php echo wp_json_encode( $conditional_statements ); ?></script>

<table class="typolab-table typolab-table--conditional-loading">
    <thead>
    <tr>
        <th>Conditional Loading</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td class="no-padding">

            <table class="font-conditional-loading">
                <thead>
                <tr>
                    <th class="statement">Include Font When</th>
                    <th class="operator">Operator</th>
                    <th class="criteria">Criteria</th>
                    <th class="actions"></th>
                </tr>
                </thead>
                <tbody>
                <tr class="no-statements">
                    <td colspan="3">
                        No defined conditional statements. Font will be loaded in all pages.
                    </td>
                </tr>
                </tbody>
            </table>

        </td>
    </tr>
    <tr class="hover">
        <td>
            <a href="#" id="add-new-conditional-statement" class="button">
                <i class="dashicons dashicons-plus"></i>
                Add Conditional Statement
            </a>
        </td>
    </tr>
    </tbody>
</table>