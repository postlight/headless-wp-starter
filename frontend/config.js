let wpUrl = `${process.env.WORDPRESS_URL}/wp-json`;

// XXX: Workaround for local env.
// If it's in browser, it cannot access docker internal domain
if (process.env.NODE_ENV === 'development') {
  if (typeof window !== 'undefined') {
    wpUrl = 'http://localhost:8080/wp-json';
  } else {
    wpUrl = 'http://wp-headless:8080/wp-json';
  }
}

const Config = {
  apiUrl: wpUrl,
  AUTH_TOKEN: 'auth-token',
  USERNAME: 'username',
  DISQUS_URL:
    process.env.NODE_ENV === 'development'
      ? 'example'
      : 'https://https-www-japaninsider-co-zh.disqus.com/embed.js',
  DISQUS_SHORT_NAME: 'https-www-japaninsider-co-z',
};

export default Config;
