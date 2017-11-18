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
            let prefix = item.object
            let link = `/${prefix}?slug=${item.url.replace("http://localhost:8080", "")}&apiRoute=${prefix}`
            if (item.object === 'custom') {
                prefix = ''
                link = item.url.replace("http://localhost:8080", "")
                return (
                    <Link
                        href={link}
                        key={item.ID}
                    >
                        <a style={linkStyle}>{item.title}</a>
                    </Link>
                );
            }
            return (
              <Link
                as={`/${prefix}${item.url.replace("http://localhost:8080", "")}`}
                href={link}
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
                    {menuItems}
                </div>
                <img
                    src="/static/images/wordpress-plus-react-header.png"
                    width="1024"
                />
            </div>
        );
    }
}

export default Header;
