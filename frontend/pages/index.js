import Layout from "../components/Layout.js";
import React, { Component } from "react";
import Link from "next/link";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";
import WPAPI from "wpapi";

const wp = new WPAPI({ endpoint: Config.apiUrl });

const headerImageStyle = {
    marginTop: 50,
    marginBottom: 50
};

class Index extends Component {
    static async getInitialProps(context) {
        const page = await wp.pages().slug('welcome').embed()
            .then((data) => {
                return data[0];
            });
        const posts = await wp.posts().embed();
        const pages = await wp.pages().embed();

        return { page, posts, pages };
    }

    render() {
        const posts = this.props.posts.map((post, index) => {
            return (
                <ul key={index}>
                    <li>
                        <Link
                            as={`/post/${post.slug}`}
                            href={`/post?slug=${post.slug}&apiRoute=post`}
                        >
                            <a>{post.title.rendered}</a>
                        </Link>
                    </li>
                </ul>
            );
        });
        const pages = this.props.pages.map((page, index) => {
            return (
                <ul key={index}>
                    <li>
                        <Link
                            as={`/page/${page.slug}`}
                            href={`/post?slug=${page.slug}&apiRoute=page`}
                        >
                            <a>{page.title.rendered}</a>
                        </Link>
                    </li>
                </ul>
            );
        });
        return (
            <Layout>
                <Menu menu={this.props.headerMenu} />
                <img
                    src="/static/images/wordpress-plus-react-header.png"
                    width="815"
                    style={headerImageStyle}
                />
                <h1>{this.props.page.title.rendered}</h1>
                <div
                    dangerouslySetInnerHTML={{
                        __html: this.props.page.content.rendered
                    }}
                />
                <h2>Posts</h2>
                {posts}
                <h2>Pages</h2>
                {pages}
            </Layout>
        );
    }
}

export default PageWrapper(Index);
