/**
 * Basic imports and configs
 */
import React, { Component } from 'react';
import WPAPI from 'wpapi';
import Config from '../../config';
import Router from 'next/router';
import Error from 'next/error';
/**
 * PageWrapper, Menu if needed, inital SportsData, Layout
 */
import Menu from '../../components/Menu';
import Layout from '../../components/Layout';
import PageWrapper from '../../components/PageWrapper';
import SportsData from '../../components/SportsData';

/**
 * Fancy Animations
 */
import { AnimatePresence, motion } from 'framer-motion';

const wp = new WPAPI({ endpoint: Config.apiUrl });

class singlePost extends Component {
  
  static async getInitialProps(context) {
    
    const { slug, apiRoute } = context.query;
    
    let apiMethod = wp.posts();

    switch (apiRoute) {
      case 'category':
        apiMethod = wp.categories();
        break;
      case 'page':
        apiMethod = wp.pages();
        break;
      default:
        break;
    }

    const singlePost = await wp.pages()
      .slug(slug)
      .embed()
      .then(data => {
        return data[0];
      });

    const slugNew = slug;
    return { singlePost, slugNew };
  }

  render() {
    
    const { singlePost, headerMenu, slugNew } = this.props;
    
    return (

      <Layout post={singlePost} slug={slugNew}>

        <Menu menu={headerMenu} />
    
          <SportsData single="true" />
      
        </Layout>
    );
  }
}

export default PageWrapper(singlePost);
