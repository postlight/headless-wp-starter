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
          slug
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
    filter: '',
  };

  executeSearch = async () => {
    const { client } = this.props;
    const { filter } = this.state;
    const result = await client.query({
      query: POST_SEARCH_QUERY,
      variables: { filter },
    });
    let posts = result.data.posts.edges;
    posts = posts.map(post => {
      const finalLink = `/post/${post.node.slug}`;
      const modifiedPost = { ...post };
      modifiedPost.node.link = finalLink;
      return modifiedPost;
    });
    this.setState({ posts });
  };

  render() {
    const { posts } = this.state;
    return (
      <div className="pa2">
        <div>
          Search
          <input
            className="search"
            type="text"
            onChange={e => this.setState({ filter: e.target.value })}
          />
          <button
            className="search"
            type="button"
            onClick={() => this.executeSearch()}
          >
            OK
          </button>
        </div>
        <div className="flex mt2 items-start">
          <div className="flex items-center" />
          <div className="ml1">
            {posts.map((post, index) => (
              <div key={post.node.slug}>
                <span className="gray">{index + 1}.</span>
                <Link to={post.node.link} className="ml1 black">
                  {post.node.title}
                </Link>
                <span className="gray"> by {post.node.author.nickname}</span>
              </div>
            ))}
            <div className="f6 lh-copy gray" />
          </div>
        </div>
      </div>
    );
  }
}

export default withApollo(Search);
