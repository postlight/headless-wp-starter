import type { AppProps } from 'next/app'

import "tachyons/css/tachyons.css";
import "../src/styles/style.scss";

function MyApp({ Component, pageProps }: AppProps) {
  return (
    <>
      <Component {...pageProps} />
    </>
  )
}

export default MyApp