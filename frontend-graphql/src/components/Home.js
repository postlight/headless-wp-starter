import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';
import logo from '../static/images/wordpress-plus-react-header.png';
import { AUTH_TOKEN } from '../constants';

const headerImageStyle = {
  marginTop: 50,
  marginBottom: 50,
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

const PROTECTED_QUERY = gql`
  query ProtectedQuery {
    viewer {
      id
      username
    }
  }
`;

class Home extends Component {
  state = {
    id: null,
    page: {
      title: '',
      content: '',
    },
    pages: [],
    posts: [],
  };

  componentDidMount() {
    this.executePageQuery();
    this.executePagesAndCategoriesQuery();
    const authToken = localStorage.getItem(AUTH_TOKEN);
    if (authToken) {
      this.executeProtectedQuery();
    }
  }

  executeProtectedQuery = async () => {
    const { client } = this.props;

    const result = await client.query({
      query: PROTECTED_QUERY,
    });
    const { id } = result.data.viewer;
    this.setState({ id });
  };

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
    const { page, posts, pages, id } = this.state;
    const authToken = localStorage.getItem(AUTH_TOKEN);
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
          <p>
            Make sure to check the{' '}
            <a href="http://localhost:3000/">React frontend</a>, built with{' '}
            <a href="http://learnnextjs.com/">Next.js</a>!
          </p>
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
                Using an authenticated query, we got your id: <span>{id}</span>
              </p>
            </div>
          ) : (
            ''
          )}
        </div>
      </div>
    );
  }
}

export default withApollo(Home);
