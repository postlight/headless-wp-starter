import React, { Component } from 'react';
import { withApollo } from '@apollo/client/react/hoc';
import { Link } from 'react-router-dom';
import {
  ApolloClient,
  createHttpLink,
  InMemoryCache,
  gql,
} from '@apollo/client';
import { AUTH_TOKEN } from '../constants';
import Config from '../config';
import { ReactComponent as Logo } from '../static/images/starter-kit-logo.svg';

/**
 * GraphQL page query
 * Gets page's title and content using slug as uri
 */
const PAGE_QUERY = gql`
  query PageQuery($uri: ID!) {
    page(id: $uri, idType: URI) {
      title
      content
    }
  }
`;

/**
 * GraphQL pages and categories query
 * Gets all available pages and posts titles and slugs
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
  // used as a authenticated GraphQL client
  authClient = null;

  constructor() {
    super();
    this.state = {
      page: {
        title: '',
        content: '',
      },
      pages: [],
      posts: [],
    };
  }

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
    await this.authClient
      .query({
        query: PROTECTED_QUERY,
      })
      .catch((err) => {
        error = err;
      });
    if (error) {
      const { history } = this.props;
      localStorage.removeItem(AUTH_TOKEN);
      history.push(`/login`);
    }
  };

  /**
   * Execute the page query using uri and set the state
   */
  executePageQuery = async () => {
    const { match, client } = this.props;
    let uri = match.params.slug;
    if (!uri) {
      uri = 'welcome';
    }
    const result = await client.query({
      query: PAGE_QUERY,
      variables: { uri },
    });
    const { page } = result.data;
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
    posts = posts.map((post) => {
      const finalLink = `/post/${post.node.slug}`;
      const modifiedPost = { ...post, node: { ...post.node, link: finalLink } };
      return modifiedPost;
    });
    let pages = result.data.pages.edges;
    pages = pages.map((page) => {
      const finalLink = `/page/${page.node.slug}`;
      const modifiedPage = { ...page, node: { ...page.node, link: finalLink } };
      return modifiedPage;
    });

    this.setState({ posts, pages });
  };

  render() {
    const { page, posts, pages } = this.state;
    return (
      <div>
        <div className="graphql intro bg-black white ph3 pv4 ph5-m pv5-l flex flex-column flex-row-l">
          <div className="color-logo w-50-l mr3-l">
            <Logo width={440} height={280} />
          </div>
          <div className="subhed pr6-l">
            <h1>{page.title}</h1>
            <div className="dek">
              You are now running a WordPress backend with a React frontend.
            </div>
            <div className="api-info b mt4">
              Starter Kit supports both REST API and GraphQL
              <div className="api-toggle">
                <a className="rest" href="http://localhost:3000">
                  REST API
                </a>
                <a className="graphql" href="http://localhost:3001">
                  GraphQL
                </a>
              </div>
            </div>
          </div>
        </div>
        <div className="recent flex mh4 mt4 w-two-thirds-l center-l">
          <div className="w-50 pr3">
            <h2>Posts</h2>
            <ul>
              {posts.map((post) => (
                <li key={post.node.slug}>
                  <Link to={post.node.link}>{post.node.title}</Link>
                </li>
              ))}
            </ul>
          </div>
          <div className="w-50 pl3">
            <h2>Pages</h2>
            <ul>
              {pages.map((post) => {
                if (post.node.slug !== 'welcome') {
                  return (
                    <li key={post.node.slug}>
                      <Link to={post.node.link}>{post.node.title}</Link>
                    </li>
                  );
                }
                return false;
              })}
            </ul>
          </div>
        </div>
        <div
          className="content mh4 mv4 w-two-thirds-l center-l home"
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: page.content,
          }}
        />
      </div>
    );
  }
}

export default withApollo(Home);
