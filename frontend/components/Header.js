import Link from "next/link";

const linkStyle = {
    marginRight: 15
};

// @TODO Use REST-powered menu http://localhost:8080/wp-json/menus/v1/menus/main
const Header = () => (
    <div>
        <Link href="/">
            <a style={linkStyle}>Home</a>
        </Link>
        <Link href="/about">
            <a style={linkStyle}>About</a>
        </Link>
        <h1>Hello Headless WordPress</h1>
    </div>
);

export default Header;
