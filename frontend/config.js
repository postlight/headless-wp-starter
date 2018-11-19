export const Config = {
  apiUrl: process.env.NODE_ENV == 'production'
    ? 'https://wordpress-dot-wordpress-test-222422.appspot.com'
    : 'http://localhost:8080'
}
