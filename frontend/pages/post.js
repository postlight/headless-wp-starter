import Error from 'next/error';
import React, { Component } from 'react';
import WPAPI from 'wpapi';
import Article from '../components/Article';
import Layout from '../components/Layout';
import Menu from '../components/Menu';
import NavBar from '../components/NavBar';
import RecentPosts from '../components/RecentPosts';
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
      rencentPosts: [],
    };
  }
  componentDidMount() {
    wp.posts()
      .embed()
      .then(posts => {
        this.setState({ rencentPosts: posts.slice(0, 9) });
      });
  }
  render() {
    const { post, headerMenu } = this.props;
    if (!post.title) return <Error statusCode={404} />;

    return (
      <Layout>
        {/* <Menu menu={headerMenu} /> */}
        <NavBar />
        <Article post={post} />
        <section className="floatingSide">
          {this.state.rencentPosts.length > 0 ? (
            <RecentPosts posts={this.state.rencentPosts} />
          ) : null}
        </section>
        <style jsx>
          {`
            .floatingSide {
              position: fixed;
              right: 30px;
              top: 25%;
              max-width: 20%;
            }
          `}
        </style>
      </Layout>
    );
  }
}

export default PageWrapper(Post);
