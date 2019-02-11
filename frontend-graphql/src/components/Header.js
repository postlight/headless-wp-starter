import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import { withRouter } from 'react-router';
import { AUTH_TOKEN } from '../constants';
import gql from 'graphql-tag';
import { withApollo } from 'react-apollo';
import { compose } from 'recompose';

const MENU_QUERY = gql`
    query MenuQuery {
        headerMenu {
            url
            label
            type
        }
    }
`;

class Header extends Component {
    state = {
        menus: []
    };

    componentDidMount() {
        this._executeMenu();
    }

    isInternal(urltype) {
        return urltype.includes('internal');
    }

    render() {
        const authToken = localStorage.getItem(AUTH_TOKEN);
        return (
            <div className="flex pa1 justify-between nowrap padding bottomborder">
                <div className="flex flex-fixed black">
                    <Link to="/" className="ml1 no-underline black">
                        Home
                    </Link>
                    {this.state.menus.map(
                        function(menu, index) {
                            if (this.isInternal(menu.type)) {
                                return (
                                    <Link
                                        key={index}
                                        to={menu.url}
                                        className="ml1 no-underline black"
                                    >
                                        {menu.label}
                                    </Link>
                                );
                            } else {
                                return (
                                    <a
                                        key={index}
                                        href={menu.url}
                                        className="ml1 no-underline black"
                                    >
                                        {menu.label}
                                    </a>
                                );
                            }
                        }.bind(this)
                    )}
                </div>
                <div className="flex flex-fixed">
                    <Link to="/search" className="ml1 no-underline black">
                        Search
                    </Link>
                    <div className="ml1">|</div>
                    {authToken ? (
                        <div
                            className="ml1 pointer black"
                            onClick={() => {
                                localStorage.removeItem(AUTH_TOKEN);
                                this.props.history.push(`/`);
                            }}
                        >
                            Logout
                        </div>
                    ) : (
                        <Link to="/login" className="ml1 no-underline black">
                            Login
                        </Link>
                    )}
                </div>
            </div>
        );
    }

    _executeMenu = async () => {
        const result = await this.props.client.query({
            query: MENU_QUERY
        });
        const menus = result.data.headerMenu;
        this.setState({ menus });
    };
}

export default compose(
    withRouter,
    withApollo
)(Header);
