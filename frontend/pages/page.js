import Layout from "../components/Layout.js";
import React, { Component } from "react";
import fetch from "isomorphic-unfetch";
import Error from "next/error";
import { withRouter } from 'next/router'
import classNames from "classnames";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";
import { createLink } from "../src/util.js";
import CalendarEvents from "../components/CalendarEvents.js";
import Shop from "../components/Shop.js";
import RepertoryWorks from "../components/RepertoryWorks.js";
import sortBy from 'lodash/sortBy';

class Page extends Component {
  static async getInitialProps(context) {
    const { slug, apiRoute } = context.query;

    const pageRes = await fetch(
      `${Config.apiUrl}/wp-json/postlight/v1/${apiRoute}?slug=${slug}`
    );

    const ancestorSlug = context.asPath.split('/')[1]
    const ancestorRes = await fetch(
      `${Config.apiUrl}/wp-json/postlight/v1/${apiRoute}?slug=${ancestorSlug}`
    );
    const ancestor = await ancestorRes.json();

    const menuItemRes = await fetch(
      `${Config.apiUrl}/wp-json/wp/v2/pages?parent=${ancestor.id}`
    );

    const menuItems = await menuItemRes.json();
    const page = await pageRes.json();

    return { page, menuItems };
  }

  isSectionActive(slug) {
    const currentPath = this.props.router.asPath
    return currentPath.indexOf(slug) === 0
  }

  isPageActive(child) {
    const currentPath = this.props.router.asPath
    return currentPath === child.props.as
  }

  render() {
    const {
      page,
      page: { acf },
      menuItems,
      headerMenu,
      repertoryWorks
    } = this.props

    if (!page.title) return <Error statusCode={404} />;

    return (
      <Layout>
        <Menu menu={headerMenu} />
        <div className="container-fluid" id="main">
          <div className="row">
            { !!menuItems.length &&
              <div className="col-md-3" id="subnav">              
                <ul id="sub-nav">
                  { sortBy(menuItems, 'menu_order')
                    .map(createLink)
                    .map((child, i) =>
                      <li className={classNames({['active']: this.isPageActive(child)})} key={i}>
                        {child}
                      </li>
                  )}
                </ul>
              </div>
            }
            <div className="col" id="content">
              {/* <h1>{page.title.rendered}</h1> */}
              <div dangerouslySetInnerHTML={{
                  __html: page.content.rendered
                }}>
              </div>

              {/* calendar events */}
              { acf && acf.events && <CalendarEvents events={acf.events} /> }

              {/* shop */}
              { acf && acf.product_categories && <Shop categories={acf.product_categories} /> }

              {/* repertory works */}
              { this.isSectionActive('/current-repertory') && <RepertoryWorks repertoryWorks={repertoryWorks} />}
            </div>
          </div>
        </div>


      </Layout>
    );
  }
}

export default withRouter(PageWrapper(Page));
