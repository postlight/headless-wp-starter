import Head from 'next/head';
import RecentPosts from './RecentPosts';
import { secondaryTextColor, titleColor } from './constants';
import stylesheet from '../src/styles/article.scss';

// TODO: Add type for Post
const Article = ({
  post: { title, content, date, _embedded },
  recentPosts,
}) => (
  <section className="block">
    <Head>
      <style
        // eslint-disable-next-line react/no-danger
        dangerouslySetInnerHTML={{ __html: stylesheet }}
      />
    </Head>
    <div className="currentPost">
      <div className="articleInfo">
        <h1 className="title">{title.rendered}</h1>
        <span className="date">
          Posted on {new Date(date).toLocaleDateString()}
        </span>
        <span className="author">
          Posted by{' '}
          {_embedded.author && _embedded.author[0] && _embedded.author[0].name}
        </span>
      </div>
      <article dangerouslySetInnerHTML={{ __html: content.rendered }} />
    </div>
    <div className="recentPost">
      {recentPosts.length > 0 ? <RecentPosts posts={recentPosts} /> : null}
    </div>
    <style jsx>
      {`
        .block {
          box-sizing: border-box;
          display: flex;
          justify-content: space-between;
          margin-top: 50px;
          margin-left: 10%;
        }
        .currentPost {
          width: 60%;
          max-width: 1090px;
        }
        .articleInfo {
          font-size: 14px;
          margin: 0 auto 30px auto;
        }
        .title {
          color: ${titleColor};
          font-weight: bold;
          font-size: 3.6rem;
          margin-bottom: 10px;
        }
        .date {
          color: ${secondaryTextColor};
        }
        .author {
          margin-left: 20px;
        }
        article {
          font-size: 1.4rem;
          line-height: 2;
          margin: 0 auto 10px auto;
        }
        .recentPost {
          flex: 1;
          display: flex;
          justify-content: center;
          padding: 30px;
        }
      `}
    </style>
  </section>
);
export default Article;
