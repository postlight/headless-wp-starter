import Link from "next/link";

const hrStyle = {
    marginTop: 75
};

const Footer = () => (
    <div>
        <hr style={hrStyle} />
        <p>
            ❤️ <Link href="https://postlight.com">Made by Postlight</Link>. 🍴{" "}
            <Link href="https://github.com/postlight/headless-wp-starter">
                Fork on GitHub
            </Link>.
        </p>
        <p>
            👋 Need help with your publishing platform?{" "}
            <Link href="mailto:hello@postlight.com?subject=Partner+with+Postlight+on+a+headless+CMS+project">
                Say hi.
            </Link>
        </p>
    </div>
);

export default Footer;
