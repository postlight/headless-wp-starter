import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';

const CATEGORY_QUERY = gql`
    query CategoryQuery($filter: String!) {
        posts(where: { categoryName: $filter }) {
            edges {
                node {
                    title
                    content
                    link
                }
            }
        }
        categories(where: { slug: [$filter] }) {
            edges {
                node {
                    name
                    categoryId
                }
            }
        }
    }
`;

class Category extends Component {
    state = {
        category: {
            name: '',
            posts: []
        }
    };

    componentDidMount() {
        this._executeCategoryQuery();
    }

    render() {
        return (
            <div className="pa2">
                <div>{this.state.category.name}</div>
                <div className="flex mt2 items-start">
                    <div className="flex items-center" />
                    <div className="ml1">
                        {this.state.category.posts.map((post, index) => (
                            <div>
                                <span className="gray">{index + 1}.</span>
                                <Link
                                    to={post.node.link}
                                    className="ml1 no-underline black"
                                >
                                    {post.node.title}
                                </Link>
                            </div>
                        ))}
                        <div className="f6 lh-copy gray" />
                    </div>
                </div>
            </div>
        );
    }

    _executeCategoryQuery = async () => {
        const { params } = this.props.match;
        const filter = params.slug;
        const result = await this.props.client.query({
            query: CATEGORY_QUERY,
            variables: { filter }
        });
        console.log(result);
        const name = result.data.categories.edges[0].node.name;
        let posts = result.data.posts.edges;
        posts = posts.map(post => {
            const res = post.node.link.split('/');
            if (res[res.length - 2] !== 'post') {
                const finalLink = '/post/' + res[res.length - 2];
                post.node = { ...post.node, link: finalLink };
            }
            return post;
        });

        const category = {
            name: name,
            posts: posts
        };
        this.setState({ category });
    };
}

export default withApollo(Category);
