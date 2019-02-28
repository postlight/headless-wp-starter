import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';
import { createHttpLink } from 'apollo-link-http';
import { InMemoryCache } from 'apollo-cache-inmemory';
import { ApolloClient } from 'apollo-boost';
import logo from '../static/images/wordpress-plus-react-header.png';
import { AUTH_TOKEN } from '../constants';
import Config from '../config';

const headerImageStyle = {
  marginTop: 50,
  marginBottom: 50,
};

/**
 * GraphQL page query
 * Gets page's tilte and content using slug as filter
 */
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

/**
 * GraphQL pages and categories query
 * Gets all available pages and posts tiltes and slugs
 */
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

/**
 * GraphQL protected query, an example of an authenticated query
 * If not authenticated it will return an error
 * If authenticated it will return the viewer's id and username
 */
const PROTECTED_QUERY = gql`
  query ProtectedQuery {
    viewer {
      userId
      username
    }
  }
`;

class Home extends Component {
  state = {
    userId: null,
    page: {
      title: '',
      content: '',
    },
    pages: [],
    posts: [],
  };

  // used as a authenticated GraphQL client
  authClient = null;

  componentDidMount() {
    this.executePageQuery();
    this.executePagesAndCategoriesQuery();

    // if localstorage contains a JWT token
    // initiate a authenticated client and execute a protected query
    const authToken = localStorage.getItem(AUTH_TOKEN);
    if (authToken) {
      this.authClient = new ApolloClient({
        link: createHttpLink({
          uri: Config.gqlUrl,
          headers: {
            Authorization: authToken ? `Bearer ${authToken}` : null,
          },
        }),
        cache: new InMemoryCache(),
      });
      this.executeProtectedQuery();
    }
  }

  /**
   * Execute the protected query and update state
   */
  executeProtectedQuery = async () => {
    let error = null;
    const result = await this.authClient
      .query({
        query: PROTECTED_QUERY,
      })
      .catch(err => {
        error = err;
      });
    if (!error) {
      const { userId } = result.data.viewer;
      this.setState({ userId });
    } else {
      const { history } = this.props;
      localStorage.removeItem(AUTH_TOKEN);
      history.push(`/login`);
    }
  };

  /**
   * Execute the page query using filter and set the state
   */
  executePageQuery = async () => {
    const { match, client } = this.props;
    let filter = match.params.slug;
    if (!filter) {
      filter = 'welcome';
    }
    const result = await client.query({
      query: PAGE_QUERY,
      variables: { filter },
    });
    const page = result.data.pages.edges[0].node;
    this.setState({ page });
  };

  /**
   * Execute the pages and categories query and set the state
   */
  executePagesAndCategoriesQuery = async () => {
    const { client } = this.props;
    const result = await client.query({
      query: PAGES_AND_CATEGORIES_QUERY,
    });
    let posts = result.data.posts.edges;
    posts = posts.map(post => {
      const finalLink = `/post/${post.node.slug}`;
      const modifiedPost = { ...post };
      modifiedPost.node.link = finalLink;
      return modifiedPost;
    });
    let pages = result.data.pages.edges;
    pages = pages.map(page => {
      const finalLink = `/page/${page.node.slug}`;
      const modifiedPage = { ...page };
      modifiedPage.node.link = finalLink;
      return modifiedPage;
    });

    this.setState({ posts, pages });
  };

  render() {
    const authToken = localStorage.getItem(AUTH_TOKEN);
    const { page, posts, pages, userId } = this.state;
    return (
      <div>
        <div className="pa2">
          <img src={logo} width="815" style={headerImageStyle} alt="logo" />
          <h1>{page.title}</h1>
          <span
            // eslint-disable-next-line react/no-danger
            dangerouslySetInnerHTML={{
              __html: page.content,
            }}
          />
          <h2>Posts</h2>
          <ul>
            {posts.map(post => (
              <li key={post.node.slug}>
                <Link to={post.node.link} className="ml1 black">
                  {post.node.title}
                </Link>
              </li>
            ))}
          </ul>
          <h2>Pages</h2>
          <ul>
            {pages.map(pageit => (
              <li key={pageit.node.slug}>
                <Link to={pageit.node.link} className="ml1 black">
                  {pageit.node.title}
                </Link>
              </li>
            ))}
          </ul>
          {authToken ? (
            <div>
              <h2>You Are Logged In</h2>
              <p>
                Your user ID is <span>{userId}</span>, retrieved via an
                authenticated API query.
              </p>
            </div>
          ) : (
            <div>
              <h2>You Are Not Logged In</h2>
              <p>
                The frontend is not making authenticated API requests.{' '}
                <a href="/login">Log in.</a>
              </p>
            </div>
          )}
          <h2>Where You're At</h2>
          <p>
            You are looking at the GraphQL-powered React frontend. Be sure to
            also check out the{' '}
            <a href="http://localhost:3000/">REST-powered frontend</a>.
          </p>
        </div>
      </div>
    );
  }
}

export default withApollo(Home);
