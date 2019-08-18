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
    const { id, rev, type, wpnonce } = url.query;
    // The REST posts controller handles both posts/#/revisions/# and pages/#/revisions/#
    // but the latter isn't documented.
    fetch(
      `${Config.apiUrl}/wp/v2/${type}s/${id}/revisions/${rev}?_wpnonce=${wpnonce}`,
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
    const { data } = post || {};

    if (data && data.status && data.status >= 400) {
      return <Error statusCode={data.status} />;
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
