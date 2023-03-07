import React, { useState } from 'react';
import axios from 'axios';
import WPAPI from 'wpapi';
import Router from 'next/router';
import { GetStaticProps, InferGetStaticPropsType, NextPage } from 'next/types';
import Layout from '../components/Layout';
import Menu from '../components/Menu';
import Config from '../config';

const wp = new WPAPI({ endpoint: Config.apiUrl });
wp.menus = wp.registerRoute('menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)');

export const getStaticProps: GetStaticProps = async () => {
  const menu = await wp.menus().id('header-menu');

  return {
    props: { menu }
  }
}

type PageProps = InferGetStaticPropsType<typeof getStaticProps>

const Login: NextPage<PageProps> = ({ menu }) => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  const login = () => {
    let message = '';
    setMessage(message);

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
        setMessage(message);
      });
  }

  return (
    <Layout>
      <Menu menu={menu} />
      <div className="content login mh4 mv4 w-two-thirds-l center-l">
        <div>
          <h1>Log in</h1>
          <p>Starter Kit allows you to log in via the JavaScript frontend, meaning you can interact with the backend without gaining admin access.</p>
          <p><strong>Log in to view hidden posts only available to authenticated users.</strong></p>
          <p className="message mb3"><strong>{message}</strong></p>
          <form onSubmit={(e) => { login(); e.preventDefault() }}>
            <input
              className="db w-100 pa3 mv3 br6 ba b--black"
              value={username}
              onChange={e => setUsername(e.target.value)}
              type="text"
              placeholder="Username"
            />
            <input
              className="db w-100 pa3 mv3 br6 ba b--black"
              value={password}
              onChange={e => setPassword(e.target.value)}
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

export default Login;
