import React, { Component } from "react";
import PageWrapper from "../components/PageWrapper.js";
import Layout from "../components/Layout.js";
import Menu from "../components/Menu.js";
import Link from "next/link";
import { Config } from "../config.js";
import safeGet from "lodash/get";
import fetch from "isomorphic-unfetch";

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
        <div className="container-fluid" id="main">
          <div className="row">
            <div className="col">
              <img className="work-featured-image" src={safeGet(post, "['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['full']['source_url']")} alt={post.title.rendered} />
            </div>
          </div>
          <div className="row">
            <div className="col" id="content" className="work-description">
              <div dangerouslySetInnerHTML={{
                  __html: post.content.rendered
                }}>
              </div>
              <div className="see-more">
                <span className="arrow">←</span>
                <Link
                  as="/current-repertory"
                  href="/page?slug=current-repertory&apiRoute=page">
                    <a>See more work</a>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </Layout>
    )
  }
}

export default PageWrapper(Work);
