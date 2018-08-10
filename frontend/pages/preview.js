import Layout from "../components/Layout.js";
import React, { PureComponent } from "react";
import fetch from "isomorphic-unfetch";
import Error from "next/error";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";

class Preview extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      post: null
    };
  }

  async componentDidMount() {
    const { id, wpnonce } = this.props.url.query;
    const previewRes = await fetch(
      `${
      Config.apiUrl
      }/wp-json/postlight/v1/post/preview?id=${id}&_wpnonce=${wpnonce}`,
      { credentials: "include" } // required for cookie nonce auth
    );

    const post = await previewRes.json();
    this.setState({ post });
  }

  render() {
    if (
      this.state.post &&
      this.state.post.code &&
      this.state.post.code === "rest_cookie_invalid_nonce"
    )
      return <Error statusCode={404} />;

    return (
      <Layout>
        <Menu menu={this.props.headerMenu} />
        <h1>{this.state.post ? this.state.post.title.rendered : ""}</h1>
        <div
          dangerouslySetInnerHTML={{
            __html: this.state.post
              ? this.state.post.content.rendered
              : ""
          }}
        />
      </Layout>
    );
  }
}

export default PageWrapper(Preview);
