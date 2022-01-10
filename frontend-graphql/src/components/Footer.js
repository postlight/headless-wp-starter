import React from 'react';
import { ReactComponent as Logo } from '../static/images/postlight-logo.svg';

const Footer = () => (
  <div className="labs-footer bg-black">
    <p className="white">A Labs project from your friends at</p>
    <a href="https://postlight.com/">
      <Logo width={135} height={32} />
    </a>
  </div>
);

export default Footer;
