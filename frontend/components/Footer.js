import React from 'react';

const hrStyle = {
  marginTop: 75,
};

const Footer = () => (
  <div>
    <hr style={hrStyle} />
    <p>
      <span role="img" aria-label="love">
        ❤️
      </span>{' '}
      <a href="https://postlight.com">Made by postlight</a>.
      <span role="img" aria-label="fork">
        🍴
      </span>{' '}
      <a href="https://github.com/postlight/headless-wp-starter">
        Fork on GitHub
      </a>
      .
    </p>
    <p>
      <span role="img" aria-label="wave">
        👋
      </span>{' '}
      Need help with your publishing platform?{' '}
      <a href="mailto:hello@postlight.com?subject=Partner+with+Postlight+on+a+headless+CMS+project">
        Say hi.
      </a>
    </p>
  </div>
);

export default Footer;
