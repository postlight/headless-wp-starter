let wpUrl = `${process.env.WORDPRESS_URL}/wp-json`;

// XXX: Workaround for local env.
// If we're running on Docker, use the WordPress container hostname instead of localhost.

if (
  process.env.HOME === '/home/node' &&
  process.env.NODE_ENV !== 'production'
) {
  wpUrl = 'http://wp-headless:8080/wp-json';
}
const Config = {
  apiUrl: wpUrl,
  AUTH_TOKEN: 'auth-token',
  USERNAME: 'username',
};

export default Config;
