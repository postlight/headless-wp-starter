import React from 'react';    
import Head from 'next/head';
import tailwindcss from '../src/styles/styles.css'
import parse from 'html-react-parser'
import useRouter from 'next/router';
import { reverseSlugName, getSafe } from "../utils/utils.js";

const Header = (props) => {

  if ( typeof props.yoast.post !== "undefined" ) {

    var yoastTitle = parse( props.yoast.post.title.rendered );
    var yoastHead = parse( props.yoast.post.yoast_head );

  } else {

    var yoastTitle = reverseSlugName( props.yoast.slug );
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
