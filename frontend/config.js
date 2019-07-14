const wpUrl = `${process.env.WORDPRESS_URL}/wp-json`;
const Config = {
  apiUrl: wpUrl,
  AUTH_TOKEN: 'auth-token',
  USERNAME: 'username',
};

export default Config;
