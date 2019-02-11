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
            content: ''
        }
    };

    componentDidMount() {
        this._executePageQuery();
    }

    render() {
        return (
            <div>
                <div className="pa2">
                    <h1>{this.state.page.title}</h1>
                </div>
                <div
                    dangerouslySetInnerHTML={{
                        __html: this.state.page.content
                    }}
                />
            </div>
        );
    }

    _executePageQuery = async () => {
        const { params } = this.props.match;
        let filter = params.slug;
        if (!filter) {
            filter = 'welcome';
        }
        const result = await this.props.client.query({
            query: PAGE_QUERY,
            variables: { filter }
        });
        const page = result.data.pages.edges[0].node;
        this.setState({ page });
    };
}

export default withApollo(Page);
