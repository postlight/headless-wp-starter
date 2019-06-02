import Error from 'next/error';
import React, { Component } from 'react';
import WPAPI from 'wpapi';
import Article from '../components/Article';
import Layout from '../components/Layout';
import Menu from '../components/Menu';
import NavBar from '../components/NavBar';
import PageWrapper from '../components/PageWrapper';
import Config from '../config';

const wp = new WPAPI({ endpoint: Config.apiUrl });

class Post extends Component {
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

    const post = await apiMethod
      .slug(slug)
      .embed()
      .then(data => {
        return data[0];
      });

    return { post };
  }

  render() {
    const { post, headerMenu } = this.props;
    if (!post.title) return <Error statusCode={404} />;

    return (
      <Layout>
        {/* <Menu menu={headerMenu} /> */}
        <NavBar />
        <Article post={post} />
      </Layout>
    );
  }
}

export default PageWrapper(Post);
