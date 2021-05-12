import React, { Component } from 'react';
import Error from 'next/error';
import Menu from '../../../../components/Menu';
import WPAPI from 'wpapi';
import Layout from '../../../../components/Layout';
import PageWrapper from '../../../../components/PageWrapper';
import Config from '../../../../config';
import fetch from 'isomorphic-unfetch';
import useSWR from 'swr'
import TeamCard from '../../../../components/TeamCard';
import SportsData from '../../../../components/SportsData';
import { motion } from 'framer-motion';
import { AnimatePresence } from 'framer-motion';

const wp = new WPAPI({ endpoint: Config.apiUrl });

class Post extends Component {
  static async getInitialProps(context) {
    
    const { slug, apiRoute } = context.query;
    
    let apiMethod = wp.pages();

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

    const post = await apiMethod
      .slug('arizona-cardinals')
      .embed()
      .then(data => {
        return data[0];
      });

    return { post };
  }

  render() {
    const { post, headerMenu } = this.props;
    if (!post.title) {
      return <TeamCard />;
    }

    const heroUrl = (
      post._embedded &&
      post._embedded['wp:featuredmedia'] &&
      post._embedded['wp:featuredmedia'][0] &&
      post._embedded['wp:featuredmedia'][0].source_url
    ) ? post._embedded['wp:featuredmedia'][0].source_url : false;

    return (

      <Layout single="true">

        <Menu menu={headerMenu} />
    
        <div className="container container max-w-screen-xl m-auto flex flex-wrap flex-col md:flex-row items-center justify-start">
                
                 {/* <AnimatePresence initial={false} exitBeforeEnter>  */}
                 
                 <SportsData single="true"/>
                  
                  {/* </AnimatePresence> */}

             </div>
          
      </Layout>
    );
  }
}

export default PageWrapper(Post);
