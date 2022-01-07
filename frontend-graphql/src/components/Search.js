import React, { Component } from 'react';
import { withApollo } from '@apollo/client/react/hoc';
import { gql } from '@apollo/client';
import { Link } from 'react-router-dom';
/**
 * GraphQL post search query that takes a filter
 * Returns the titles, slugs and authors of posts found
 */
const POST_SEARCH_QUERY = gql`
  query PostSearchQuery($filter: String!) {
    posts(where: { search: $filter }) {
      edges {
        node {
          title
          slug
          author {
            node {
              name
            }
          }
        }
      }
    }
  }
`;

/**
 * Search component that fetches results by filter
 */
class Search extends Component {
  constructor() {
    super();
    this.state = {
      posts: [],
      filter: '',
    };
  }

  handleKeyDown = (e) => {
    if (e.keyCode === 13) {
      this.executeSearch();
    }
    return true;
  };

  /**
   * Execute search query, process the response and set the state
   */
  executeSearch = async () => {
    const { client } = this.props;
    const { filter } = this.state;
    let posts = [];
    if (filter.length === 0) {
      this.setState({ posts });
    } else {
      const result = await client.query({
        query: POST_SEARCH_QUERY,
        variables: { filter },
      });
      posts = result.data.posts.edges;
      posts = posts.map((post) => {
        const finalLink = `/post/${post.node.slug}`;
        const modifiedPost = {
          ...post,
          node: { ...post.node, link: finalLink },
        };
        return modifiedPost;
      });
      this.setState({ posts });
    }
  };

  render() {
    const { posts } = this.state;
    return (
      <div className="content login mh4 mv4 w-two-thirds-l center-l">
        <div>
          <h1>Search</h1>
          <input
            className="db w-100 pa3 mv3 br6 ba b--black"
            type="text"
            placeholder="Search by name and content"
            onChange={(e) => this.setState({ filter: e.target.value })}
            onKeyDown={this.handleKeyDown}
          />
          <button
            className="round-btn invert ba bw1 pv2 ph3"
            type="button"
            onClick={() => this.executeSearch()}
          >
            Submit
          </button>
        </div>
        <div className="mv4">
          {posts.map((post, index) => (
            <div className="mv4" key={post.node.slug}>
              <span className="gray">{index + 1}.</span>
              <Link to={post.node.link} className="ml1 black">
                <h3>{post.node.title}</h3>
              </Link>
            </div>
          ))}
        </div>
      </div>
    );
  }
}

export default withApollo(Search);
