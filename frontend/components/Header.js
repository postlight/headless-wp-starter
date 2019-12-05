import React from 'react';
import Head from 'next/head';
import tachyons from 'tachyons/css/tachyons.min.css';
import stylesheet from '../src/styles/style.scss';

const Header = () => (
  <Head>
    <style
      // eslint-disable-next-line react/no-danger
      dangerouslySetInnerHTML={{ __html: tachyons }}
    />
    <style
      // eslint-disable-next-line react/no-danger
      dangerouslySetInnerHTML={{ __html: stylesheet }}
    />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charSet="utf-8" />
    <title>WordPress + React Starter Kit Frontend by Postlight</title>
  </Head>
);

export default Header;
