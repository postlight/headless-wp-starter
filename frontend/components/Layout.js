import React from 'react';
import Footer from './Footer';
import Header from './Header';

const Layout = props => {
  const { children } = props;
  return (
    <div className="layout">
      <Header />
      {children}
      <Footer />
      <style jsx>{`
        .layout {
          padding-top: 100px;
          margin: auto;
          max-width: 960px;
        }
      `}</style>
    </div>
  );
};
export default Layout;
