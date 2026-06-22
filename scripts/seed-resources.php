<?php
/**
 * Seed sample Resource posts for local development.
 *
 * @package cvipi
 */

if ( 'cli' !== PHP_SAPI ) {
	$allowed_hosts = array( 'cvipi.local', 'localhost', '127.0.0.1' );
	$request_host  = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( strtok( preg_replace( '/[^a-z0-9.:-]/i', '', $_SERVER['HTTP_HOST'] ), ':' ) ) : '';

	if ( ! in_array( $request_host, $allowed_hosts, true ) ) {
		http_response_code( 403 );
		exit( 'Forbidden' );
	}
}

$wp_load_path = dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! file_exists( $wp_load_path ) ) {
	fwrite( STDERR, "Could not find wp-load.php.\n" );
	exit( 1 );
}

require_once $wp_load_path;

$categories = array(
	'archived-events-webinars' => 'Archived Events & Webinars',
	'toolkits'                 => 'Toolkits',
	'research-reports'         => 'Research & Reports',
	'other-resources'          => 'Other Resources',
);

foreach ( $categories as $slug => $name ) {
	if ( ! term_exists( $slug, 'category' ) ) {
		wp_insert_term(
			$name,
			'category',
			array(
				'slug' => $slug,
			)
		);
	}
}

$resources = array(
	array(
		'title'       => 'Community Violence Intervention Webinar: Lessons From the Field',
		'slug'        => 'community-violence-intervention-webinar-lessons-from-the-field',
		'category'    => 'archived-events-webinars',
		'excerpt'     => 'A recorded conversation with practitioners about outreach, partnership, and long-term community safety strategies.',
		'content'     => 'This archived webinar shares practical lessons from community violence intervention practitioners working with residents, local agencies, and service providers.',
		'date'        => '2026-02-14 10:00:00',
		'custom_date' => '20260214',
		'length'      => '58 min',
		'type'        => 'Video',
		'link_text'   => 'Watch Webinar',
		'youtube'     => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
		'tags'        => array( 'webinar', 'community violence intervention' ),
	),
	array(
		'title'       => 'Hospital-Based Violence Intervention Roundtable Recording',
		'slug'        => 'hospital-based-violence-intervention-roundtable-recording',
		'category'    => 'archived-events-webinars',
		'excerpt'     => 'A panel discussion on referral pathways, survivor support, and the hospital-community partnership model.',
		'content'     => 'This resource captures key takeaways from a roundtable focused on hospital-based violence intervention implementation.',
		'date'        => '2025-11-08 10:00:00',
		'custom_date' => '20251108',
		'length'      => '42 min',
		'type'        => 'Video',
		'link_text'   => 'View Recording',
		'youtube'     => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
		'tags'        => array( 'webinar', 'hospital-based intervention' ),
	),
	array(
		'title'       => 'CVI Partnership Planning Toolkit',
		'slug'        => 'cvi-partnership-planning-toolkit',
		'category'    => 'toolkits',
		'excerpt'     => 'A practical planning tool for defining roles, timelines, and coordination routines across local CVI partnerships.',
		'content'     => 'Use this toolkit to map partner responsibilities, meeting rhythms, shared measures, and communication expectations.',
		'date'        => '2026-01-22 10:00:00',
		'custom_date' => '20260122',
		'length'      => '18 pages',
		'type'        => 'PDF',
		'link_text'   => 'Download Toolkit',
		'document'    => 'https://example.com/cvi-partnership-planning-toolkit.pdf',
		'tags'        => array( 'toolkit', 'partnerships' ),
	),
	array(
		'title'       => 'Outreach Team Supervision Checklist',
		'slug'        => 'outreach-team-supervision-checklist',
		'category'    => 'toolkits',
		'excerpt'     => 'A checklist for supervisors supporting outreach workers through coaching, documentation, and debriefing routines.',
		'content'     => 'This checklist helps teams establish consistent supervision practices and support outreach worker wellbeing.',
		'date'        => '2025-09-18 10:00:00',
		'custom_date' => '20250918',
		'length'      => '6 pages',
		'type'        => 'PDF',
		'link_text'   => 'Open Checklist',
		'document'    => 'https://example.com/outreach-team-supervision-checklist.pdf',
		'tags'        => array( 'toolkit', 'outreach' ),
	),
	array(
		'title'       => 'Research Brief: Community-Led Safety and Violence Reduction',
		'slug'        => 'research-brief-community-led-safety-and-violence-reduction',
		'category'    => 'research-reports',
		'excerpt'     => 'A concise research brief summarizing evidence and implementation considerations for community-led safety work.',
		'content'     => 'This report summarizes emerging research on community-led safety strategies and the conditions that support durable reductions in violence.',
		'date'        => '2026-03-04 10:00:00',
		'custom_date' => '20260304',
		'length'      => '14 pages',
		'type'        => 'PDF',
		'link_text'   => 'Read Brief',
		'document'    => 'https://example.com/community-led-safety-research-brief.pdf',
		'tags'        => array( 'research', 'community-led safety' ),
	),
	array(
		'title'       => 'Report: Building Data Capacity for CVI Programs',
		'slug'        => 'building-data-capacity-for-cvi-programs',
		'category'    => 'research-reports',
		'excerpt'     => 'A report on data practices, outcome tracking, and responsible evaluation for community violence intervention programs.',
		'content'     => 'This report offers guidance for building practical data capacity while protecting trust and centering community context.',
		'date'        => '2025-07-10 10:00:00',
		'custom_date' => '20250710',
		'length'      => '26 pages',
		'type'        => 'PDF',
		'link_text'   => 'Download Report',
		'document'    => 'https://example.com/building-data-capacity-cvi-programs.pdf',
		'tags'        => array( 'research', 'data' ),
	),
	array(
		'title'       => 'Funding Readiness Worksheet',
		'slug'        => 'funding-readiness-worksheet',
		'category'    => 'other-resources',
		'excerpt'     => 'A worksheet to help organizations gather core materials before applying for funding or technical assistance.',
		'content'     => 'This worksheet helps teams organize budgets, narratives, partnership details, and program materials.',
		'date'        => '2025-05-29 10:00:00',
		'custom_date' => '20250529',
		'length'      => '4 pages',
		'type'        => 'PDF',
		'link_text'   => 'Download Worksheet',
		'document'    => 'https://example.com/funding-readiness-worksheet.pdf',
		'tags'        => array( 'funding', 'worksheet' ),
	),
	array(
		'title'       => 'CVI Communications Template Pack',
		'slug'        => 'cvi-communications-template-pack',
		'category'    => 'other-resources',
		'excerpt'     => 'Editable language for partner updates, community announcements, and program one-pagers.',
		'content'     => 'This template pack gives local teams a starting point for clear, consistent CVI communications.',
		'date'        => '2025-04-15 10:00:00',
		'custom_date' => '20250415',
		'length'      => 'Template pack',
		'type'        => 'PDF',
		'link_text'   => 'Get Templates',
		'document'    => 'https://example.com/cvi-communications-template-pack.pdf',
		'tags'        => array( 'communications', 'templates' ),
	),
);

foreach ( $resources as $resource ) {
	$existing = get_page_by_path( $resource['slug'], OBJECT, 'post' );

	$post_data = array(
		'post_title'   => $resource['title'],
		'post_name'    => $resource['slug'],
		'post_excerpt' => $resource['excerpt'],
		'post_content' => $resource['content'],
		'post_status'  => 'publish',
		'post_type'    => 'post',
		'post_date'    => $resource['date'],
	);

	if ( $existing ) {
		$post_data['ID'] = $existing->ID;
		$post_id         = wp_update_post( $post_data, true );
	} else {
		$post_id = wp_insert_post( $post_data, true );
	}

	if ( is_wp_error( $post_id ) ) {
		fwrite( STDERR, $resource['title'] . ': ' . $post_id->get_error_message() . "\n" );
		continue;
	}

	$category_term = get_term_by( 'slug', $resource['category'], 'category' );

	if ( $category_term ) {
		wp_set_post_terms( $post_id, array( (int) $category_term->term_id ), 'category' );
	}

	wp_set_post_terms( $post_id, $resource['tags'], 'post_tag' );

	update_post_meta( $post_id, 'resource_custom_date', $resource['custom_date'] );
	update_post_meta( $post_id, 'resource_length', $resource['length'] );
	update_post_meta( $post_id, 'resource_type', $resource['type'] );
	update_post_meta( $post_id, 'resource_link_text', $resource['link_text'] );
	update_post_meta( $post_id, 'resource_youtube_link', isset( $resource['youtube'] ) ? $resource['youtube'] : '' );
	update_post_meta( $post_id, 'resource_document', isset( $resource['document'] ) ? $resource['document'] : '' );
	update_post_meta( $post_id, 'featured_resource', 0 );
}

echo 'Seeded ' . count( $resources ) . " resources.\n";
