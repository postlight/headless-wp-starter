import React, { PureComponent } from "react";
import Head from "next/head";
import stylesheet from '../src/styles/style.scss'

class Header extends PureComponent {
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
                    <title>
                        WordPress + React Starter Kit Frontend by Postlight
                    </title>
                </Head>
            </div>
        );
    }
}

export default Header;
