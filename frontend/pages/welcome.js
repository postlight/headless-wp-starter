import Layout from "../components/Layout.js";
import React, { Component } from "react";
import Link from "next/link";
import fetch from "isomorphic-unfetch";
import Error from "next/error";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";
import { createLink } from "../src/util.js";

class Index extends Component {
  static async getInitialProps(context) {
    const { slug, apiRoute } = context.query;
    const res = await fetch(
      `${Config.apiUrl}/wp-json/postlight/v1/page?slug=welcome`
    );
    const post = await res.json();
    const childrenRes = await fetch(
      `${Config.apiUrl}/wp-json/wp/v2/pages?parent=${post.id}`
    );
    const children = await childrenRes.json();
    return { post, children };
  }

  render() {
    let { post, children } = this.props
    if (!post.title) return <Error statusCode={404} />;

    return (
      <Layout>
        <Menu menu={this.props.headerMenu} />
        <div className="container" id="main">
          <div>(note, this template is pages/index.js, all the other pages are pages/page.js)</div>
          <div
            dangerouslySetInnerHTML={{
              __html: this.props.post.content.rendered
            }}
          />
        </div>
      </Layout>
    );
  }
}

export default PageWrapper(Index);
