import Error from 'next/error';
import React, { Component } from 'react';
import WPAPI from 'wpapi';
import Article from '../components/Article';
import Layout from '../components/Layout';
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

  constructor(props) {
    super(props);
    this.state = {
      recentPosts: [],
    };
  }

  componentDidMount() {
    wp.posts()
      .embed()
      .then(posts => {
        this.setState({ recentPosts: posts.slice(0, 9) });
      });
  }

  render() {
    const { post } = this.props;
    const { recentPosts } = this.state;
    if (!post.title) return <Error statusCode={404} />;

    return (
      <Layout>
        <NavBar />
        <Article post={post} recentPosts={recentPosts} />
      </Layout>
    );
  }
}

export default PageWrapper(Post);
