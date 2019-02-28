import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';

/**
 * GraphQL post query that takes a post slug as a filter
 * Returns the title, content and author of the post
 */
const POST_QUERY = gql`
  query PostQuery($filter: String!) {
    postBy(slug: $filter) {
      title
      content
      author {
        nickname
      }
    }
  }
`;

/**
 * Fetch and display a Post
 */
class Post extends Component {
  state = {
    post: {
      title: '',
      content: '',
      author: {
        nickname: '',
      },
    },
  };

  componentDidMount() {
    this.executePostQuery();
  }

  /**
   * Execute post query, process the response and set the state
   */
  executePostQuery = async () => {
    const { match, client } = this.props;
    const filter = match.params.slug;
    const result = await client.query({
      query: POST_QUERY,
      variables: { filter },
    });
    const post = result.data.postBy;
    this.setState({ post });
  };

  render() {
    const { post } = this.state;
    return (
      <div>
        <div className="pa2">
          <h1>{post.title}</h1>
        </div>
        <div
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: post.content,
          }}
        />
        <div>Written by {post.author.nickname}</div>
      </div>
    );
  }
}

export default withApollo(Post);
