import React, { Component } from "react";
import Link from "next/link";
import { Config } from "../config.js";
import { createLink } from "../src/util.js";

class Menu extends Component {
  constructor() {
    super();
  }

  render() {
    const menuItems = this.props.menu.items.map(createLink);

    return( 
      <nav>
        <div className="nav-brand">
          <Link href="/"><a>Meredith Monk</a></Link>
        </div>
        <div className="nav-links">
          {menuItems}
        </div>
      </nav>
    )
  }


}

export default Menu;
