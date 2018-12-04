import React, { Component } from "react";
import Link from "next/link";
import Head from "next/head";
import Menu from "./Menu.js";
import { Config } from "../config.js";
import stylesheet from '../src/styles/style.scss'

class Header extends Component {
  constructor() {
    super();
  }

  render() {

    return (
      <div>
        <Head>
          <style dangerouslySetInnerHTML={{ __html: stylesheet }} />
          <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
          />
          <meta charSet="utf-8" />
          <link href="https://fonts.googleapis.com/css?family=Roboto:400,700|Roboto+Condensed" rel="stylesheet" />
          <link href="https://fonts.googleapis.com/css?family=Raleway:300&text=Merdithonk" rel="stylesheet" />
          <title>
            Meredith Monk
          </title>
        </Head>
      </div>
    );
  }
}

export default Header;
