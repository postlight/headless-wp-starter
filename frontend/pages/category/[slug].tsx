import React from 'react';
import Link from 'next/link';
import WPAPI from 'wpapi';
import { GetStaticPaths, GetStaticProps, InferGetStaticPropsType, NextPage } from 'next';
import Layout from '../../components/Layout';
import Menu from '../../components/Menu';
import Config from '../../config';

const wp = new WPAPI({ endpoint: Config.apiUrl });
wp.menus = wp.registerRoute('menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)');

export const getStaticProps: GetStaticProps = async (context) => {
  const { slug } = context.params;

  let posts = [];

  const categories = await wp
    .categories()
    .slug(slug)
    .embed();

  const menu = await wp.menus().id('header-menu');

  if (categories.length > 0) {
    posts = await wp
      .posts()
      .category(categories[0].id)
      .embed();
  }

  return { props: { categories, posts, menu } };
}

export const getStaticPaths: GetStaticPaths = async () => {
  const categories = await wp.categories();
  const paths = categories.map(category => ({ params: { slug: category.slug } }))

  return {
    paths,
    fallback: false
  }
}

type PageProps = InferGetStaticPropsType<typeof getStaticProps>;

const DynamicCategories: NextPage<PageProps> = ({ categories, posts, menu }) => {
  const fposts = posts.map(post => {
    return (
      <div key={post.id}>
        <h2 className="mt5">
          <Link
            as={`/post/${post.slug}`}
            href={`/post?slug=${post.slug}&apiRoute=post`}
          >
            {post.title.rendered}
          </Link>
        </h2>
        <div
          className="mv4"
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: post.excerpt.rendered,
          }}
        />
        <Link
          as={`/post/${post.slug}`}
          href={`/post?slug=${post.slug}&apiRoute=post`}
          legacyBehavior>
          <span className="round-btn pointer invert ba bw1 pv2 ph3">
            Read more
          </span>
        </Link>
      </div>
    );
  });
  return (
    <Layout>
      <Menu menu={menu} />
      <div className="content mh4 mt4 mb6 w-two-thirds-l center-l">
        <span className="gray f3 b">Category Archives:</span>
        <h1 className="f1 mt3">{categories[0].name}</h1>
        {fposts}
      </div>
    </Layout>
  );
}

export default DynamicCategories
