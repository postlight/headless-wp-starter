import React, { Component } from 'react';
import Link from 'next/link';
import WPAPI from 'wpapi';
import Layout from '../components/Layout';
import PageWrapper from '../components/PageWrapper';
import Menu from '../components/Menu';
import Config from '../config';

const wp = new WPAPI({ endpoint: Config.apiUrl });

const headerImageStyle = {
  marginTop: 50,
  marginBottom: 50,
};

class Index extends Component {
  static async getInitialProps() {
    const [page, posts, pages] = await Promise.all([
      wp
        .pages()
        .slug('welcome')
        .embed()
        .then(data => {
          return data[0];
        }),
      wp.posts().embed(),
      wp.pages().embed(),
    ]);

    return { page, posts, pages };
  }

  render() {
    const { posts, pages, headerMenu, page } = this.props;
    const fposts = posts.map(post => {
      return (
        <ul key={post.slug}>
          <li>
            <Link
              as={`/post/${post.slug}`}
              href={`/post?slug=${post.slug}&apiRoute=post`}
            >
              {post.title.rendered}
            </Link>
          </li>
        </ul>
      );
    });
    const fpages = pages.map(ipage => {
      return (
        <ul key={ipage.slug}>
          <li>
            <Link
              as={`/page/${ipage.slug}`}
              href={`/post?slug=${ipage.slug}&apiRoute=page`}
            >
              {ipage.title.rendered}
            </Link>
          </li>
        </ul>
      );
    });
    return (
      <Layout>
        <Menu menu={headerMenu} />
        <img
          src="/static/images/wordpress-plus-react-header.png"
          width="815"
          alt="logo"
          style={headerImageStyle}
        />
        <h1>{page.title.rendered}</h1>
        <div
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: page.content.rendered,
          }}
        />
        <p>
          Make sure to check the{' '}
          <a href="http://localhost:3001/">React frontend</a>, built with{' '}
          <a href="https://graphql.org/">GraphQL</a>!
        </p>
        <h2>Posts</h2>
        {fposts}
        <h2>Pages</h2>
        {fpages}
      </Layout>
    );
  }
}

export default PageWrapper(Index);
