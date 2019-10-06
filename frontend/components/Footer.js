import { backgroundColor } from './constants';

const Footer = () => (
  <footer>
    <div className="footerInfo">
      <figure>
        <img src="/static/images/white-logo.svg" alt="logo" />
      </figure>
      <p className="aboutUsEmail">contact@japaninsider.co</p>
      <p className="aboutUsAddress">106-0046 東京都港区元麻布3-1-6</p>
    </div>
    <style jsx>
      {`
        footer {
          background: #d94a3d;
          box-sizing: border-box;
          height: 200px;
          padding: 33px 0 32px 240px;
        }

        .footerInfo {
          color: #ffffff;
          font-size: 1.4rem;
          line-height: 20px;
          max-width: 960px;
          text-align: left;
        }

        .footerInfo > figure {
          margin-bottom: 9px;
        }

        .aboutUsEmail {
          color: #ffffff;
          font-size: 1.6rem;
          margin-bottom: 5px;
        }

        .aboutUsAddress {
          color: #ffffff;
          font-size: 1.6rem;
        }
      `}
    </style>
  </footer>
);

export default Footer;
