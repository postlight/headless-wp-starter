import React, { Component } from 'react';
import { withApollo } from '@apollo/client/react/hoc';
import { gql } from '@apollo/client';

/**
 * GraphQL post query that takes a post slug as a filter
 * Returns the title, content and author of the post
 */
const POST_QUERY = gql`
  query PostQuery($filter: ID!) {
    post(id: $filter, idType: SLUG) {
      title
      content
      author {
        node {
          name
        }
      }
    }
  }
`;

/**
 * Fetch and display a Post
 */
class Post extends Component {
  constructor() {
    super();
    this.state = {
      post: {
        title: '',
        content: '',
        author: {
          node: {
            name: '',
          },
        },
      },
    };
  }

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
    const { post } = result.data;
    this.setState({ post });
  };

  render() {
    const { post } = this.state;
    return (
      <div
        className={`content mh4 mv4 w-two-thirds-l center-l post-${post.id} post-type-post`}
      >
        <h1>{post.title}</h1>
        <div
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: post.content,
          }}
        />
      </div>
    );
  }
}

export default withApollo(Post);
