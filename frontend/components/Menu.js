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

    return (
      <div className="menu">
        <div class="flex">
          <div className="brand bb flex justify-center items-center w-100 w-25-l bn-l">
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
          <div className="links dn flex-l justify-around items-center w-75">
            {menu.items.map(item => {
              if (item.object === 'custom') {
                return (
                  <Link href={item.url} key={item.ID}>
                    <a>{item.title}</a>
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
                  <a>{item.title}</a>
                </Link>
              );
            })}
          </div>
        </div>
        <div className="dropdown bb flex justify-center items-center dn-l">
          <select>
            {menu.items.map(item => {
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
            })}
          </select>
        </div>
      </div>
    );
  }
}
export default Menu;
