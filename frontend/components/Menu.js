import React, { Component } from "react";
import classNames from "classnames";
import { withRouter } from 'next/router'
import Link from "next/link";
import { Config } from "../config.js";
import { createLink } from "../src/util.js";

class Menu extends Component {
  constructor() {
    super();
  }

  isActive(link) {
    const currentPath = this.props.router.asPath
    return currentPath.indexOf(link.props.as) > -1
  }

  render() {
    const menuItems = this.props.menu.items

    return ( 
      <nav className="navbar navbar-expand-lg navbar-light light container-fluid">
        <Link href="/welcome/?slug=welcome&apiRoute=welcome" as="/">
          <a className="navbar-brand">Meredith Monk</a>
        </Link>
        <button className="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
          <span className="navbar-toggler-icon"></span>
        </button>
        <div className="collapse navbar-collapse" id="navbarToggler">
          <ul className="navbar-nav ml-auto mt-2">
            { menuItems.map(createLink).map((link, i) =>
              <li className={classNames("nav-item", {['active']: this.isActive(link)})} key={i}>
                { link }
              </li>
            )}
          </ul>
        </div>
      </nav>
    )
  }
}

export default withRouter(Menu);
