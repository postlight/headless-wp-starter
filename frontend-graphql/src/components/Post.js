import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';

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
        <div>{post.author.nickname}</div>
      </div>
    );
  }
}

export default withApollo(Post);
