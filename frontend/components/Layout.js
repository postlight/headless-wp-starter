import React from 'react';
import Header from './Header';
import Menu from './Menu';
import Footer from './Footer';

const Layout = props => {
  const { children } = props;
  return (
    <div>
      <Header />
      <main>
        {children}
      </main>
      <Footer />
    </div>
  );
};

export default Layout;
