import React, { Component } from 'react';
import Error from 'next/error';
import Menu from '../components/Menu';
import WPAPI from 'wpapi';
import Layout from '../components/Layout';
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
    if (!post.title) {
      return <Error statusCode={404} />;
    }

    const heroUrl = (
      post._embedded &&
      post._embedded['wp:featuredmedia'] &&
      post._embedded['wp:featuredmedia'][0] &&
      post._embedded['wp:featuredmedia'][0].source_url
    ) ? post._embedded['wp:featuredmedia'][0].source_url : false;

    return (
      <Layout className="test">
        <Menu menu={headerMenu} />
        {heroUrl ? (
          <div className={`hero flex items-center post-type-${post.type}`}>
            <img
              className="w-100"
              src={heroUrl}
            />
          </div>
        ) : ''}
        <div className={`content mh4 mv4 w-two-thirds-l center-l post-${post.id} post-type-${post.type}`}>
          <h1>{post.title.rendered}</h1>
          <div
            // eslint-disable-next-line react/no-danger
            dangerouslySetInnerHTML={{
              __html: post.content.rendered,
            }}
          />
        </div>
      </Layout>
    );
  }
}

export default PageWrapper(Post);
