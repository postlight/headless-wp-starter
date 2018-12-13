import React from "react";
import { Config } from "../config.js";
import WPAPI from "wpapi";

const wp = new WPAPI({ endpoint: Config.apiUrl });

// This route is copied from the plugin: wordpress/wp-content/plugins/wp-rest-api-v2-menus/wp-rest-api-v2-menus.php
wp.menus = wp.registerRoute('menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)');

const PageWrapper = Comp => (
    class extends React.Component {
        static async getInitialProps(args) {
            const headerMenu = await wp.menus().id('header-menu');

            return {
                headerMenu,
                ...(Comp.getInitialProps ? await Comp.getInitialProps(args) : null),
            };
        }

        render() {
            return (
                <Comp {...this.props} />
            )
        }
    }
)

export default PageWrapper;
