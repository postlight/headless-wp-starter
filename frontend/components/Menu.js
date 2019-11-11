/* eslint-disable jsx-a11y/anchor-is-valid */
import React, { Component } from 'react';
import Link from 'next/link';
import Router from 'next/router';
import Config from '../config';
import Logo from '../static/images/starter-kit-logo.svg';

const getSlug = url => {
  const parts = url.split('/');
  return parts.length > 2 ? parts[parts.length - 2] : '';
};

class Menu extends Component {
  state = {
    token: null,
    username: null,
  };

  componentDidMount() {
    const token = localStorage.getItem(Config.AUTH_TOKEN);
    const username = localStorage.getItem(Config.USERNAME);
    this.setState({ token, username });
  }

  render() {
    const { menu } = this.props;
    const { token, username } = this.state;
    const menuItems = menu.items.map(item => {
      if (item.object === 'custom') {
        return (
          <option
            value={item.url}
            key={item.ID}
          >
            {item.title}
          </option>
        );
      }
      const slug = getSlug(item.url);
      const actualPage = item.object === 'category' ? 'category' : 'post';
      return (
        <option
          value={`/${actualPage}?slug=${slug}&apiRoute=${item.object}`}
          key={item.ID}
        >
          {item.title}
        </option>
      );
    });

    return (
      <div className="menu">
        <div className="brand">
          <Link href="/">
            <a className="starter-kit-logo">
              <Logo width={48} height={32}/>
              <div className="pl2">
                WordPress + React<br/>
                Starter Kit
              </div>
            </a>
          </Link>
        </div>
        <div className="dropdown">
          <select>
            {menuItems}
          </select>
        </div>
      </div>
    );
  }
}
export default Menu;
