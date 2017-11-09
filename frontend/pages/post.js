import Layout from "../components/Layout.js";
import fetch from "isomorphic-unfetch";

const Post = props => (
    <Layout>
        <h1>{props.post.title.rendered}</h1>
        <div
            dangerouslySetInnerHTML={{
                __html: props.post.content.rendered
            }}
        />
    </Layout>
);

Post.getInitialProps = async function(context) {
    const { slug, apiRoute } = context.query;
    const res = await fetch(
        `http://localhost:8080/wp-json/postlight/v1/${apiRoute}?slug=${slug}`
    );
    const post = await res.json();
    return { post };
};

export default Post;
