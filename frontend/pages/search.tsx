import { useState } from 'react';
import Link from 'next/link';
import WPAPI from 'wpapi';
import { GetStaticProps, InferGetStaticPropsType, NextPage } from 'next';
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

const Search: NextPage<PageProps> = ({ menu }) => {
  const [posts, setPosts] = useState([]);
  const [filter, setFilter] = useState('');

  /**
   * Execute search query, process the response and set the state
   */
  const executeSearch = async () => {
    let posts = await wp
      .posts()
      .search(filter);

    setPosts(posts);
  }

  return (
    <Layout>
      <Menu menu={menu} />
      <div className="content login mh4 mv4 w-two-thirds-l center-l">
        <div>
          <h1>Search</h1>
          <input
            className="db w-100 pa3 mv3 br6 ba b--black"
            type="text"
            placeholder="Search by name and content"
            onChange={e => setFilter(e.target.value)}
          />
          <button
            className="round-btn invert ba bw1 pv2 ph3"
            type="button"
            onClick={executeSearch}
          >
            Submit
          </button>
        </div>
        <div className="mv4">
          {posts ? posts.map((post, index) => (
            <div className="mv4" key={post.slug}>
              <span className="gray">{index + 1}.</span>
              <Link href={`/post/${post.slug}`}>
                <h3 className="ml1 dib pointer">{post.title.rendered}</h3>
              </Link>
            </div>
          )) : ''}
        </div>
      </div>
    </Layout>
  );
}

export default Search
