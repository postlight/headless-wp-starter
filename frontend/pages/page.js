import Layout from "../components/Layout.js";
import React, { Component } from "react";
import Link from "next/link";
import fetch from "isomorphic-unfetch";
import Error from "next/error";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";
import { createLink } from "../src/util.js";
import CalendarEvents from "../components/CalendarEvents.js";
import sortBy from 'lodash/sortBy';

class Post extends Component {
  static async getInitialProps(context) {
    const { slug, apiRoute } = context.query;
    const res = await fetch(
      `${Config.apiUrl}/wp-json/postlight/v1/${apiRoute}?slug=${slug}`
    );
    const post = await res.json();

    const ancestorSlug = context.asPath.split('/')[1]
    const ancestorRes = await fetch(
      `${Config.apiUrl}/wp-json/postlight/v1/${apiRoute}?slug=${ancestorSlug}`
    );
    const ancestor = await ancestorRes.json();

    const menuItemRes = await fetch(
      `${Config.apiUrl}/wp-json/wp/v2/pages?parent=${ancestor.id}`
    );
    const menuItems = await menuItemRes.json();
    return { post, menuItems };
  }

  render() {
    const {
      post,
      post: { acf },
      menuItems
    } = this.props

    if (!post.title) return <Error statusCode={404} />;

    return (
      <Layout>
        <Menu menu={this.props.headerMenu} />
        <div className="container-fluid" id="main">
          <div className="row">
            { !!menuItems.length &&
              <div className="col-3" id="subnav">              
                <ul id="sub-nav">
                  { sortBy(menuItems, 'menu_order')
                    .map(createLink)
                    .map((child, i) =>
                      <li key={i}>{child}</li>
                  )}
                </ul>
              </div>
            }
            <div className="col" id="content">
              <div dangerouslySetInnerHTML={{
                  __html: post.content.rendered
                }}>
              </div>
              {/* calendar events */}
              { acf && acf.events && <CalendarEvents events={acf.events} /> }
            </div>
          </div>
        </div>


      </Layout>
    );
  }
}

export default PageWrapper(Post);
