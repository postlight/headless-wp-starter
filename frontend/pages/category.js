import Layout from "../components/Layout.js";
import React, { Component } from "react";
import Link from "next/link";
import fetch from "isomorphic-unfetch";
import Error from "next/error";
import { Config } from "../config.js";

class Category extends Component {
    static async getInitialProps(context) {
        const { slug } = context.query;
        const res = await fetch(
            `${Config.apiUrl}/wp-json/wp/v2/categories?slug=${slug}`
        );
        const categories = await res.json();
        return { categories };
    }
    constructor() {
        super();
        this.state = {
            posts: []
        };
    }
    componentDidMount() {
        if (this.props.categories.length > 0) {
            const postsDataURL = `${
                Config.apiUrl
            }/wp-json/wp/v2/posts?_embed&categories=${
                this.props.categories[0].id
            }`;
            fetch(postsDataURL)
                .then(res => res.json())
                .then(res => {
                    this.setState({
                        posts: res
                    });
                });
        }
    }
    render() {
        if (this.props.categories.length == 0)
            return <Error statusCode={404} />;

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
        return (
            <Layout>
                <h1>{this.props.categories[0].name} Posts</h1>
                {posts}
            </Layout>
        );
    }
}

export default Category;
