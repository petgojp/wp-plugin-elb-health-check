<?php

require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-config.php' );

$processes = get_option( 'elb-health-check-processes' );
foreach ( $processes as $process ) {

	$output = shell_exec( "ps aux | grep $process | grep -v grep" );
	if ( empty( $output ) ) {
		header( 'Status: 500 Internal Server Error' );
		return;
	}
}
echo 'OK';

?>
