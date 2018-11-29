import React, { Component } from "react";
import PageWrapper from "../components/PageWrapper.js";
import Layout from "../components/Layout.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";
import safeGet from "lodash/get";

class Work extends Component {
  static async getInitialProps(context) {
    const { slug, apiRoute } = context.query;
    const res = await fetch(
      `${Config.apiUrl}/wp-json/postlight/v1/${apiRoute}?slug=${slug}&_embed`
    );
    const post = await res.json();
    return { post };
  }

  render() {
    const { post } = this.props
    return (
      <Layout>
        <Menu menu={this.props.headerMenu} />
        <img src={safeGet(post, "['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['medium']['source_url']")} alt={post.title.rendered} />
        <div className="col" id="content">
          <div dangerouslySetInnerHTML={{
              __html: post.content.rendered
            }}>
          </div>
        </div>
      </Layout>
    )
  }
}

export default PageWrapper(Work);
