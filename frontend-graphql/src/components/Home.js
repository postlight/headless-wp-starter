import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';
import logo from '../static/images/wordpress-plus-react-header.png';

const headerImageStyle = {
    marginTop: 50,
    marginBottom: 50
};

const PAGE_QUERY = gql`
    query PageQuery($filter: String!) {
        pages(where: { name: $filter }) {
            edges {
                node {
                    title
                    slug
                    content
                }
            }
        }
    }
`;

const PAGES_AND_CATEGORIES_QUERY = gql`
    query PagesAndPostsQuery {
        posts {
            edges {
                node {
                    title
                    slug
                }
            }
        }
        pages {
            edges {
                node {
                    title
                    slug
                }
            }
        }
    }
`;

class Home extends Component {
    state = {
        page: {
            title: '',
            content: ''
        },
        pages: [],
        posts: []
    };

    componentDidMount() {
        this._executePageQuery();
        this._executePagesAndCategoriesQuery();
    }

    render() {
        return (
            <div>
                <div className="pa2">
                    <img src={logo} width="815" style={headerImageStyle} />
                    <h1>{this.state.page.title}</h1>
                    <span
                        dangerouslySetInnerHTML={{
                            __html: this.state.page.content
                        }}
                    />
                    <p>
                        Make sure to check the{' '}
                        <a href="http://localhost:3000/">React frontend</a>,
                        built with <a href="http://learnnextjs.com/">Next.js</a>
                        !
                    </p>
                    <h2>Posts</h2>
                    <ul>
                        {this.state.posts.map((post, index) => (
                            <li key={index}>
                                <Link
                                    to={post.node.link}
                                    className="ml1 no-underline black"
                                >
                                    {post.node.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                    <h2>Pages</h2>
                    <ul>
                        {this.state.pages.map((page, index) => (
                            <li key={index}>
                                <Link
                                    to={page.node.link}
                                    className="ml1 no-underline black"
                                >
                                    {page.node.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        );
    }

    _executePageQuery = async () => {
        const { params } = this.props.match;
        let filter = params.slug;
        if (!filter) {
            filter = 'welcome';
        }
        const result = await this.props.client.query({
            query: PAGE_QUERY,
            variables: { filter }
        });
        const page = result.data.pages.edges[0].node;
        this.setState({ page });
    };

    _executePagesAndCategoriesQuery = async () => {
        const result = await this.props.client.query({
            query: PAGES_AND_CATEGORIES_QUERY
        });
        let posts = result.data.posts.edges;
        posts = posts.map(post => {
            const finalLink = '/post/' + post.node.slug;
            post.node = { ...post.node, link: finalLink };
            return post;
        });
        let pages = result.data.pages.edges;
        pages = pages.map(page => {
            const finalLink = '/page/' + page.node.slug;
            page.node = { ...page.node, link: finalLink };
            return page;
        });

        this.setState({ posts, pages });
    };
}

export default withApollo(Home);
