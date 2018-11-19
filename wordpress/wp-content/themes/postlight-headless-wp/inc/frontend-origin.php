<?php

/**
 * @return str Frontend origin URL, i.e., http://localhost:3000.
 */
function get_frontend_origin() {
    return ($_ENV['FRONTEND_URL'] ? $_ENV['FRONTEND_URL'] : 'http://localhost:3000');
}
