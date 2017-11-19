import React, { Component } from "react";
import Link from "next/link";
import Head from "next/head";

const linkStyle = {
    marginRight: 15
};

const headerImageStyle = {
    marginTop: 50,
    marginBottom: 50
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
                <Head>
                    <meta
                        name="viewport"
                        content="width=device-width, initial-scale=1"
                    />
                    <meta charSet="utf-8" />
                    <title>
                        WordPress + React Starter Kit Frontend by Postlight
                    </title>
                </Head>
                <style jsx global>{`
                    body {
                        padding: 0;
                        margin: 0;
                        background: #fff;
                        font: 14px helvetica;
                        color: #000;
                    }
                `}</style>
                <div>
                    <Link href="/">
                        <a style={linkStyle}>Home</a>
                    </Link>
                    {menuItems}
                </div>
                <img
                    src="/static/images/wordpress-plus-react-header.png"
                    width="815"
                    style={headerImageStyle}
                />
            </div>
        );
    }
}

export default Header;
