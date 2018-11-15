<?php
header( sprintf( 'Location: /wp-json/wp/v2/%s/%s', get_post_type_object( get_post_type() )->rest_base, get_post()->ID ) );