import React from "react";
import { Config } from "../config.js";

const PageWrapper = Comp => (
  class extends React.Component {
    constructor(props) {
      super(props)
      this.state = this.state || {}
      this.state.headerMenu = this.props.headerMenu
      this.state.repertoryWorks = this.props.repertoryWorks
    }

    static async getInitialProps(args) {
      if (!!args.req) {
        const headerMenuRes = await fetch(
          `${Config.apiUrl}/wp-json/menus/v1/menus/header-menu`
        );
        const repertoryWorksRes = await fetch(
          `${Config.apiUrl}/wp-json/wp/v2/work?_embed`
        );
        const headerMenu = await headerMenuRes.json();
        const repertoryWorks = await repertoryWorksRes.json();

        return {
          headerMenu,
          repertoryWorks,
          ...(Comp.getInitialProps ? await Comp.getInitialProps(args) : null),
        };
      }
      return {
        ...(Comp.getInitialProps ? await Comp.getInitialProps(args) : null)
      }
    }

    render() {
      return (
        <div id="wrapper">
          <Comp {...{...this.props, ...this.state}} />
        </div>
      )
    }
  }
)

export default PageWrapper;
