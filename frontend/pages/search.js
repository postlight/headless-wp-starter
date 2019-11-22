import React, { Component } from 'react';
import Link from 'next/link';
import axios from 'axios';
import Router from 'next/router';
import Layout from '../components/Layout';
import PageWrapper from '../components/PageWrapper';
import Menu from '../components/Menu';
import WPAPI from 'wpapi';
import Config from '../config';

const wp = new WPAPI({ endpoint: Config.apiUrl });

class Search extends Component {
  state = {
    posts: [],
    filter: '',
  };

  /**
   * Execute search query, process the response and set the state
   */
  executeSearch = async () => {
    const { filter } = this.state;
    let posts = await wp
      .posts()
      .search(filter);

    this.setState({ posts });
  }

  render() {
    const { posts } = this.state;
    const { headerMenu } = this.props;

    return (
      <Layout>
        <Menu menu={headerMenu} />
        <div className="content login mh4 mv4 w-two-thirds-l center-l">
          <div>
            <h1>Search</h1>
            <input
              className="db w-100 pa3 mv3 br6 ba b--black"
              type="text"
              placeholder="Search by name and content"
              onChange={e => this.setState({ filter: e.target.value })}
              onKeyDown={this.handleKeyDown}
            />
            <button
              className="round-btn invert ba bw1 pv2 ph3"
              type="button"
              onClick={() => this.executeSearch()}
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
}

export default PageWrapper(Search);
