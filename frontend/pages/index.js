import Layout from "../components/Layout.js";
import React, { Component } from "react";
import Link from "next/link";

class Index extends Component {
    constructor() {
        super();
        this.state = {
            posts: [],
            pages: []
        };
    }
    componentDidMount() {
        const postsDataURL = "http://localhost:8080/wp-json/wp/v2/posts?_embed";
        fetch(postsDataURL)
            .then(res => res.json())
            .then(res => {
                this.setState({
                    posts: res
                });
            });
        const pageDataURL = "http://localhost:8080/wp-json/wp/v2/pages?_embed";
        fetch(pageDataURL)
            .then(res => res.json())
            .then(res => {
                this.setState({
                    pages: res
                });
            });
    }
    render() {
        const posts = this.state.posts.map((post, index) => {
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
        const pages = this.state.pages.map((page, index) => {
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
                <h1>Hello Headless WordPress</h1>
                <h2>Posts</h2>
                {posts}
                <h2>Pages</h2>
                {pages}
            </Layout>
        );
    }
}

export default Index;
