import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';

const PAGE_QUERY = gql`
  query PageQuery($filter: String!) {
    pages(where: { name: $filter }) {
      edges {
        node {
          title
          content
        }
      }
    }
  }
`;

class Page extends Component {
  state = {
    page: {
      title: '',
      content: '',
    },
  };

  componentDidMount() {
    this.executePageQuery();
  }

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

  render() {
    const { page } = this.state;
    return (
      <div>
        <div className="pa2">
          <h1>{page.title}</h1>
        </div>
        <div
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: page.content,
          }}
        />
      </div>
    );
  }
}

export default withApollo(Page);
