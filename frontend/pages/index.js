import Layout from "../components/Layout.js";
import React, { Component } from "react";
import fetch from "isomorphic-unfetch";
import Link from "next/link";
import PageWrapper from "../components/PageWrapper.js";
import Menu from "../components/Menu.js";
import { Config } from "../config.js";

const headerImageStyle = {
    marginTop: 50,
    marginBottom: 50
};

class Index extends Component {
    static async getInitialProps(context) {
        const pageRes = await fetch(
            `${Config.apiUrl}/wp-json/postlight/v1/page?slug=welcome`
        );
        const page = await pageRes.json();
        const postsRes = await fetch(
            `${Config.apiUrl}/wp-json/wp/v2/posts?_embed`
        );
        const posts = await postsRes.json();
        const pagesRes = await fetch(
            `${Config.apiUrl}/wp-json/wp/v2/pages?_embed`
        );
        const pages = await pagesRes.json();
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
