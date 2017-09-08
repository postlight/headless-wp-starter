import Header from "../components/Header";
import React, { Component } from "react";

class Index extends Component {
    constructor() {
        super();
        this.state = {
            posts: [],
            pages: []
        };
    }
    componentDidMount() {
        let postsDataURL = "http://localhost:8080/wp-json/wp/v2/posts?_embed";
        fetch(postsDataURL)
            .then(res => res.json())
            .then(res => {
                this.setState({
                    posts: res
                });
            });
        let pageDataURL = "http://localhost:8080/wp-json/wp/v2/pages?_embed";
        fetch(pageDataURL)
            .then(res => res.json())
            .then(res => {
                this.setState({
                    pages: res
                });
            });
    }
    render() {
        let posts = this.state.posts.map((post, index) => {
            return (
                <ul key={index}>
                    <li>
                        <strong>Title:</strong> {post.title.rendered}
                    </li>
                </ul>
            );
        });
        let pages = this.state.pages.map((page, index) => {
            return (
                <ul key={index}>
                    <li>
                        <strong>Title:</strong> {page.title.rendered}
                    </li>
                </ul>
            );
        });
        return (
            <div>
                <Header />
                <h2>Posts</h2>
                {posts}
                <h2>Pages</h2>
                {pages}
            </div>
        );
    }
}

export default Index;
