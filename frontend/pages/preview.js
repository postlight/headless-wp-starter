import React, { Component } from 'react';
import fetch from 'isomorphic-unfetch';
import Error from 'next/error';
import Layout from '../components/Layout';
import PageWrapper from '../components/PageWrapper';
import Menu from '../components/Menu';
import Config from '../config';

class Preview extends Component {
  constructor() {
    super();
    this.state = {
      post: null,
    };
  }

  componentDidMount() {
    const { url } = this.props;
    const { id, wpnonce } = url.query;
    fetch(
      `${Config.apiUrl}/wp/v2/posts/${id}?_wpnonce=${wpnonce}`,
      { credentials: 'include' }, // required for cookie nonce auth
    )
      .then(res => res.json())
      .then(res => {
        this.setState({
          post: res,
        });
      });
  }

  render() {
    const { headerMenu } = this.props;
    const { post } = this.state;
    if (post && post.code && post.code === 'rest_cookie_invalid_nonce') {
      return <Error statusCode={404} />;
    }

    return (
      <Layout>
        <Menu menu={headerMenu} />
        <h1>{post ? post.title.rendered : ''}</h1>
        <div
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: post ? post.content.rendered : '',
          }}
        />
      </Layout>
    );
  }
}

export default PageWrapper(Preview);
