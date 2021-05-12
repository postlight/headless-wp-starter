import React from 'react';
import Header from './Header';
import Menu from './Menu';
import Footer from './Footer';

const Layout = props => {
  const { children } = props;
  let seo_home = props;
  // console.log( 'all my children');
  // console.log( props.single )
  
  return (
    <body className="font-sans text-gray-900 antialiased bg-yellow-500">
      <Header seo_details={props.seo} single={props.single} />
      <main>
        {children}
      </main>
      {/* <Footer /> */}
    </body>
  );
};

export default Layout;
