import React, { Component } from 'react';
import Link from 'next/link';
import Router from 'next/router';
import WPAPI from 'wpapi';
import Layout from '../components/Layout';
import PageWrapper from '../components/PageWrapper';
import Menu from '../components/Menu';
import Config from '../config';
import SportsData, { data } from '../components/SportsData';
import fetch from 'isomorphic-unfetch';
import useSWR from 'swr'

const wp = new WPAPI({ endpoint: Config.apiUrl });

const tokenExpired = () => {
  if (process.browser) {
    localStorage.removeItem(Config.AUTH_TOKEN);
  }
  wp.setHeaders('Authorization', '');
  Router.push('/login');
};



class Index extends Component {
  state = {
    id: '',
  };
  
  static async getInitialProps() {
    try {
      
      const [posts, pages, home] = await Promise.all([
        
        wp.posts().embed(),
        wp.pages().embed(),
        wp
          .pages()
          .slug('acme-sports-present-nfl-teams')
          .embed()
          .then(data => {
            return data[0];
          })
          

      ]);

      return { posts, pages, home };
    } catch (err) {
      if (err.data.status === 403) {
        tokenExpired();
      }
    }

    return null;

  }

  componentDidMount() {
    const token = localStorage.getItem(Config.AUTH_TOKEN);
    if (token) {
      wp.setHeaders('Authorization', `Bearer ${token}`);
      wp.users()
        .me()
        .then(data => {
          const { id } = data;
          this.setState({ id });
        })
        .catch(err => {
          if (err.data.status === 403) {
            tokenExpired();
          }
        });
    }
    
  }

  render() {
    const { id } = this.state; 
    const { headerMenu, home } = this.props;
    
    return (
      <Layout post={home}>

        <Menu menu={headerMenu} />
    
        <div className="container container max-w-screen-xl m-auto flex flex-wrap flex-col items-center justify-start">
                
          {/* <AnimatePresence initial={false} exitBeforeEnter>  */}
          
          <SportsData />
          
          {/* </AnimatePresence> */}

        </div>
          
      </Layout>
    );
  }
}

export default PageWrapper(Index);
