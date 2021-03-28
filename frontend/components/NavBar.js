import { textColor, titleColor, whiteColor } from './constants';

const NavBar = () => (
  <header>
    <a id="logo-link" href="https://www.japaninsider.co/">
      <figure>
        <img className="logo" src="/static/images/logo.svg" alt="logo" />
      </figure>
    </a>
    <nav>
      <a className="consultBtn" href="https://gumo.works/bd" target="_blank">
        免費諮詢
      </a>
      <a href="https://www.japaninsider.co/#service" target="_blank">
        服務內容
      </a>
      <a href="https://www.japaninsider.co/#faq" target="_blank">
        常見問題
      </a>
      <a href="https://www.japaninsider.co/#article" target="_blank">
        精選文章
      </a>
      <a
        className="fbLogo"
        href="https://www.facebook.com/japaninsiders/"
        target="_blank"
      >
        <figure>
          <img src="/static/images/fb.svg" alt="fb logo" />
        </figure>
      </a>
    </nav>
    <style jsx>
      {`
        header {
          align-items: center;
          background-color: ${whiteColor};
          box-sizing: border-box;
          display: flex;
          font-size: 1.6rem;
          height: 100px;
          justify-content: space-between;
          max-width: 960px;
          position: fixed;
          top: 0;
          width: 100%;
          z-index: 99;
        }

        @media screen and (max-width: 768px) {
          header {
            flex-direction: column;
            padding-bottom: 40px;
          }
          .logo {
            margin-top: 20px;
          }
        }
        .logo {
          height: 40px;
          object-fit: contain;
        }
        nav {
          display: flex;
          align-items: center;
        }
        nav a {
          color: #01403a;
          margin-left: 16px;
          text-decoration: none;
        }
        nav a:hover,
        nav a:focus,
        nav a.selected {
          opacity: 0.7;
        }
        nav a.consultBtn {
          align-items: center;
          background: #d94a3d;
          border-radius: 54px;
          color: #ffffff;
          display: flex;
          font-size: 1.6rem;
          height: 36px;
          justify-content: center;
          margin-left: 0;
          text-decoration: none;
          width: 96px;
        }
        .consultBtn:hover {
          opacity: 0.7;
        }
        .fbLogo {
          margin-left: 32px;
        }
      `}
    </style>
  </header>
);

export default NavBar;
