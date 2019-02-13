import React, { Component } from 'react';
import axios from 'axios';
import Router from 'next/router';
import Layout from '../components/Layout';
import PageWrapper from '../components/PageWrapper';
import Menu from '../components/Menu';
import Config from '../config';

class Login extends Component {
  state = {
    username: '',
    password: '',
  };

  static async getInitialProps() {
    return '';
  }

  login() {
    const { username, password } = this.state;
    axios
      .post(`${Config.apiUrl}/jwt-auth/v1/token`, {
        username,
        password,
      })
      .then(res => {
        const { data } = res;
        localStorage.setItem(Config.AUTH_TOKEN, data.token);
        localStorage.setItem(Config.USERNAME, data.user_nicename);
        Router.push('/');
      });
  }

  render() {
    const { username, password } = this.state;
    const { headerMenu } = this.props;

    return (
      <Layout>
        <Menu menu={headerMenu} />
        <h1>Login</h1>
        <div className="login">
          <input
            className="input-padding"
            value={username}
            onChange={e => this.setState({ username: e.target.value })}
            type="text"
            placeholder="Your username"
          />
          <input
            className="input-padding"
            value={password}
            onChange={e => this.setState({ password: e.target.value })}
            type="password"
            placeholder="Your password"
          />
          <button
            className="button"
            type="button"
            onClick={() => {
              this.login();
            }}
          >
            Login
          </button>
        </div>
      </Layout>
    );
  }
}

export default PageWrapper(Login);
