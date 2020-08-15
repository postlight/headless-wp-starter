import React from 'react';
import Head from 'next/head';
import stylesheet from '../src/styles/article.scss';
import RecentPosts from './RecentPosts';

// TODO: Add type for Post
const Article = ({
  post: { title, content, date, _embedded },
  recentPosts,
}) => {
  const matchedImgSrcUrlList = content.rendered.match(
    /(?<=<img src=").*?(?=")/gm,
  );
  const featuredImageUrl =
    matchedImgSrcUrlList && matchedImgSrcUrlList.length
      ? matchedImgSrcUrlList[0]
      : 'https://www.japaninsider.co/assets/images/logo.svg';
  return (
    <section className="block">
      <Head>
        <meta property="og:image" content={featuredImageUrl} />
        <style
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{ __html: stylesheet }}
        />
      </Head>
      <div className="content">
        <div className="currentPost">
          <div className="articleInfo">
            <h1 className="title">{title.rendered}</h1>
            <span className="date">
              Posted on {new Date(date).toLocaleDateString()}
            </span>
            <span className="author">
              Posted by{' '}
              {_embedded.author &&
                _embedded.author[0] &&
                _embedded.author[0].name}
            </span>
          </div>
          <article dangerouslySetInnerHTML={{ __html: content.rendered }} />
        </div>
        <div className="recentPost">
          {recentPosts.length > 0 ? <RecentPosts posts={recentPosts} /> : null}
        </div>
      </div>
      <style jsx>
        {`
          .block {
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
          }
          .content {
            display: flex;
            flex-direction: row;
            width: 100%;
          }

          @media (max-width: 768px) {
            .content {
              flex-direction: column;
              align-items: center;
              margin-left: 20px;
              margin-right: 20px;
            }
            .currentPost {
              margin-left: auto;
              margin-right: auto;
              margin-bottom: 80px;
            }
          }

          .currentPost {
            width: 100%;
            max-width: 633px;
          }
          .articleInfo {
            margin: 0 auto 30px auto;
          }
          .title {
            margin-bottom: 10px;
          }
          .date {
            color: #636363;
            font-size: 1.4rem;
          }
          .author {
            color: #636363;
            font-size: 1.4rem;
            margin-left: 20px;
          }
          article {
            margin: 0 auto 10px auto;
          }
          .recentPost {
            width: 307px;
            height: 659px;
            border: 1px solid #eeeeee;
            box-sizing: border-box;
            border-radius: 8px;
            padding-top: 32px;
            padding-left: 20px;
            padding-right: 20px;
            margin-bottom: 80px;
          }
        `}
      </style>
    </section>
  );
};
export default Article;
