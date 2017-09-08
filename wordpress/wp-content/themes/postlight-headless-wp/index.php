<?php
// Redirect individual post to the REST API endpoint
if ( is_single() ) {
	header( 'Location: /wp-json/wp/v2/posts/' . get_post()->ID );
} else {
	header( 'Location: /wp-json/' );
}
