import React, { Component } from 'react';
import Link from 'next/link';
import Router from 'next/router';
import WPAPI from 'wpapi';
import Layout from '../components/Layout';
import PageWrapper from '../components/PageWrapper';
import Menu from '../components/Menu';
import Config from '../config';
import Logo from '../static/images/starter-kit-logo.svg';

const wp = new WPAPI({ endpoint: Config.apiUrl });

const headerImageStyle = {
  marginTop: 50,
  marginBottom: 50,
};

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
    const { posts, pages, headerMenu, page } = this.props;
    const fposts = posts.map(post => {
      return (
        <ul key={post.slug}>
          <li>
            <Link
              as={`/post/${post.slug}`}
              href={`/post?slug=${post.slug}&apiRoute=post`}
            >
              <a>{post.title.rendered}</a>
            </Link>
          </li>
        </ul>
      );
    });
    const fpages = pages.map(ipage => {
      if (ipage.slug !== 'welcome') {
        return (
          <ul key={ipage.slug}>
            <li>
              <Link
                as={`/page/${ipage.slug}`}
                href={`/post?slug=${ipage.slug}&apiRoute=page`}
              >
                <a>{ipage.title.rendered}</a>
              </Link>
            </li>
          </ul>
        );
      }
    });

    return (
      <Layout>
        <Menu menu={headerMenu} />
        <div className="intro bg-black white ph3 pv4 ph5-m pv5-l flex flex-column flex-row-l">
          <div className="color-logo w-50-l mr3-l">
            <Logo width={440} height={280} />
          </div>
          <div className="subhed pr6-l">
            <h1>{page.title.rendered}</h1>
            <div className="dek">
              You are now running a WordPress backend with a React frontend.
            </div>
            <div className="api-info b mt4">
              Starter Kit supports both REST API and GraphQL
              <div className="api-toggle">
                <a className="rest" href="http://localhost:3000">REST API</a>
                <a className="graphql" href="http://localhost:3001">GraphQL</a>
              </div>
            </div>
          </div>
        </div>
        <div className="recent flex mh4 mv4 w-two-thirds-l center-l">
          <div className="w-50 pr3">
            <h2>Posts</h2>
            {fposts}
          </div>
          <div className="w-50 pl3">
            <h2>Pages</h2>
            {fpages}
          </div>
        </div>
        <div className="content mh4 mv4 w-two-thirds-l center-l home"
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: page.content.rendered,
          }}
        />
      </Layout>
    );
  }
}

export default PageWrapper(Index);
