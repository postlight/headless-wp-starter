import React, { Component } from 'react';
import { withApollo } from 'react-apollo';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';

/**
 * GraphQL category query that takes a category slug as a filter
 * Returns the posts belonging to the category and the category name and ID
 */
const CATEGORY_QUERY = gql`
  query CategoryQuery($filter: String!) {
    posts(where: { categoryName: $filter }) {
      edges {
        node {
          title
          slug
        }
      }
    }
    categories(where: { slug: [$filter] }) {
      edges {
        node {
          name
          categoryId
        }
      }
    }
  }
`;

/**
 * Fetch and display a Category
 */
class Category extends Component {
  state = {
    category: {
      name: '',
      posts: [],
    },
  };

  componentDidMount() {
    this.executeCategoryQuery();
  }

  /**
   * Execute the category query, parse the result and set the state
   */
  executeCategoryQuery = async () => {
    const { match, client } = this.props;
    const filter = match.params.slug;
    const result = await client.query({
      query: CATEGORY_QUERY,
      variables: { filter },
    });
    const { name } = result.data.categories.edges[0].node;
    let posts = result.data.posts.edges;
    posts = posts.map(post => {
      const finalLink = `/post/${post.node.slug}`;
      const modifiedPost = { ...post };
      modifiedPost.node.link = finalLink;
      return modifiedPost;
    });

    const category = {
      name,
      posts,
    };
    this.setState({ category });
  };

  render() {
    const { category } = this.state;
    return (
      <div className="pa2">
        <h1>{category.name}</h1>
        <div className="flex mt2 items-start">
          <div className="flex items-center" />
          <div className="ml1">
            {category.posts.map((post, index) => (
              <div key={post.node.slug}>
                <span className="gray">{index + 1}.</span>
                <Link to={post.node.link} className="ml1 black">
                  {post.node.title}
                </Link>
              </div>
            ))}
            <div className="f6 lh-copy gray" />
          </div>
        </div>
      </div>
    );
  }
}

export default withApollo(Category);
