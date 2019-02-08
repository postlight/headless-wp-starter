import React, { Component } from 'react';
import { withApollo } from 'react-apollo';

class Home extends Component {
    state = {
        posts: [],
        filter: ''
    };

    render() {
        return (
            <div className="pa2">
                <div>Welcome</div>
            </div>
        );
    }
}

export default withApollo(Home);
