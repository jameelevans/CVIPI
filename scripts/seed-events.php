<?php
/**
 * Seed sample Events for local development.
 *
 * This uses the Local MySQL socket directly so it can run even when CLI
 * WordPress bootstrap cannot connect through wp-config.php.
 *
 * @package cvipi
 */

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'Forbidden' );
}

$socket = '/Users/jameelevans/Library/Application Support/Local/run/q95mqih80/mysql/mysqld.sock';
$db     = new mysqli( 'localhost', 'root', 'root', 'local', 0, $socket );

if ( $db->connect_error ) {
	fwrite( STDERR, $db->connect_error . "\n" );
	exit( 1 );
}

$db->set_charset( 'utf8mb4' );

function cvipi_seed_slug( $value ) {
	$value = strtolower( trim( $value ) );
	$value = preg_replace( '/[^a-z0-9]+/', '-', $value );
	return trim( $value, '-' );
}

function cvipi_seed_get_term_taxonomy_id( $db, $name, $taxonomy ) {
	$slug = cvipi_seed_slug( $name );
	$stmt = $db->prepare(
		'SELECT tt.term_taxonomy_id
		FROM wp_terms t
		JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
		WHERE t.slug = ? AND tt.taxonomy = ?
		LIMIT 1'
	);
	$stmt->bind_param( 'ss', $slug, $taxonomy );
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();

	if ( $row ) {
		return (int) $row['term_taxonomy_id'];
	}

	$stmt = $db->prepare( 'INSERT INTO wp_terms (name, slug, term_group) VALUES (?, ?, 0)' );
	$stmt->bind_param( 'ss', $name, $slug );
	$stmt->execute();
	$term_id = (int) $db->insert_id;

	$description = '';
	$parent      = 0;
	$count       = 0;
	$stmt        = $db->prepare( 'INSERT INTO wp_term_taxonomy (term_id, taxonomy, description, parent, count) VALUES (?, ?, ?, ?, ?)' );
	$stmt->bind_param( 'issii', $term_id, $taxonomy, $description, $parent, $count );
	$stmt->execute();

	return (int) $db->insert_id;
}

function cvipi_seed_event( $db, $event ) {
	$slug = cvipi_seed_slug( $event['title'] );
	$stmt = $db->prepare( 'SELECT ID FROM wp_posts WHERE post_type = "event" AND post_name = ? LIMIT 1' );
	$stmt->bind_param( 's', $slug );
	$stmt->execute();
	$existing = $stmt->get_result()->fetch_assoc();

	$post_author           = 1;
	$post_status           = 'publish';
	$post_type             = 'event';
	$comment_status        = 'closed';
	$ping_status           = 'closed';
	$post_content_filtered = '';
	$post_parent           = 0;
	$menu_order            = 0;
	$to_ping               = '';
	$pinged                = '';
	$post_password         = '';
	$guid                  = 'http://cvipi.local/events/' . $slug . '/';
	$post_mime_type        = '';
	$post_date             = $event['post_date'];
	$post_date_gmt         = gmdate( 'Y-m-d H:i:s', strtotime( $post_date ) );
	$post_modified         = current_time_string();
	$post_modified_gmt     = gmdate( 'Y-m-d H:i:s' );

	if ( $existing ) {
		$post_id = (int) $existing['ID'];
		$stmt    = $db->prepare(
			'UPDATE wp_posts
			SET post_title = ?, post_content = ?, post_excerpt = ?, post_date = ?, post_date_gmt = ?, post_modified = ?, post_modified_gmt = ?, post_status = ?
			WHERE ID = ?'
		);
		$stmt->bind_param(
			'ssssssssi',
			$event['title'],
			$event['content'],
			$event['excerpt'],
			$post_date,
			$post_date_gmt,
			$post_modified,
			$post_modified_gmt,
			$post_status,
			$post_id
		);
		$stmt->execute();
	} else {
		$stmt = $db->prepare(
			'INSERT INTO wp_posts
			(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);
		$stmt->bind_param(
			'isssssssssssssssisiss',
			$post_author,
			$post_date,
			$post_date_gmt,
			$event['content'],
			$event['title'],
			$event['excerpt'],
			$post_status,
			$comment_status,
			$ping_status,
			$post_password,
			$slug,
			$to_ping,
			$pinged,
			$post_modified,
			$post_modified_gmt,
			$post_content_filtered,
			$post_parent,
			$guid,
			$menu_order,
			$post_type,
			$post_mime_type
		);
		$stmt->execute();
		$post_id = (int) $db->insert_id;
	}

	$meta = array(
		'event_date'          => $event['date'],
		'event_time'          => $event['time'],
		'event_timezone'      => 'ET',
		'event_location'      => $event['location'],
		'event_cta_url'       => $event['status'] === 'upcoming' ? 'https://example.com/register/' . $slug : '',
		'event_cta_label'     => $event['status'] === 'upcoming' ? 'Register' : '',
		'event_recording_url' => $event['status'] === 'past' ? 'https://example.com/recordings/' . $slug : '',
		'event_duration'      => $event['status'] === 'past' ? $event['duration'] : '',
	);

	foreach ( $meta as $key => $value ) {
		$stmt = $db->prepare( 'DELETE FROM wp_postmeta WHERE post_id = ? AND meta_key = ?' );
		$stmt->bind_param( 'is', $post_id, $key );
		$stmt->execute();

		$stmt = $db->prepare( 'INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)' );
		$stmt->bind_param( 'iss', $post_id, $key, $value );
		$stmt->execute();
	}

	$type_tt_id  = cvipi_seed_get_term_taxonomy_id( $db, $event['type'], 'event_type' );
	$topic_tt_id = cvipi_seed_get_term_taxonomy_id( $db, $event['topic'], 'event_topic' );

	$stmt = $db->prepare(
		'DELETE tr FROM wp_term_relationships tr
		JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		WHERE tr.object_id = ? AND tt.taxonomy IN ("event_type", "event_topic")'
	);
	$stmt->bind_param( 'i', $post_id );
	$stmt->execute();

	foreach ( array( $type_tt_id, $topic_tt_id ) as $term_taxonomy_id ) {
		$stmt = $db->prepare( 'INSERT IGNORE INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES (?, ?, 0)' );
		$stmt->bind_param( 'ii', $post_id, $term_taxonomy_id );
		$stmt->execute();
	}

	return $post_id;
}

function current_time_string() {
	return date( 'Y-m-d H:i:s' );
}

$events = array(
	array( 'title' => 'CVI Implementation Office Hours', 'date' => '20260709', 'time' => '13:00', 'type' => 'Webinars', 'topic' => 'Violence Intervention', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Youth Program Design Lab', 'date' => '20260716', 'time' => '14:00', 'type' => 'Peer Learning', 'topic' => 'Youth Programs', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Data Capacity for CVI Teams', 'date' => '20260723', 'time' => '12:30', 'type' => 'Webinars', 'topic' => 'Data & Evaluation', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Hospital Partnership Roundtable', 'date' => '20260806', 'time' => '15:00', 'type' => 'Panels', 'topic' => 'Hospital-Based CVI', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Policy and Advocacy Briefing', 'date' => '20260820', 'time' => '11:00', 'type' => 'Webinars', 'topic' => 'Policy & Advocacy', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Street Outreach Practice Exchange', 'date' => '20260903', 'time' => '13:30', 'type' => 'Peer Learning', 'topic' => 'Street Outreach', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Community Impact Measurement Session', 'date' => '20260917', 'time' => '14:00', 'type' => 'Panels', 'topic' => 'Impact', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Annual CVIPI Learning Convening', 'date' => '20261008', 'time' => '10:00', 'type' => 'Conferences', 'topic' => 'Program Design', 'location' => 'Washington, DC', 'status' => 'upcoming' ),
	array( 'title' => 'Focused Deterrence Strategy Clinic', 'date' => '20261112', 'time' => '13:00', 'type' => 'Peer Learning', 'topic' => 'Focused Deterrence', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Community Safety Communications Workshop', 'date' => '20261203', 'time' => '12:00', 'type' => 'Webinars', 'topic' => 'Communications', 'location' => 'Zoom', 'status' => 'upcoming' ),
	array( 'title' => 'Archived Webinar: Building Outreach Teams', 'date' => '20260514', 'time' => '13:00', 'type' => 'Webinars', 'topic' => 'Street Outreach', 'location' => 'Recording', 'status' => 'past', 'duration' => '54 min' ),
	array( 'title' => 'Archived Panel: Community-Led Safety', 'date' => '20260422', 'time' => '14:00', 'type' => 'Panels', 'topic' => 'Violence Intervention', 'location' => 'Recording', 'status' => 'past', 'duration' => '47 min' ),
	array( 'title' => 'Archived Training: Evaluation Basics', 'date' => '20260318', 'time' => '12:00', 'type' => 'Webinars', 'topic' => 'Data & Evaluation', 'location' => 'Recording', 'status' => 'past', 'duration' => '61 min' ),
	array( 'title' => 'Archived Clinic: Referral Pathways', 'date' => '20260227', 'time' => '15:00', 'type' => 'Peer Learning', 'topic' => 'Hospital-Based CVI', 'location' => 'Recording', 'status' => 'past', 'duration' => '43 min' ),
	array( 'title' => 'Archived Briefing: Funding Readiness', 'date' => '20260129', 'time' => '11:30', 'type' => 'Webinars', 'topic' => 'Policy & Advocacy', 'location' => 'Recording', 'status' => 'past', 'duration' => '39 min' ),
	array( 'title' => 'Archived Convening: Youth Violence Prevention', 'date' => '20251211', 'time' => '10:00', 'type' => 'Conferences', 'topic' => 'Youth Programs', 'location' => 'Recording', 'status' => 'past', 'duration' => '74 min' ),
	array( 'title' => 'Archived Exchange: Violence Interruption', 'date' => '20251113', 'time' => '13:00', 'type' => 'Peer Learning', 'topic' => 'Violence Interruption', 'location' => 'Recording', 'status' => 'past', 'duration' => '45 min' ),
	array( 'title' => 'Archived Workshop: Program Design Tools', 'date' => '20251009', 'time' => '14:30', 'type' => 'Webinars', 'topic' => 'Program Design', 'location' => 'Recording', 'status' => 'past', 'duration' => '52 min' ),
	array( 'title' => 'Archived Roundtable: Impact Storytelling', 'date' => '20250918', 'time' => '12:30', 'type' => 'Panels', 'topic' => 'Impact', 'location' => 'Recording', 'status' => 'past', 'duration' => '48 min' ),
	array( 'title' => 'Archived Session: Community Partnerships', 'date' => '20250821', 'time' => '13:30', 'type' => 'Peer Learning', 'topic' => 'Community Partnerships', 'location' => 'Recording', 'status' => 'past', 'duration' => '57 min' ),
);

foreach ( $events as $event ) {
	$event['duration']  = isset( $event['duration'] ) ? $event['duration'] : '';
	$event['post_date'] = substr( $event['date'], 0, 4 ) . '-' . substr( $event['date'], 4, 2 ) . '-' . substr( $event['date'], 6, 2 ) . ' ' . $event['time'] . ':00';
	$event['excerpt']   = 'A practical CVIPI event for practitioners focused on ' . strtolower( $event['topic'] ) . '.';
	$event['content']   = 'This sample event supports testing of the Events archive filters, AJAX updates, upcoming and archived states, and accessible card links.';

	$post_id = cvipi_seed_event( $db, $event );
	echo $post_id . "\t" . $event['date'] . "\t" . $event['type'] . "\t" . $event['topic'] . "\t" . $event['title'] . "\n";
}

$db->query(
	'UPDATE wp_term_taxonomy tt
	SET count = (
		SELECT COUNT(*)
		FROM wp_term_relationships tr
		JOIN wp_posts p ON p.ID = tr.object_id
		WHERE tr.term_taxonomy_id = tt.term_taxonomy_id
		AND p.post_status = "publish"
	)
	WHERE tt.taxonomy IN ("event_type", "event_topic")'
);

echo 'Seeded ' . count( $events ) . " events.\n";
