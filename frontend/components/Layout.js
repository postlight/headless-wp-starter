import React from 'react';
import Header from './Header';
import Menu from './Menu';
import Footer from './Footer';
import Link from 'next/link';

const Layout = props => {
  const { children } = props;

  return (
    <body className="font-sans text-gray-900 antialiased bg-black">
      <Header yoast={props}/>
      <main>
      <div className="grid lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1 gap-4">
      </div>
        {children}
      </main>
      {/* <Footer /> */}
    </body>
  );
};

export default Layout;
