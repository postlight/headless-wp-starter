import Head from 'next/head';
import React from 'react';
import stylesheet from '../src/styles/style.scss';

const Header = () => (
  <div>
    <Head>
      <title>Japan Insider</title>
      <style
        // eslint-disable-next-line react/no-danger
        dangerouslySetInnerHTML={{ __html: stylesheet }}
      />
    </Head>
  </div>
);

export default Header;
