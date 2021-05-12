import React from 'react';
import WPAPI from 'wpapi';
import Config from '../config';
import fetch from 'isomorphic-unfetch'
import useSWR from 'swr'
const API_TOKEN = '74db8efa2a6db279393b433d97c2bc843f8e32b0'

const wp = new WPAPI({ endpoint: Config.apiUrl });

// This route is copied from the plugin: wordpress/wp-content/plugins/wp-rest-api-v2-menus/wp-rest-api-v2-menus.php
wp.menus = wp.registerRoute('menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)');


async function fetcher(path) {
  
  const res = await fetch(path, {
  
    // mode: "no-cors",
    // mode:"cors-with-forced-preflight"
    // 'Content-Type': 'application/json',
  
  })
  const json = await res.json()
  return json

}



const PageWrapper = Comp =>
  class extends React.Component {
    static async getInitialProps(args) {
      const [headerMenu, childProps] = await Promise.all([
        wp.menus().id('header-menu'),
        Comp.getInitialProps ? Comp.getInitialProps(args) : {}
      ]);

      // var slugSplit = location.pathname.split("/");
      
      // const urlSlug = slugSplit[3];

      return {
        headerMenu,
        ...childProps
      };
    }

    render() {

      // var slugSplit = location.pathname.split("/");
      // var urlSlug = slugSplit[3];
      // console.log( urlSlug );
      
      return <Comp {...this.props} />;
    }
  };

export default PageWrapper;
