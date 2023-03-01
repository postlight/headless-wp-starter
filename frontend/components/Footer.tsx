import Image from "next/image";

const Footer = () => (
  <div className="labs-footer bg-black">
    <p className="white">A Labs project from your friends at</p>
    <a href="https://postlight.com/">
      <Image src="/images/postlight-logo.svg" width={32} height={32} alt="Postlight Logo" />
    </a>
  </div>
);

export default Footer;
