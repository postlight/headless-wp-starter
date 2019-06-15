import { backgroundColor, textColor } from './constants';

const RecentPosts = ({ posts }) => (
  <section className="block">
    <h3>Recent Posts</h3>
    <ul>
      {posts.map(post => (
        <li className="title">
          <a href={`/post/${post.slug}`}>{post.title.rendered}</a>
        </li>
      ))}
    </ul>
    <style jsx>{`
      h3 {
        font-size: 1.8rem;
        color: ${backgroundColor};
        margin-bottom: 15px;
      }
      .title {
        font-size: 1.4rem;
        color: ${textColor};
        line-height: 2;
      }
    `}</style>
  </section>
);

export default RecentPosts;
