import React, { Component } from "react";
import Link from "next/link";
import { Config } from "../config.js";

const linkStyle = {
    marginRight: 15
};

class Menu extends Component {
  constructor() {
      super();
      this.state = {
          menu: []
      };
  }

  getSlug(url) {
      const parts = url.split("/");
      return parts.length > 2 ? parts[parts.length - 2] : "";
  }

  componentDidMount() {
      const menuItemsURL = `${
          Config.apiUrl
      }/wp-json/menus/v1/menus/header-menu`;
      fetch(menuItemsURL)
          .then(res => res.json())
          .then(res => {
              this.setState({
                  menu: res.items
              });
          });
  }


  render() {
      const menuItems = this.state.menu.map((item, index) => {
        if (item.object === "custom") {
            return (
                <Link href={item.url} key={item.ID}>
                    <a style={linkStyle}>{item.title}</a>
                </Link>
            );
        }
        const slug = this.getSlug(item.url);
        const actualPage = item.object === "category" ? "category" : "post";
        return (
            <Link
                as={`/${item.object}/${slug}`}
                href={`/${actualPage}?slug=${slug}&apiRoute=${item.object}`}
                key={item.ID}
            >
                <a style={linkStyle}>{item.title}</a>
            </Link>
        );
    });


    return(
      <div>
          <Link href="/">
              <a style={linkStyle}>Home</a>
          </Link>
          {menuItems}
      </div>
    )
  }


}

export default Menu;
