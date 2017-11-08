import React, { Component } from "react";
import Link from "next/link";

const linkStyle = {
    marginRight: 15
};

class Header extends Component {
    constructor() {
        super();
        this.state = {
            menu: []
        };
    }
    componentDidMount() {
        const menuItemsURL =
            "http://localhost:8080/wp-json/menus/v1/menus/main";
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
            return (
                <Link
                    href={item.url.replace("http://localhost:8080", "")}
                    key={item.ID}
                >
                    <a style={linkStyle}>{item.title}</a>
                </Link>
            );
        });
        return (
            <div>
                <div>
                    <Link href="/">
                        <a style={linkStyle}>Home</a>
                    </Link>
                    <Link href="/another-page">
                        <a style={linkStyle}>Another Page</a>
                    </Link>
                    <span style={linkStyle}>WordPress menu:</span>
                    {menuItems}
                </div>
                <img
                    src="static/images/wordpress-plus-react-header.png"
                    width="1024"
                />
                <h1>Hello Headless WordPress</h1>
            </div>
        );
    }
}

export default Header;
