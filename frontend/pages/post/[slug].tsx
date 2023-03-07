import React from 'react';
import WPAPI from 'wpapi';
import { GetStaticPaths, GetStaticProps, InferGetStaticPropsType, NextPage } from 'next';
import Layout from '../../components/Layout';
import Config from '../../config';
import Menu from '../../components/Menu';

const wp = new WPAPI({ endpoint: Config.apiUrl });
wp.menus = wp.registerRoute('menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)');

export const getStaticProps: GetStaticProps = async (context) => {
  const { slug } = context.params;

  const posts = wp.posts();

  const [post, menu] = await Promise.all([
    await posts
      .slug(slug)
      .embed()
      .then((data) => {
        return data[0];
      }),
    wp.menus().id('header-menu')
  ]);

  return { props: { post, menu } };
}

export const getStaticPaths: GetStaticPaths = async () => {
  const posts = await wp.posts().embed();
  const paths = posts.map(post => ({ params: { slug: post.slug } }));

  return {
    paths,
    fallback: false,
  }
}

type PageProps = InferGetStaticPropsType<typeof getStaticProps>;

const DynamicPost: NextPage<PageProps> = ({ post, menu }) => {
  const heroUrl =
    post._embedded &&
      post._embedded['wp:featuredmedia'] &&
      post._embedded['wp:featuredmedia'][0] &&
      post._embedded['wp:featuredmedia'][0].source_url
      ? post._embedded['wp:featuredmedia'][0].source_url
      : false;

  return (
    <Layout className="test">
      <Menu menu={menu} />
      {heroUrl ? (
        <div className={`hero flex items-center post-type-${post.type}`}>
          <img className="w-100" src={heroUrl} />
        </div>
      ) : (
        ''
      )}
      <div
        className={`content mh4 mv4 w-two-thirds-l center-l post-${post.id} post-type-${post.type}`}
      >
        <h1>{post.title.rendered}</h1>
        <div
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: post.content.rendered,
          }}
        />
      </div>
    </Layout>
  );
}

export default DynamicPost;
