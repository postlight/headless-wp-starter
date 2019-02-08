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
                nickname: ''
            }
        }
    };

    componentDidMount() {
        this._executePostQuery();
    }

    render() {
        return (
            <div>
                <div className="pa2">
                    <div>{this.state.post.title}</div>
                </div>
                <div
                    dangerouslySetInnerHTML={{
                        __html: this.state.post.content
                    }}
                />
                <div>{this.state.post.author.nickname}</div>
            </div>
        );
    }

    _executePostQuery = async () => {
        const { params } = this.props.match;
        const filter = params.slug;
        const result = await this.props.client.query({
            query: POST_QUERY,
            variables: { filter }
        });
        const post = result.data.postBy;
        this.setState({ post });
    };
}

export default withApollo(Post);
