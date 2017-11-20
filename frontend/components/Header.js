import React, { Component } from "react";
import Link from "next/link";
import Head from "next/head";
import {Config} from "../config.js"

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
            Config.apiUrl + "/wp-json/menus/v1/menus/header-menu";
        fetch(menuItemsURL)
            .then(res => res.json())
            .then(res => {
                this.setState({
                    menu: res.items
                });
            });
    }
    getSlug(url) {
        const parts = url.split("/");
        return parts.length > 2 ? parts[parts.length - 2] : "";
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
