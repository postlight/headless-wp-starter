import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import Router from 'next/router';
import WPAPI from 'wpapi';
import { GetStaticProps, InferGetStaticPropsType, NextPage } from 'next';
import Logo from '../components/common/Logo';
import Layout from '../components/Layout';
import Menu from '../components/Menu';
import Config from '../config';

const wp = new WPAPI({ endpoint: Config.apiUrl });
wp.menus = wp.registerRoute('menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)');

const headerImageStyle = {
  marginTop: 50,
  marginBottom: 50,
};

const tokenExpired = () => {
  if (!!window) {
    localStorage.removeItem(Config.AUTH_TOKEN);
  }

  wp.setHeaders('Authorization', '');
  Router.push('/login');
};

export const getStaticProps: GetStaticProps = async () => {
  const [page, posts, pages, menu] = await Promise.all([
    wp
      .pages()
      .slug('welcome')
      .embed()
      .then((data) => {
        return data[0];
      }),
    wp.posts().embed(),
    wp.pages().embed(),
    wp.menus().id('header-menu')
  ]);

  return {
    props: { page, posts, pages, menu }
  }
}

type PageProps = InferGetStaticPropsType<typeof getStaticProps>

const Index: NextPage<PageProps> = ({ page, posts, pages, menu }) => {
  const [_, setId] = useState("");

  useEffect(() => {
    const token = localStorage.getItem(Config.AUTH_TOKEN);
    if (token) {
      wp.setHeaders('Authorization', `Bearer ${token}`);
      wp.users()
        .me()
        .then((data) => {
          const { id } = data;
          setId(id);
        })
        .catch((err) => {
          if (err.data.status === 403) {
            tokenExpired();
          }
        });
    }
  }, []);

  const fposts = posts.map((post) => {
    return (
      <ul key={post.slug}>
        <li>
          <Link
            as={`/post/${post.slug}`}
            href={`/post?slug=${post.slug}&apiRoute=post`}
          >
            {post.title.rendered}
          </Link>
        </li>
      </ul>
    );
  });


  const fpages = pages.map((ipage) => {
    if (ipage.slug !== 'welcome') {
      return (
        <ul key={ipage.slug}>
          <li>
            <Link
              as={`/page/${ipage.slug}`}
              href={`/post?slug=${ipage.slug}&apiRoute=page`}
            >
              {ipage.title.rendered}
            </Link>
          </li>
        </ul>
      );
    }
  });

  return (
    <Layout>
      <Menu menu={menu} />
      <div className="intro bg-black white ph3 pv4 ph5-m pv5-l flex flex-column flex-row-l">
        <div className="color-logo w-50-l mr3-l">
          <Logo width={440} height={280} color="#FFF" />
        </div>
        <div className="subhed pr6-l">
          <h1>{page.title.rendered}</h1>
          <div className="dek">
            You are now running a WordPress backend with a React frontend.
          </div>
          <div className="api-info b mt4">
            Starter Kit supports both REST API and GraphQL
            <div className="api-toggle">
              <a className="rest" href="http://localhost:3000">
                REST API
              </a>
              <a className="graphql" href="http://localhost:3001">
                GraphQL
              </a>
            </div>
          </div>
        </div>
      </div>
      <div className="recent flex mh4 mv4 w-two-thirds-l center-l">
        <div className="w-50 pr3">
          <h2>Posts</h2>
          {fposts}
        </div>
        <div className="w-50 pl3">
          <h2>Pages</h2>
          {fpages}
        </div>
      </div>
      <div
        className="content mh4 mv4 w-two-thirds-l center-l home"
        // eslint-disable-next-line react/no-danger
        dangerouslySetInnerHTML={{
          __html: page.content.rendered,
        }}
      />
    </Layout>
  );
}

export default Index;
