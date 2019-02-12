import React, { Component } from "react";
import Link from "next/link";
import Head from "next/head";
import Menu from "./Menu.js";
import { Config } from "../config.js";
import stylesheet from '../src/styles/style.scss'

class Header extends Component {
    constructor() {
        super();
    }

    render() {
        let title = '';
        if ( this.props.title ) {
            title = this.props.title;
            if ( this.props.settings ) {
                title += ' | ' + this.props.settings.site_title;
            }
        } else if ( this.props.settings ) {
            title = this.props.settings.site_title;
        } else {
            title = 'WordPress + React Starter Kit Frontend by Postlight';
        }

        return (
            <div>
                <Head>
                    <style dangerouslySetInnerHTML={{ __html: stylesheet }} />
                    <meta
                        name="viewport"
                        content="width=device-width, initial-scale=1"
                    />
                    <meta charSet="utf-8" />
                    <title>{title}</title>
                </Head>
            </div>
        );
    }
}

export default Header;
