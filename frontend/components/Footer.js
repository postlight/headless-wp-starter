import Link from "next/link";

const hrStyle = {
    marginTop: 75
};

const Footer = () => (
    <div>
        <hr style={hrStyle} />
        <p>
            ❤️{" "}
            <Link href="https://postlight.com">
                <a>Made by Postlight</a>
            </Link>. 🍴{" "}
            <Link href="https://github.com/postlight/headless-wp-starter">
                <a>Fork on GitHub</a>
            </Link>.
        </p>
        <p>
            👋 Need help with your publishing platform?{" "}
            <Link href="mailto:hello@postlight.com?subject=Partner+with+Postlight+on+a+headless+CMS+project">
                <a>Say hi.</a>
            </Link>
        </p>
    </div>
);

export default Footer;
