import Head from 'next/head';
import React from 'react';
import stylesheet from '../src/styles/style.scss';

const Header = () => (
  <div>
    <Head>
      {/* For SEO */}
      <meta
        name="description"
        content="JapanInsider是提供日本群眾募資、線上電商營運、線下通路開發的專業顧問團隊！"
      />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <meta name="keywords" content="日本,群眾募資,新創,跨境電商" />
      <meta property="og:type" content="website" />
      <meta
        property="og:title"
        content="Japan Insider 日本群眾募資與跨境電商的專業顧問團隊"
      />
      <meta
        name="og:description"
        content="JapanInsider是提供日本群眾募資、線上電商營運、線下通路開發的專業顧問團隊！"
      />
      <meta property="og:site_name" content="JapanInsider" />
      <meta property="og:locale" content="zh_TW" />
      <meta
        property="og:image"
        content="https://drive.google.com/uc?id=1_NDNP6OlTG_XX9dr5c-vyShd3HaBYt15"
      />
      {/* For SEO */}

      <title>Japan Insider</title>
      <style
        // eslint-disable-next-line react/no-danger
        dangerouslySetInnerHTML={{ __html: stylesheet }}
      />
    </Head>
  </div>
);

export default Header;
