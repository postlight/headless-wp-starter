let wpUrl = 'http://localhost:8080/wp-json';

// If we're running on Docker, use the WordPress container hostname instead of localhost.
if (process.env.HOME === '/home/node') {
  wpUrl = 'http://wp-headless:8080/wp-json';
}
const Config = {
  apiUrl: wpUrl,
  AUTH_TOKEN: 'auth-token',
  SPORTS_DATA_URL: 'https://delivery.chalk247.com',
  PROXIED_API_BASE: 'http://localhost:3000/',
  API_TOKEN: '74db8efa2a6db279393b433d97c2bc843f8e32b0',
  USERNAME: 'username',
};

export default Config;
