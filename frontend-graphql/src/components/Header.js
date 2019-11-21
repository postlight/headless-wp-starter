import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import { withRouter } from 'react-router';
import { withApollo } from 'react-apollo';
import { compose } from 'recompose';
import gql from 'graphql-tag';
import { AUTH_TOKEN, USERNAME } from '../constants';
import { ReactComponent as Logo } from '../static/images/starter-kit-logo.svg';

/**
 * GraphQL menu query
 * Gets the labels, types (internal or external) and URLs
 */
const MENU_QUERY = gql`
  query MenuQuery {
    headerMenu {
      url
      label
      type
    }
  }
`;

// Checks if urltype is internal or external
const isInternal = urltype => urltype.includes('internal');

class Header extends Component {
  state = {
    menus: [],
  };

  componentDidMount() {
    this.executeMenu();
  }

  /**
   * Execute the menu query, parse the result and set the state
   */
  executeMenu = async () => {
    const { client } = this.props;
    const result = await client.query({
      query: MENU_QUERY,
    });
    const menus = result.data.headerMenu;
    this.setState({ menus });
  };

/*
      <div className="flex pa1 justify-between nowrap padding bottomborder">
        <div className="flex flex-fixed black">
          <Link to="/" className="ml1 no-underline black">
            Home
          </Link>
        </div>
        <div className="flex flex-fixed">
          <Link to="/search" className="ml1 no-underline black">
            Search
          </Link>
          <div className="ml1">|</div>
        </div>
      </div>

        <div className="dropdown bb flex justify-center items-center dn-l">
          <select
            onChange={handleSelectChange}
          >
            <option value={false}>Menu</option>
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
*/
  render() {
    const authToken = localStorage.getItem(AUTH_TOKEN);
    const { menus } = this.state;
    const { history } = this.props;
    return (
      <div className="menu bb">
        <div className="flex justify-between w-90-l center-l">
          <div className="brand bb flex justify-center items-center w-100 justify-between-l bn-l">
            <Link
              to="/"
              className="starter-kit-logo"
            >
              <Logo width={48} height={32}/>
              <div className="pl2">
                WordPress + React<br/>
                Starter Kit
              </div>
            </Link>
          </div>
          <div className="links dn flex-l justify-between items-center">
            {menus.map(menu => {
              if (isInternal(menu.type)) {
                return (
                  <Link
                    key={menu.label}
                    to={menu.url}
                    className="ml1 no-underline black"
                  >
                    {menu.label}
                  </Link>
                );
              }
              return (
                <a
                  key={menu.label}
                  href={menu.url}
                  className="ml1 no-underline black"
                >
                  {menu.label}
                </a>
              );
            })}

            {authToken ? (
              <a
                href="/"
                className="pointer round-btn ba bw1 pv2 ph3"
                onClick={() => {
                  localStorage.removeItem(AUTH_TOKEN);
                  history.push(`/`);
                }}
              >
                Log out {localStorage.getItem(USERNAME)}
              </a>
            ) : (
              <Link
                to="/login"
                className="round-btn ba bw1 pv2 ph3"
              >
                Log in
              </Link>
            )}
          </div>
        </div>
      </div>

    );
  }
}

export default compose(
  withRouter,
  withApollo,
)(Header);
