/* eslint-disable jsx-a11y/anchor-is-valid */
import React, { Component } from 'react';
import Link from 'next/link';
import Router from 'next/router';
import Config from '../config';

const linkStyle = {
  marginRight: 15,
};

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
          <Link href={item.url} key={item.ID}>
            <a style={linkStyle}>{item.title}</a>
          </Link>
        );
      }
      const slug = getSlug(item.url);
      const actualPage = item.object === 'category' ? 'category' : 'post';
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

    return (
      <div>
        <Link href="/">
          <a style={linkStyle}>Home</a>
        </Link>
        {menuItems}

        {token ? (
          <button
            type="button"
            className="pointer black"
            onClick={() => {
              localStorage.removeItem(Config.AUTH_TOKEN);
              Router.push('/login');
            }}
          >
            Logout {username}
          </button>
        ) : (
          <Link href="/login">
            <a style={linkStyle}>Login</a>
          </Link>
        )}
      </div>
    );
  }
}
export default Menu;
