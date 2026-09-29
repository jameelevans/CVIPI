<?php
define( 'ABSPATH', '/tmp/test-public/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'DAY_IN_SECONDS', 86400 );
class WP_Error {
  public function __construct( public $code, public $message ) {}
  public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
$cache = array(); $options = array();
function get_transient( $key ) { global $cache; return $cache[$key] ?? false; }
function set_transient( $key, $value, $expiry ) { global $cache; $cache[$key] = $value; }
function get_option( $key ) { global $options; return $options[$key] ?? false; }
function add_option( $key, $value, $deprecated, $autoload ) { global $options; if ( isset( $options[$key] ) ) return false; $options[$key] = $value; return true; }
function delete_option( $key ) { global $options; unset( $options[$key] ); }
require __DIR__ . '/../inc/analytics-dashboard/reports.php';
function check( $condition, $message ) { if ( ! $condition ) throw new RuntimeException( $message ); echo "PASS: $message\n"; }
$ranges = cvipi_dashboard_ranges( '28' );
check( '28daysAgo' === $ranges[0]['startDate'] && '29daysAgo' === $ranges[1]['endDate'], 'Adjacent complete periods do not overlap' );
check( 'today' === cvipi_dashboard_ranges( 'today' )[0]['endDate'], 'Today is explicitly partial' );
$queries = cvipi_dashboard_queries( '7' );
check( 9 === count( $queries ), 'All nine reports are defined' );
foreach ( $queries as $query ) {
  $filters = $query['dimensionFilter']['andGroup']['expressions'];
  check( 'cvipi.info' === $filters[0]['filter']['stringFilter']['value'] && '/staging/' === $filters[1]['notExpression']['filter']['stringFilter']['value'], 'Report excludes local and staging' );
}
$pending = cvipi_dashboard_queries( '7', array() );
check( ! isset( $pending['forms'] ) && 1 === count( $pending['links']['dimensions'] ), 'Pending custom dimensions do not break standard reports' );
$rows = cvipi_dashboard_rows( array( 'dimensionHeaders' => array( array( 'name' => 'dateRange' ) ), 'metricHeaders' => array( array( 'name' => 'sessions' ) ), 'rows' => array( array( 'dimensionValues' => array( array( 'value' => 'previous' ) ), 'metricValues' => array( array( 'value' => '12' ) ) ) ) ) );
check( 'previous' === $rows[0]['dateRange'] && 12.0 === $rows[0]['sessions'], 'Comparison periods and numeric values retain their meaning' );
$key = 'cvipi_ga_dashboard_v1_555256472_7';
$cache[$key] = array( 'fetchedAt' => gmdate( 'c' ), 'reports' => array() );
check( $cache[$key] === cvipi_dashboard_data( '7', true ), 'Force refresh is throttled within 60 seconds' );
$cache[$key]['fetchedAt'] = gmdate( 'c', time() - 1000 );
$cache[$key . '_failure'] = 'Temporary outage.';
check( true === cvipi_dashboard_data( '7' )['stale'], 'Cached outage result is explicitly stale' );
unset( $cache[$key] );
check( is_wp_error( cvipi_dashboard_data( '7' ) ), 'Outage without cache is not converted to zero' );
unset( $cache[$key . '_failure'] );
$options[$key . '_lock'] = time();
check( 'busy' === cvipi_dashboard_data( '7' )->code, 'Concurrent refreshes are bounded' );
delete_option( $key . '_lock' );
check( 'connection' === cvipi_dashboard_data( '7' )->code && ! isset( $options[$key . '_lock'] ), 'Missing private credentials fail closed and release lock' );
