import { lightGray, textColor, titleColor } from './constants';

const RecentPosts = ({ posts }) => (
  <section className="block">
    <h3>最新文章</h3>
    <ul>
      {posts.map(post => (
        <li className="title">
          <a className="link" href={`/post/${post.slug}`}>
            {post.title.rendered}
          </a>
        </li>
      ))}
    </ul>
    <style jsx>{`
      h3 {
        font-size: 1.8rem;
        color: ${titleColor};
        margin-bottom: 30px;
      }
      .title {
        font-size: 1.4rem;
        margin-bottom: 20px;
      }
      .link {
        color: #636363;
        text-decoration: none;
      }
      .link:hover {
        color: ${lightGray};
      }
    `}</style>
  </section>
);

export default RecentPosts;
