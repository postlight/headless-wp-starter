import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';

const POST_SEARCH_QUERY = gql`
    query PostSearchQuery($filter: String!) {
        posts(where: { search: $filter }) {
            edges {
                node {
                    title
                    link
                    author {
                        nickname
                    }
                }
            }
        }
    }
`;

class Search extends Component {
    state = {
        posts: [],
        filter: ''
    };

    render() {
        return (
            <div className="pa2">
                <div>
                    Search
                    <input
                        className="search"
                        type="text"
                        onChange={e =>
                            this.setState({ filter: e.target.value })
                        }
                    />
                    <button
                        className="search"
                        onClick={() => this._executeSearch()}
                    >
                        OK
                    </button>
                </div>
                <div className="flex mt2 items-start">
                    <div className="flex items-center" />
                    <div className="ml1">
                        {this.state.posts.map((post, index) => (
                            <div>
                                <span className="gray">{index + 1}.</span>
                                <Link
                                    to={post.node.link}
                                    className="ml1 no-underline black"
                                >
                                    {post.node.title}
                                </Link>
                                <span className="gray">
                                    {' '}
                                    by {post.node.author.nickname}
                                </span>
                            </div>
                        ))}
                        <div className="f6 lh-copy gray" />
                    </div>
                </div>
            </div>
        );
    }

    _executeSearch = async () => {
        const { filter } = this.state;
        const result = await this.props.client.query({
            query: POST_SEARCH_QUERY,
            variables: { filter }
        });
        let posts = result.data.posts.edges;
        posts = posts.map(post => {
            const res = post.node.link.split('/');
            if (res[res.length - 2] !== 'post') {
                const finalLink = '/post/' + res[res.length - 2];
                post.node = { ...post.node, link: finalLink };
            }
            return post;
        });
        this.setState({ posts });
    };
}

export default withApollo(Search);
