import Header from "./Header";
import Footer from "./Footer";

const layoutStyle = {
    margin: 20,
    padding: 20
};

const Layout = props => (
    <div style={layoutStyle}>
        <Header title={props.title} settings={props.settings} />
        {props.children}
        <Footer />
    </div>
);

export default Layout;
