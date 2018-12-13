import Layout from "../components/Layout.js";
import React, { Component } from "react";
import Error from "next/error";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";
import WPAPI from "wpapi";

const wp = new WPAPI({ endpoint: Config.apiUrl });

class Post extends Component {
    static async getInitialProps(context) {
        const { slug, apiRoute } = context.query;

        let apiMethod = wp.posts();

        switch (apiRoute) {
            case 'category':
                apiMethod = wp.categories();
                break;
            case 'page':
                apiMethod = wp.pages();
                break;
        };

        const post = await apiMethod.slug(slug).embed()
            .then((data) => {
                return data[0];
            });

        return { post };
    }

    render() {
        if (!this.props.post.title) return <Error statusCode={404} />;

        return (
            <Layout>
                <Menu menu={this.props.headerMenu} />
                <h1>{this.props.post.title.rendered}</h1>
                <div
                    dangerouslySetInnerHTML={{
                        __html: this.props.post.content.rendered
                    }}
                />
            </Layout>
        );
    }
}

export default PageWrapper(Post);
