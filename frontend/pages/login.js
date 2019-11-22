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
    message: '',
  };

  static async getInitialProps() {
    return '';
  }

  login() {
    let message = '';
    this.setState({ message });
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
      })
      .catch(() => {
        message =
          'Sorry, that username and password combination is not valid.';
        this.setState({ message });
      });
  }

  render() {
    const { username, password, message } = this.state;
    const { headerMenu } = this.props;

    return (
      <Layout>
        <Menu menu={headerMenu} />
        <div className="content login mh4 mv4 w-two-thirds-l center-l">
          <div>
            <h1>Log in</h1>
            <p>Starter Kit allows you to log in via the JavaScript frontend, meaning you can interact with the backend without gaining admin access.</p>
            <p><strong>Log in to view hidden posts only available to authenticated users.</strong></p>
            <p className="message mb3"><strong>{message}</strong></p>
            <form onSubmit={(e) => {this.login(); e.preventDefault()}}>
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
              <input
                className="round-btn invert ba bw1 pv2 ph3"
                type="submit"
                value="Log in"
              />
            </form>
          </div>
        </div>
      </Layout>
    );
  }
}

export default PageWrapper(Login);
