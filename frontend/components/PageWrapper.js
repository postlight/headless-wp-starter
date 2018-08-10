import React, { PureComponent } from "react";
import { Config } from "../config.js";

const PageWrapper = Comp => (
  class extends PureComponent {
    static async getInitialProps(args) {
      const [wrappedInitialProps, headerMenuRes] = await Promise.all([
        Comp.getInitialProps ? Comp.getInitialProps(args) : null,
        fetch(
        `${Config.apiUrl}/wp-json/menus/v1/menus/header-menu`
        ),
      ]);

      const headerMenu = await headerMenuRes.json();
      return {
        headerMenu,
        ...wrappedInitialProps,
      };
    }

    render() {
      return (
        <Comp {...this.props} />
      )
    }
  }
)

export default PageWrapper;
