import React, { Component } from "react";
import Link from "next/link";
import { Config } from "../config.js";
import { createLink } from "../src/util.js";

class Menu extends Component {
  constructor() {
    super();
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
              <li className="nav-item" key={i}>
                { link }
              </li>
            )}
          </ul>
        </div>
      </nav>
    )
  }


}

export default Menu;
