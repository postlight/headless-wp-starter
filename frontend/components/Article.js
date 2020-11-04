/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable jsx-a11y/label-has-associated-control */
import React from 'react';
import Head from 'next/head';
import stylesheet from '../src/styles/article.scss';
import RecentPosts from './RecentPosts';

// TODO: Add type for Post
const Article = ({
  post: { title, content, date, _embedded, excerpt },
  recentPosts,
}) => {
  const matchedImgSrcUrlList = content.rendered.match(
    /(?<=<img src=").*?(?=")/gm,
  );
  const featuredImageUrl =
    matchedImgSrcUrlList && matchedImgSrcUrlList.length
      ? matchedImgSrcUrlList[0]
      : 'https://www.japaninsider.co/assets/images/logo.svg';
  const parser = new DOMParser();
  const excerptDOM = parser.parseFromString(excerpt.rendered);
  return (
    <section className="block">
      <Head>
        <meta property="og:image" content={featuredImageUrl} />
        <meta property="og:title" content={title.rendered} />
        <meta property="og:description" content={excerptDOM.body.textContent} />
        <style
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{ __html: stylesheet }}
        />
        {/* <!-- Mailchimp Signup Form --> */}
        <link
          href="//cdn-images.mailchimp.com/embedcode/slim-10_7.css"
          rel="stylesheet"
          type="text/css"
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
        <div className="">
          <div className="recentPost">
            {recentPosts.length > 0 ? (
              <RecentPosts posts={recentPosts} />
            ) : null}
          </div>
          <MailChimpForm />
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

const MailChimpForm = () => (
  <div
    id="mc_embed_signup"
    style={{
      background: '#fff',
      clear: 'left',
      font: '14px Helvetica,Arial,sans-serif',
    }}
  >
    <form
      action={`https://gumo.us14.list-manage.com/subscribe/post?u=70f47caaa71d96fe967dfa602&amp;id=a8225094be`}
      method="post"
      id="mc-embedded-subscribe-form"
      name="mc-embedded-subscribe-form"
      className="validate"
      target="_blank"
      noValidate
    >
      <div id="mc_embed_signup_scroll">
        <label htmlFor="mce-EMAIL">
          訂閱Japan Insider電子報，追蹤日本最新資訊
        </label>
        <input
          type="email"
          name="EMAIL"
          className="email"
          id="mce-EMAIL"
          placeholder="email address"
          required
        />
        {/* <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups--> */}
        <div
          style={{ position: 'absolute', left: '-5000px' }}
          aria-hidden="true"
        >
          <input
            type="text"
            name="b_70f47caaa71d96fe967dfa602_a8225094be"
            tabIndex="-1"
          />
        </div>
        <div className="clear">
          <input
            type="submit"
            value="Subscribe"
            name="subscribe"
            id="mc-embedded-subscribe"
            className="button"
          />
        </div>
      </div>
    </form>
  </div>
);

export default Article;
