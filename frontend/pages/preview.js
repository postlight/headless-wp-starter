import Layout from "../components/Layout.js";
import React, { Component } from "react";
import Error from "next/error";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";
import WPAPI from "wpapi";

const wp = new WPAPI({ endpoint: Config.apiUrl });

wp.postPreview = wp.registerRoute('postlight/v1', '/post/preview');

class Preview extends Component {
    constructor() {
        super();
        this.state = {
            post: null
        };
    }

    componentDidMount() {
        const { id, wpnonce } = this.props.url.query;

        wp.postPreview().param('id', id).param('_wpnonce', wpnonce)
            .then((res) => {
                console.log('result', res);
                this.setState({
                    post: res
                });
            });
    }

    render() {
        if (
            this.state.post &&
            this.state.post.code &&
            this.state.post.code === "rest_cookie_invalid_nonce"
        )
            return <Error statusCode={404} />;

        return (
            <Layout>
                <Menu menu={this.props.headerMenu} />
                <h1>{this.state.post ? this.state.post.title.rendered : ""}</h1>
                <div
                    dangerouslySetInnerHTML={{
                        __html: this.state.post
                            ? this.state.post.content.rendered
                            : ""
                    }}
                />
            </Layout>
        );
    }
}

export default PageWrapper(Preview);
