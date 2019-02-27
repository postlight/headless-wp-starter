import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import { withRouter } from 'react-router';
import { withApollo } from 'react-apollo';
import { compose } from 'recompose';
import gql from 'graphql-tag';
import { AUTH_TOKEN, USERNAME } from '../constants';

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

  render() {
    const authToken = localStorage.getItem(AUTH_TOKEN);
    const { menus } = this.state;
    const { history } = this.props;
    return (
      <div className="flex pa1 justify-between nowrap padding bottomborder">
        <div className="flex flex-fixed black">
          <Link to="/" className="ml1 no-underline black">
            Home
          </Link>
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
        </div>
        <div className="flex flex-fixed">
          <Link to="/search" className="ml1 no-underline black">
            Search
          </Link>
          <div className="ml1">|</div>
          {authToken ? (
            <button
              type="button"
              className="pointer black"
              onClick={() => {
                localStorage.removeItem(AUTH_TOKEN);
                history.push(`/`);
              }}
            >
              Logout {localStorage.getItem(USERNAME)}
            </button>
          ) : (
            <Link to="/login" className="ml1 no-underline black">
              Login
            </Link>
          )}
        </div>
      </div>
    );
  }
}

export default compose(
  withRouter,
  withApollo,
)(Header);
