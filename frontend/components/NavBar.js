import { textColor, titleColor, whiteColor } from './constants';

const NavBar = () => (
  <header>
    <a id="logo-link" href="https://www.japaninsider.co/">
      <figure>
        <img className="logo" src="/static/images/logo.svg" alt="logo" />
      </figure>
    </a>
    <nav>
      <a href="https://www.japaninsider.co/" target="_blank">
        首頁
      </a>
      <a href="https://www.japaninsider.co/#service" target="_blank">
        服務內容
      </a>
      <a href="https://www.japaninsider.co/#success-case" target="_blank">
        過去實績
      </a>
      <a href="https://www.japaninsider.co/#team" target="_blank">
        團隊成員
      </a>
      <a href="https://www.japaninsider.co/#japan-insider" target="_blank">
        日本內幕
      </a>
      <a href="https://japaninsider.typeform.com/to/S7rcLo" target="_blank">
        聯絡我們
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
          justify-content: center;
          padding: 40px 30px 6px;
          position: fixed;
          top: 0;
          width: 100%;
          z-index: 99;
        }
        .logo {
          height: 40px;
          object-fit: contain;
          width: 100%;
        }
        nav {
          margin-left: 137px;
        }
        nav a {
          color: ${textColor};
          padding: 8px;
          text-decoration: none;
        }
        nav a:hover,
        nav a:focus,
        nav a.selected {
          color: ${titleColor};
        }
      `}
    </style>
  </header>
);

export default NavBar;
