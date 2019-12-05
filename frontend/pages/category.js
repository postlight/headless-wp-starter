/* eslint-disable jsx-a11y/anchor-is-valid */
import React, { Component } from 'react';
import Link from 'next/link';
import Error from 'next/error';
import WPAPI from 'wpapi';
import Layout from '../components/Layout';
import PageWrapper from '../components/PageWrapper';
import Menu from '../components/Menu';
import Config from '../config';

const wp = new WPAPI({ endpoint: Config.apiUrl });

class Category extends Component {
  static async getInitialProps(context) {
    const { slug } = context.query;

    const categories = await wp
      .categories()
      .slug(slug)
      .embed();

    if (categories.length > 0) {
      const posts = await wp
        .posts()
        .category(categories[0].id)
        .embed();
      return { categories, posts };
    }

    return { categories };
  }

  render() {
    const { categories, posts, headerMenu } = this.props;
    if (categories.length === 0) return <Error statusCode={404} />;

    const fposts = posts.map(post => {
      return (
        <div key={post.id}>
          <h2 className="mt5">
            <Link
              as={`/post/${post.slug}`}
              href={`/post?slug=${post.slug}&apiRoute=post`}
            >
              <a>{post.title.rendered}</a>
            </Link>
          </h2>
          <div
            className="mv4"
            // eslint-disable-next-line react/no-danger
            dangerouslySetInnerHTML={{
              __html: post.excerpt.rendered,
            }}
          />
          <Link
            as={`/post/${post.slug}`}
            href={`/post?slug=${post.slug}&apiRoute=post`}
          >
            <span className="round-btn pointer invert ba bw1 pv2 ph3">
              Read more
            </span>
          </Link>
        </div>
      );
    });
    return (
      <Layout>
        <Menu menu={headerMenu} />
        <div className="content mh4 mt4 mb6 w-two-thirds-l center-l">
          <span className="gray f3 b">Category Archives:</span>
          <h1 className="f1 mt3">{categories[0].name}</h1>
          {fposts}
        </div>
      </Layout>
    );
  }
}

export default PageWrapper(Category);
