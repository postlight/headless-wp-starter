import React, { Component } from 'react';
import { Mutation } from '@apollo/client/react/components';
import { gql } from '@apollo/client';
import { AUTH_TOKEN, USERNAME } from '../constants';

/**
 * GraphQL mutation used for logging in
 * Returns an authToken and nickname
 */
const LOGIN_MUTATION = gql`
  mutation LoginMutation(
    $username: String!
    $password: String!
    $clientMutationId: String!
  ) {
    login(
      input: {
        clientMutationId: $clientMutationId
        username: $username
        password: $password
      }
    ) {
      authToken
      user {
        nickname
      }
    }
  }
`;

/**
 * Login component that uses a graphql mutation
 */
class Login extends Component {
  constructor() {
    super();
    this.state = {
      username: '',
      password: '',
      message: '',
    };
  }

  confirm = async data => {
    const { history } = this.props;
    const { authToken, user } = data.login;
    localStorage.setItem(AUTH_TOKEN, authToken);
    localStorage.setItem(USERNAME, user.nickname);
    history.push(`/`);
  };

  handleError = () => {
    const message =
      ' - Sorry, that username and password combination is not valid.';
    this.setState({ message });
  };

  render() {
    const { username, password, message } = this.state;
    const clientMutationId =
      Math.random()
        .toString(36)
        .substring(2) + new Date().getTime().toString(36);
    return (
      <div className="content login mh4 mv4 w-two-thirds-l center-l">
        <div>
          <h1>Log in</h1>
          <p>
            Starter Kit allows you to log in via the JavaScript frontend,
            meaning you can interact with the backend without gaining admin
            access.
          </p>
          <p>
            <strong>
              Log in to view hidden posts only available to authenticated users.
            </strong>
          </p>
          <p className="message mb3">
            <strong>{message}</strong>
          </p>
          <input
            className="db w-100 pa3 mv3 br6 ba b--black"
            value={username}
            onChange={e => this.setState({ username: e.target.value })}
            type="text"
            placeholder="Username"
          />
          <input
            className="db w-100 pa3 mv3 br6 ba b--black"
            value={password}
            onChange={e => this.setState({ password: e.target.value })}
            type="password"
            placeholder="Password"
          />
          <Mutation
            mutation={LOGIN_MUTATION}
            variables={{ username, password, clientMutationId }}
            onCompleted={data => this.confirm(data)}
            onError={() => this.handleError()}
          >
            {mutation => (
              <button
                className="round-btn invert ba bw1 pv2 ph3"
                type="button"
                onClick={mutation}
              >
                Log in
              </button>
            )}
          </Mutation>
        </div>
      </div>
    );
  }
}

export default Login;
