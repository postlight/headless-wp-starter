import Head from 'next/head';
import { secondaryTextColor, titleColor } from './constants';
import stylesheet from '../src/styles/article.scss';

// TODO: Add type for Post
const Article = ({ post: { title, content, date, author } }) => (
  <section className="block">
    <Head>
      <style
        // eslint-disable-next-line react/no-danger
        dangerouslySetInnerHTML={{ __html: stylesheet }}
      />
    </Head>
    <div className="articleInfo">
      <h1 className="title">{title.rendered}</h1>
      {/* TODO: fix date format */}
      <span className="date">Posted on {date}</span>
      <span className="author">
        {/* TODO: fix author mapping */}
        Posted by {author}
      </span>
    </div>
    <article
      className="content"
      dangerouslySetInnerHTML={{ __html: content.rendered }}
    />
    <style jsx>
      {`
        .block {
          display: flex;
          flex-direction: column;
          justify-content: center;
          margin-top: 50px;
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
        .content {
          font-size: 1.4rem;
          line-height: 2;
          margin: 0 auto 10px auto;
          max-width: 70%;
        }
      `}
    </style>
  </section>
);
export default Article;
