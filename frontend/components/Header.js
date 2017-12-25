import React, { Component } from "react";
import Link from "next/link";
import Head from "next/head";
import Menu from "./Menu.js";
import { Config } from "../config.js";

const headerImageStyle = {
    marginTop: 50,
    marginBottom: 50
};

class Header extends Component {
    constructor() {
        super();
    }

    render() {

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
                <Menu />
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
