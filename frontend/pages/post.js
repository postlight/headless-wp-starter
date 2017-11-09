import Layout from "../components/Layout.js";
import fetch from "isomorphic-unfetch";

const Post = props => (
    <Layout>
        <h1>{props.posts[0].title.rendered}</h1>
        <div
            dangerouslySetInnerHTML={{
                __html: props.posts[0].content.rendered
            }}
        />
    </Layout>
);

Post.getInitialProps = async function(context) {
    const { slug, apiRoute } = context.query;
    const res = await fetch(
        `http://localhost:8080/wp-json/wp/v2/${apiRoute}?slug=${slug}`
    );
    const posts = await res.json();
    return { posts };
};

export default Post;
