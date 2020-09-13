import "./reset.css";
import "./main.css";
import "./main";
import { Elm } from "./Main.elm";
import * as serviceWorker from "./serviceWorker";

// 1. detect current language from url
// 2. load language file asynchronously
// 3. init elm and pass translations as flag

// const getLocaleFromPathname = () =>
//   window.location.pathname.split("/")[1] || "en";

// const locale = getLocaleFromPathname();

// import(`./locale/${locale}.js`).then((module) => {
//   const translations = module.default;
//   Elm.Main.init({
//     node: document.getElementById("root"),
//     flags: {
//       translations: JSON.stringify(translations),
//     },
//   });
// });

Elm.Main.init({
  node: document.getElementById("root"),
  // flags: {
  //   translations: JSON.stringify(translations),
  // },
});

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
