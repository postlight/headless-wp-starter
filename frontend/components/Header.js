import React from 'react';    
import Head from 'next/head';
import tailwindcss from '../src/styles/styles.css'
import parse from 'html-react-parser'

const Header = (props) => {

  if ( props.single !== "true" ){
    var yoastTitle = parse( props.seo_details.title.rendered );
    var yoastHead = parse( props.seo_details.yoast_head );

  } else {

    var yoastTitle = 'Replace wtih Regex';
    var yoastHead = '';

  }
  
  return (
  
    <Head>

      <style
        // eslint-disable-next-line react/no-danger
        dangerouslySetInnerHTML={{ __html: tailwindcss }}
      />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <meta charSet="utf-8" />
      <title>{yoastTitle}</title>
      
      {yoastHead}

    </Head>

  );

};

export default Header;
